<?php
namespace T9AdminPro\Modules\Hrm\Staffs;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Staffs CPT for HRM Module in T9AdminPro.
 * Registers the Staffs custom post type without showing in Admin Menu.
 */
class StaffsCPT {

    /**
     * Constructor to initialize hooks for the Staffs CPT.
     */
    public function __construct() {
        add_action('init', [$this, 'register_staffs_post_type']);
    }

    /**
     * Register the Staffs custom post type.
     */
    public function register_staffs_post_type() {
        $labels = [
            'name'               => __('Staffs', 't9admin-pro'),
            'singular_name'      => __('Staff', 't9admin-pro'),
            'add_new'            => __('Add New Staff', 't9admin-pro'),
            'add_new_item'       => __('Add New Staff', 't9admin-pro'),
            'edit_item'          => __('Edit Staff', 't9admin-pro'),
            'new_item'           => __('New Staff', 't9admin-pro'),
            'view_item'          => __('View Staff', 't9admin-pro'),
            'search_items'       => __('Search Staffs', 't9admin-pro'),
            'not_found'          => __('No staffs found', 't9admin-pro'),
            'not_found_in_trash' => __('No staffs found in Trash', 't9admin-pro'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'supports'           => ['title', 'author'],
            'rewrite'            => ['slug' => 'staffs'],
            'show_in_menu'       => false, // Không hiển thị trên Admin Menu
            'has_archive'        => true,
        ];

        register_post_type('staffs', $args);
    }
}