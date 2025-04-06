<?php
namespace T9AdminPro\License;

if (!defined('ABSPATH')) {
    exit;
}

class T9Admin_License {

    /**
     * Kiểm tra trạng thái license thông qua WooCommerce License Manager API
     */
public static function check_license_status() {
    $license_key = get_option('t9admin_pro_license_key', '');

    if (empty($license_key)) {
        return 'invalid';
    }

    $url = "https://thenine.vn/wp-json/lmfwc/v2/licenses/validate/{$license_key}";

    $auth_header = 'Basic ' . base64_encode('ck_fad64b827efca02dcf3aa86ce4bf299d0e977fab:cs_002b7edacc23a033aa1fd99cc10e57b7d92fa11e');

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => $auth_header,
            'Content-Type'  => 'application/json'
        ]
    ]);

    if (is_wp_error($response)) {
        error_log('Connection error: ' . $response->get_error_message());
        return 'error';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    error_log('Parsed API Response: ' . print_r($body, true));

    // Kiểm tra thành công
    if (isset($body['success']) && $body['success'] == 1) {
        return 'valid';
    }

    // Trường hợp phản hồi không hợp lệ hoặc key sai
    return 'invalid';
}





    /**
     * Kiểm tra license có hợp lệ không
     */
    public static function is_license_valid() {
        $status = self::check_license_status();
        return $status === 'valid';
    }

    /**
     * Lưu license key vào database
     */
    public static function save_license($license_key) {
        update_option('t9admin_pro_license_key', sanitize_text_field($license_key));
    }
}
