<?php

namespace T9AdminPro\Core;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class T9Admin_Nonce_Handler {

    /**Invalid POST request. Nonce verification failed.
     * Tạo nonce.
     *
     * @param string $action Tên hành động (unique).
     * @return string Nonce đã tạo.
     */
    public static function createNonce($action) {
        return wp_create_nonce($action);
    }

    /**
     * Kiểm tra nonce từ request POST.
     *
     * @param string $action Tên hành động.
     */
    public static function verifyPostNonce($action) {
        $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce'] ?? ''));

        if (empty($nonce) || !wp_verify_nonce($nonce, $action)) {
            wp_die(esc_html__('Invalid POST request. Nonce verification failed.', 't9admin-pro'));
        }
    }

    /**
     * Kiểm tra nonce từ request GET.
     *
     * @param string $action Tên hành động.
     */
    public static function verifyGetNonce($action) {
        $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? ''));

        if (empty($nonce)) {
            wp_die(esc_html__('Nonce missing for GET request.', 't9admin-pro'));
        }

        if (!wp_verify_nonce($nonce, $action)) {
            wp_die(esc_html__('Invalid GET request. Nonce verification failed.', 't9admin-pro'));
        }
    }
}
