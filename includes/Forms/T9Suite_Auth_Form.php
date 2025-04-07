<?php

namespace T9Suite\Forms;

use T9Suite\Core\T9Suite_Nonce_Handler;
use T9Suite\Core\T9Suite_Auth;
use T9Suite\Settings\T9Suite_Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class T9Suite_Auth_Form {

    /**
     * Xử lý form đăng nhập.
     */
    public static function handleLoginForm() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Debug: Kiểm tra dữ liệu POST
            error_log('POST Data: ' . print_r($_POST, true));

            // Lấy custom route trước khi dùng
            $custom_route = T9Suite_Settings::get_custom_route();

            // Xác thực nonce
            $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce'] ?? ''));
            if (empty($nonce) || !wp_verify_nonce($nonce, 't9suite_login_action')) {
                wp_redirect(add_query_arg('login_error', 'invalid_nonce', home_url("/{$custom_route}/login")));
                exit;
            }

            // Lấy thông tin từ form
            $username = isset($_POST['username']) ? sanitize_text_field(wp_unslash($_POST['username'])) : '';
            $password = isset($_POST['password']) ? sanitize_text_field(wp_unslash($_POST['password'])) : '';

            $auth = new T9Suite_Auth();

            // Thực hiện đăng nhập
            $login_result = $auth->login($username, $password);

            if (is_wp_error($login_result)) {
                wp_redirect(add_query_arg('login_error', '1', home_url("/{$custom_route}/login")));
                exit;
            }

            wp_redirect(home_url("/{$custom_route}"));
            exit;
        }
    }
}
