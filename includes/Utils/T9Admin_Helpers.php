<?php

namespace T9AdminPro\Utils;

if (!defined('ABSPATH')) {
    exit;
}

class T9Admin_Helpers {

    public function __construct() {
        add_action('admin_bar_menu', [$this, 'add_toolbar_shortcut'], 100);
    }

    /**
     * Adds a shortcut to the WordPress admin toolbar.
     *
     * @param object $wp_admin_bar WP_Admin_Bar instance.
     */
    public function add_toolbar_shortcut($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $custom_route = get_option('t9admin_pro_custom_route', 't9admin');
        $custom_route_url = home_url("/{$custom_route}");

        $wp_admin_bar->add_node([
            'id'    => 't9admin-pro-dashboard',
            'title' => __('T9Admin Dashboard', 't9admin-pro'),
            'href'  => $custom_route_url,
            'meta'  => [
                'title' => __('Go to T9Admin Dashboard', 't9admin-pro'),
                'target'=> '_blank',
            ],
        ]);
    }
}
