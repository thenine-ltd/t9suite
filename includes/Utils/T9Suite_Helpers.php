<?php

namespace T9Suite\Utils;

if (!defined('ABSPATH')) {
    exit;
}

class T9Suite_Helpers {

    public function __construct() {
        add_action('admin_bar_menu', [$this, 'add_toolbar_shortcut'], 100);
    }

    /**
     * Adds a shortcut to the WordPress admin toolbar.
     *
     * @param \WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
     */
    public function add_toolbar_shortcut($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $custom_route = get_option('t9suite_custom_route', 't9suite');
        $custom_route_url = home_url("/{$custom_route}");

        $wp_admin_bar->add_node([
            'id'    => 't9suite-dashboard',
            'title' => __('T9Suite Dashboard', 't9suite'),
            'href'  => $custom_route_url,
            'meta'  => [
                'title'  => __('Go to T9Suite Dashboard', 't9suite'),
                'target' => '_blank',
            ],
        ]);
    }
}
