<?php
namespace T9Suite\License;

if (!defined('ABSPATH')) {
    exit;
}

class T9Suite_License {

    /**
     * Kiểm tra trạng thái license
     */
    public static function check_license_status() {
        if (!defined('T9SUITE_PRODUCT_ID')) {
            define('T9SUITE_PRODUCT_ID', 224583); // ID của product cha
        }

        $valid_variation_ids = [224666, 224665];

        $license_key = get_option('t9suite_license_key', '');
        error_log("🔍 Checking license status - Stored license key: {$license_key}");

        if (empty($license_key)) {
            error_log('❌ License key is empty.');
            return [
                'status'            => 'invalid',
                'message'           => 'No license key provided.',
                'activated_at'      => null,
                'expires_at'        => null,
                'timesActivated'    => 0,
                'timesActivatedMax' => 0
            ];
        }

        $cached = get_transient('t9suite_license_status_data');
        if ($cached && is_array($cached)) {
            error_log('✅ Using cached license status: ' . print_r($cached, true));
            return $cached;
        }

        $url = "https://thenine.vn/wp-json/lmfwc/v2/licenses/{$license_key}";
        $auth_header = 'Basic ' . base64_encode('ck_fad64b827efca02dcf3aa86ce4bf299d0e977fab:cs_002b7edacc23a033aa1fd99cc10e57b7d92fa11e');

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => $auth_header,
                'Content-Type'  => 'application/json'
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            error_log('❌ License API error: ' . $response->get_error_message());
            return [
                'status'            => 'error',
                'message'           => 'Failed to connect to license server.',
                'activated_at'      => null,
                'expires_at'        => null,
                'timesActivated'    => 0,
                'timesActivatedMax' => 0
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('🔍 License response: ' . print_r($body, true));

        $success = $body['success'] ?? false;
        $data    = $body['data'] ?? [];

        $status       = 'invalid';
        $activated_at = $data['createdAt'] ?? null;
        $expires_at   = $data['expiresAt'] ?? null;
        $times_activated = (int) ($data['timesActivated'] ?? 0);
        $times_activated_max = (int) ($data['timesActivatedMax'] ?? 0);
        $message      = '';

        if ($success && isset($data['status'])) {
            $license_status = (int) $data['status'];
            if ($license_status === 1 || $license_status === 2) {
                $product_id = (int) ($data['productId'] ?? 0);

                error_log("🔍 Checking product_id: $product_id");

                if ($product_id === T9SUITE_PRODUCT_ID || in_array($product_id, $valid_variation_ids)) {
                    $status = 'valid';
                    $message = 'License is valid.';
                } else {
                    error_log('❌ License key does not match required product ID or variations.');
                    $status = 'wrong_product';
                    $message = 'License key does not match the required product.';
                }
            } else {
                error_log('❌ License status is not active: ' . $license_status);
                $message = 'License is not active.';
            }
        }

        if (!empty($expires_at) && strtotime($expires_at) < time()) {
            error_log('⚠️ License has expired.');
            $status = 'expired';
            $message = 'License has expired.';
        }

        $result = [
            'status'            => $status,
            'activated_at'      => $activated_at,
            'expires_at'        => $expires_at,
            'timesActivated'    => $times_activated,
            'timesActivatedMax' => $times_activated_max,
            'message'           => $message
        ];

        error_log('✅ Final license status: ' . print_r($result, true));
        set_transient('t9suite_license_status_data', $result, 30 * MINUTE_IN_SECONDS);
        return $result;
    }

    /**
     * Kiểm tra license có hợp lệ không
     */
    public static function is_license_valid() {
        $data = self::check_license_status();
        return $data['status'] === 'valid';
    }

    /**
     * Lưu hoặc hủy license
     */
    public static function save_license($license_key) {
        $license_key = sanitize_text_field($license_key);
        $auth_header = 'Basic ' . base64_encode('ck_fad64b827efca02dcf3aa86ce4bf299d0e977fab:cs_002b7edacc23a033aa1fd99cc10e57b7d92fa11e');

        // Xóa cache trước khi xử lý
        delete_transient('t9suite_license_status_data');

        // Trường hợp Detach License
        if (empty($license_key)) {
            $stored_key = get_option('t9suite_license_key', '');
            error_log("🔍 Detach license - Stored key: {$stored_key}");

            if (empty($stored_key)) {
                error_log('❌ No stored license key to deactivate.');
                delete_transient('t9suite_license_status_data');
                return [
                    'status'  => 'detached',
                    'message' => 'No license to deactivate.'
                ];
            }

            // Lấy token từ option
            $activation_token = get_option('t9suite_activation_token', '');
            error_log("🔍 Detach license - Activation token: {$activation_token}");

            if (empty($activation_token)) {
                error_log('❌ No activation token found for deactivation.');
                delete_option('t9suite_license_key');
                delete_transient('t9suite_license_status_data');
                return [
                    'status'  => 'detached',
                    'message' => 'License deactivated locally (no token available).'
                ];
            }

            // Gọi API /deactivate với token
            $url = "https://thenine.vn/wp-json/lmfwc/v2/licenses/deactivate/{$stored_key}?token={$activation_token}";
            $response = wp_remote_get($url, [
                'headers' => [
                    'Authorization' => $auth_header,
                    'Content-Type'  => 'application/json'
                ],
                'timeout' => 15,
            ]);

            if (is_wp_error($response)) {
                error_log('❌ Deactivation failed: ' . $response->get_error_message());
                return [
                    'status'  => 'error',
                    'message' => 'Failed to deactivate license: ' . $response->get_error_message()
                ];
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            error_log('🔁 Deactivation response: ' . print_r($body, true));

            if (!empty($body['success'])) {
                $times_activated = (int) ($body['data']['timesActivated'] ?? 0);
                error_log("🔍 After deactivation, timesActivated: {$times_activated}");

                // Lưu token vào lịch sử trước khi xóa
                $activation_history = get_option('t9suite_activation_history', []);
                if (!is_array($activation_history)) {
                    $activation_history = [];
                }
                $activation_history[] = [
                    'token' => $activation_token,
                    'license_key' => $stored_key,
                    'deactivated_at' => current_time('mysql'),
                    'timesActivated' => $times_activated
                ];
                update_option('t9suite_activation_history', $activation_history);
                error_log("📜 Saved token to history: {$activation_token}");

                // Xóa license key, nhưng không xóa token ngay lập tức
                delete_option('t9suite_license_key');
                delete_transient('t9suite_license_status_data');
                return [
                    'status'  => 'detached',
                    'message' => 'License deactivated successfully.'
                ];
            } else {
                return [
                    'status'  => 'error',
                    'message' => 'Deactivation failed: ' . ($body['message'] ?? 'Unknown error.')
                ];
            }
        }

        // Kiểm tra trạng thái hiện tại trước khi activate
        delete_transient('t9suite_license_status_data'); // Đảm bảo lấy dữ liệu mới nhất
        $current_status = self::check_license_status();
        error_log("🔍 Before activation, timesActivated: {$current_status['timesActivated']}/{$current_status['timesActivatedMax']}");

        if ($current_status['timesActivated'] >= $current_status['timesActivatedMax'] && $current_status['timesActivatedMax'] > 0) {
            // Kiểm tra xem có token cũ nào để reactivate không
            $activation_history = get_option('t9suite_activation_history', []);
            $deactivated_token = '';

            foreach ($activation_history as $entry) {
                if ($entry['license_key'] === $license_key && !empty($entry['deactivated_at'])) {
                    $deactivated_token = $entry['token'];
                    break;
                }
            }

            if (!empty($deactivated_token)) {
                error_log("🔍 Found deactivated token for reactivation: {$deactivated_token}");
                // Gọi /activate với token cũ để reactivate
                $url = "https://thenine.vn/wp-json/lmfwc/v2/licenses/activate/{$license_key}?token={$deactivated_token}";
                $response = wp_remote_get($url, [
                    'headers' => [
                        'Authorization' => $auth_header,
                        'Content-Type'  => 'application/json'
                    ],
                    'timeout' => 15,
                ]);

                if (is_wp_error($response)) {
                    error_log('❌ Reactivation failed: ' . $response->get_error_message());
                    return [
                        'status'  => 'error',
                        'message' => 'Failed to connect to license server: ' . $response->get_error_message()
                    ];
                }

                $body = json_decode(wp_remote_retrieve_body($response), true);
                error_log('🔁 Reactivation response: ' . print_r($body, true));

                if (!empty($body['success']) && empty($body['data']['errors'])) {
                    $data = $body['data'] ?? [];

                    // Cập nhật token trong option
                    update_option('t9suite_activation_token', $deactivated_token);
                    error_log("✅ Reactivation token reused: {$deactivated_token}");

                    // Cập nhật lịch sử: xóa deactivated_at
                    foreach ($activation_history as &$entry) {
                        if ($entry['token'] === $deactivated_token) {
                            unset($entry['deactivated_at']);
                            $entry['reactivated_at'] = current_time('mysql');
                            $entry['timesActivated'] = (int) ($data['timesActivated'] ?? 0);
                            break;
                        }
                    }
                    update_option('t9suite_activation_history', $activation_history);
                    error_log("📜 Updated token history after reactivation: {$deactivated_token}");

                    // Lưu license key
                    $saved = update_option('t9suite_license_key', $license_key);
                    if ($saved) {
                        error_log("✅ License key saved successfully: {$license_key}");
                    } else {
                        error_log("❌ Failed to save license key: {$license_key}");
                    }

                    delete_transient('t9suite_license_status_data');

                    $status_check = self::check_license_status();
                    if ($status_check['status'] === 'valid') {
                        return [
                            'status'  => 'valid',
                            'message' => 'License reactivated successfully.'
                        ];
                    } else {
                        return [
                            'status'  => $status_check['status'],
                            'message' => $status_check['message']
                        ];
                    }
                } else {
                    $error_message = $body['data']['errors']['lmfwc_rest_data_error'][0] ?? 'Unknown error.';
                    error_log('❌ Reactivation error: ' . $error_message);
                    return [
                        'status'  => 'error',
                        'message' => 'Reactivation failed: ' . $error_message
                    ];
                }
            } else {
                error_log("❌ No deactivated token found for reactivation.");
                return [
                    'status'  => 'error',
                    'message' => "License has reached maximum activations: {$current_status['timesActivated']}/{$current_status['timesActivatedMax']}. No deactivated token available for reactivation."
                ];
            }
        }

        // Trường hợp Activate License (tạo activation mới)
        $url = "https://thenine.vn/wp-json/lmfwc/v2/licenses/activate/{$license_key}";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => $auth_header,
                'Content-Type'  => 'application/json'
            ],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('❌ Activation failed: ' . $response->get_error_message());
            return [
                'status'  => 'error',
                'message' => 'Failed to connect to license server: ' . $response->get_error_message()
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('🔁 Activation response: ' . print_r($body, true));

        if (!empty($body['success']) && empty($body['data']['errors'])) {
            $data = $body['data'] ?? [];

            // Lưu activation token
            $activation_data = $data['activationData'] ?? [];
            $activation_token = '';

            // Xử lý cả hai trường hợp: activationData là object hoặc array
            if (is_array($activation_data) && !isset($activation_data['token'])) {
                // Nếu là array, lấy token từ phần tử cuối cùng (activation mới nhất)
                $last_activation = end($activation_data);
                $activation_token = $last_activation['token'] ?? '';
            } else {
                // Nếu là object, lấy token trực tiếp
                $activation_token = $activation_data['token'] ?? '';
            }

            if (!empty($activation_token)) {
                update_option('t9suite_activation_token', $activation_token);
                error_log("✅ Activation token saved: {$activation_token}");

                // Lưu token vào lịch sử khi activate
                $activation_history = get_option('t9suite_activation_history', []);
                if (!is_array($activation_history)) {
                    $activation_history = [];
                }
                $activation_history[] = [
                    'token' => $activation_token,
                    'license_key' => $license_key,
                    'activated_at' => current_time('mysql'),
                    'timesActivated' => (int) ($data['timesActivated'] ?? 0)
                ];
                update_option('t9suite_activation_history', $activation_history);
                error_log("📜 Saved token to history: {$activation_token}");
            } else {
                error_log("❌ No activation token found in response.");
            }

            // Lưu license key chỉ khi activation thành công
            $saved = update_option('t9suite_license_key', $license_key);
            if ($saved) {
                error_log("✅ License key saved successfully: {$license_key}");
            } else {
                error_log("❌ Failed to save license key: {$license_key}");
            }

            delete_transient('t9suite_license_status_data');

            $status_check = self::check_license_status();
            if ($status_check['status'] === 'valid') {
                return [
                    'status'  => 'valid',
                    'message' => 'License activated successfully.'
                ];
            } else {
                return [
                    'status'  => $status_check['status'],
                    'message' => $status_check['message']
                ];
            }
        } else {
            $error_message = $body['data']['errors']['lmfwc_rest_data_error'][0] ?? 'Unknown error.';
            error_log('❌ Activation error: ' . $error_message);
            return [
                'status'  => 'error',
                'message' => 'Activation failed: ' . $error_message
            ];
        }
    }
}