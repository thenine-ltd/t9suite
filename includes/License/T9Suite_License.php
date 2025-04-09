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

        if (empty($license_key)) {
            error_log('❌ License key is empty.');
            return ['status' => 'invalid', 'message' => 'No license key provided.', 'activated_at' => null, 'expires_at' => null];
        }

        $cached = get_transient('t9suite_license_status_data');
        if ($cached && is_array($cached)) {
            error_log('✅ Using cached license status');
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
            return ['status' => 'error', 'message' => 'Failed to connect to license server.', 'activated_at' => null, 'expires_at' => null];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('🔍 License response: ' . print_r($body, true));

        $success = $body['success'] ?? false;
        $data    = $body['data'] ?? [];

        $status       = 'invalid';
        $activated_at = $data['createdAt'] ?? null;
        $expires_at   = $data['expiresAt'] ?? null;
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
            'status'       => $status,
            'activated_at' => $activated_at,
            'expires_at'   => $expires_at,
            'message'      => $message
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
            if (!empty($stored_key)) {
                $url = "https://thenine.vn/wp-json/lmfwc/v2/licenses/deactivate/{$stored_key}";
                $response = wp_remote_get($url, [ // Thay POST thành GET
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

            delete_option('t9suite_license_key');
            delete_transient('t9suite_license_status_data');
            return [
                'status'  => 'detached',
                'message' => 'No license to deactivate.'
            ];
        }

        // Trường hợp Activate License
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

        if (!empty($body['success'])) {
            $data = $body['data'] ?? [];
            $activated = (int) ($data['timesActivated'] ?? 0);
            $max = (int) ($data['timesActivatedMax'] ?? 0);

            if ($max > 0 && $activated >= $max) {
                error_log("❌ License has reached max activations: {$activated}/{$max}");
                return [
                    'status'  => 'error',
                    'message' => "License has reached maximum activations: {$activated}/{$max}."
                ];
            }

            update_option('t9suite_license_key', $license_key);
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
            error_log('❌ Activation error: ' . ($body['message'] ?? 'Unknown error.'));
            return [
                'status'  => 'error',
                'message' => 'Activation failed: ' . ($body['message'] ?? 'Invalid license key.')
            ];
        }
    }
}