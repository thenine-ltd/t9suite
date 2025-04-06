<?php

namespace T9AdminPro\Forms;

use T9AdminPro\Core\T9Admin_Nonce_Handler;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class T9Admin_Form_Handler {

    public static function handlePostForms() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['t9admin_action'])) {
            // Kiểm tra nonce cho form
            T9Admin_Nonce_Handler::verifyRequestNonce('t9admin_pro_form_action');

            // Lấy action từ form
            $action = sanitize_text_field(wp_unslash($_POST['t9admin_action']));

            // Kiểm tra xem action có phải là liên quan đến CPT không
            if (preg_match('/^(.*?)_(create|edit|delete)$/', $action, $matches)) {
                $cpt = $matches[1]; // Ví dụ: "product", "order"
                $operation = $matches[2]; // Ví dụ: "create", "edit", "delete"

                T9Admin_CPT_Form::handleCPTForm($cpt, $operation);
            } else {
                wp_die(esc_html__('Invalid form action.', 't9admin-pro'));
            }
        }
    }
}
