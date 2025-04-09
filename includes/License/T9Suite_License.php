<?php
namespace T9Suite\License;

if (!defined('ABSPATH')) {
    exit;
}

class T9Suite_License {

    public static function check_license_status() {
        if (!defined('T9SUITE_PRODUCT_ID')) {
            define('T9SUITE_PRODUCT_ID', 224583); // ID của product cha
        }

        // Danh sách các variation ID hợp lệ
        $valid_variation_ids = [224666, 224665]; // Thêm các variation ID khác nếu cần

        $license_key = get_option('t9suite_license_key', '');

        if (empty($license_key)) {
            error_log('❌ License key is empty.');
            return ['status' => 'invalid'];
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
            return ['status' => 'error'];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('🔍 License response: ' . print_r($body, true));

        $success = $body['success'] ?? false;
        $data    = $body['data'] ?? [];

        $status       = 'invalid';
        $activated_at = $data['createdAt'] ?? null;
        $expires_at   = $data['expiresAt'] ?? null;

        if ($success && isset($data['status'])) {
            $license_status = (int) $data['status'];
            // Chấp nhận 1 hoặc 2 là "active"
            if ($license_status === 1 || $license_status === 2) {
                $product_id = (int) ($data['productId'] ?? 0);

                error_log("🔍 Checking product_id: $product_id");

                // Kiểm tra product cha hoặc variation
                if ($product_id === T9SUITE_PRODUCT_ID || in_array($product_id, $valid_variation_ids)) {
                    $status = 'valid';
                } else {
                    error_log('❌ License key does not match required product ID or variations.');
                    $status = 'wrong_product';
                }
            } else {
                error_log('❌ License status is not active: ' . $license_status);
            }
        }

        if (!empty($expires_at) && strtotime($expires_at) < time()) {
            error_log('⚠️ License has expired.');
            $status = 'expired';
        }

        $result = [
            'status'       => $status,
            'activated_at' => $activated_at,
            'expires_at'   => $expires_at
        ];

        error_log('✅ Final license status: ' . print_r($result, true));
        set_transient('t9suite_license_status_data', $result, 30 * MINUTE_IN_SECONDS);
        return $result;
    }

    public static function is_license_valid() {
        $data = self::check_license_status();
        return $data['status'] === 'valid';
    }

    public static function save_license($license_key) {
        $license_key = sanitize_text_field($license_key);

        if (empty($license_key)) {
            delete_option('t9suite_license_key');
            delete_transient('t9suite_license_status_data');
        } else {
            update_option('t9suite_license_key', $license_key);
            delete_transient('t9suite_license_status_data');
        }
    }
}