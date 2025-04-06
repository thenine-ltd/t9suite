<?php

if (!defined('ABSPATH')) exit;

class T9AdminProPagesHandler {

    /**
     * Render the requested page content dynamically
     *
     * @param string $page The page slug from the query parameter
     */
    public static function t9admin_pro_render_page_content($page) {
        // Validate nonce if present
        if (isset($_GET['_wpnonce'])) {
            $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
            if (!wp_verify_nonce($nonce, 't9admin_pro_page_action')) {
                wp_die(esc_html__('Invalid request. Nonce verification failed.', 't9admin-pro'));
            }
        }
    
        $page = $page ? sanitize_text_field(wp_unslash($page)) : 'dashboard';

        $page_action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';

        $content_file = T9ADMIN_PRO_PLUGIN_DIR . "templates/default/pages/{$page}.php";

        if (post_type_exists($page)) {
            if ($page_action === 'create') {
                $content_file = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/pages/post-type-create.php';
            } else {
                $content_file = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/pages/post-type-content.php';
            }
        }

        if (file_exists($content_file)) {
            include $content_file;
        } else {
            echo '<h1>' . esc_html__('Content Not Found', 't9admin-pro') . '</h1>';
        }
    }
}
