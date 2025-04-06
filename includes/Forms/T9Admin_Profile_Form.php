<?php

namespace T9AdminPro\Forms;

if (!defined('ABSPATH')) {
    exit;
}

class T9Admin_Profile_Form {

    public static function register() {
        add_action('init', [__CLASS__, 'handleProfileUpdate']);
    }

    public static function handleProfileUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

            // Kiểm tra nonce
            if (!isset($_POST['t9admin_pro_profile_nonce']) || 
                !wp_verify_nonce($_POST['t9admin_pro_profile_nonce'], 't9admin_pro_update_profile')) {
                wp_redirect(add_query_arg('status', 'error', get_permalink()));
                exit;
            }

            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_redirect(add_query_arg('status', 'error', get_permalink()));
                exit;
            }

            // Lấy dữ liệu và sanitize
            $first_name = sanitize_text_field($_POST['first_name'] ?? '');
            $last_name = sanitize_text_field($_POST['last_name'] ?? '');
            $phone = sanitize_text_field($_POST['phone'] ?? '');

            // Cập nhật thông tin cá nhân
            $updated = wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);

            update_user_meta($user_id, 'phone', $phone);

            if (is_wp_error($updated)) {
                wp_redirect(add_query_arg('status', 'error', get_permalink()));
                exit;
            }

            // Kiểm tra và xử lý upload avatar nếu có
            if (!empty($_FILES['avatar']['name'])) {
                require_once ABSPATH . 'wp-admin/includes/file.php';

                $file = $_FILES['avatar'];
                $upload = wp_handle_upload($file, ['test_form' => false]);

                if (isset($upload['error'])) {
                    wp_redirect(add_query_arg('status', 'error', get_permalink()));
                    exit;
                }

                update_user_meta($user_id, 'avatar_url', $upload['url']);
            }

            // Redirect thành công
            wp_redirect(add_query_arg('status', 'success', get_permalink()));
            exit;
        }
    }
}
