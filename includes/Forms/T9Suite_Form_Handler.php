<?php

namespace T9Suite\Forms;

use T9Suite\Core\T9Suite_Nonce_Handler;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class T9Suite_Form_Handler {

    public static function handlePostForms() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['t9suite_action'])) {
            // Kiểm tra nonce cho form
            T9Suite_Nonce_Handler::verifyPostNonce('t9suite_form_action');

            // Lấy action từ form
            $action = sanitize_text_field(wp_unslash($_POST['t9suite_action']));

            // Kiểm tra action có định dạng CPT_action
            if (preg_match('/^(.*?)_(create|edit|delete)$/', $action, $matches)) {
                $cpt = $matches[1];       // Ví dụ: product, order
                $operation = $matches[2]; // Ví dụ: create, edit, delete

                // ✅ Đảm bảo bạn có T9Suite_CPT_Form::handleCPTForm()
                if (class_exists('\T9Suite\Forms\T9Suite_CPT_Form')) {
                    \T9Suite\Forms\T9Suite_CPT_Form::handleCPTForm($cpt, $operation);
                } else {
                    wp_die(esc_html__('CPT form handler not found.', 't9suite'));
                }
            } else {
                wp_die(esc_html__('Invalid form action.', 't9suite'));
            }
        }
    }
}
