<?php
namespace T9Suite\Settings;

use T9Suite\Settings\License\License_Settings;
use T9Suite\Settings\General\General_Settings;
use T9Suite\Settings\Marketplace\Marketplace_Settings;

if (!defined('ABSPATH')) {
    exit;
}

class T9Suite_Settings {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_main_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_typography_styles']);

        add_action('rest_api_init', [$this, 'init_rest_apis']);
        add_action('wp_ajax_t9suite_download_module', [$this, 'handle_ajax_download']);
    }

    public function register_main_menu() {
        // Tạo menu chính "T9Suite"
        add_menu_page(
            __('T9Suite', 't9suite'),
            'T9Suite',
            'manage_options',
            't9suite',
            [$this, 'redirect_to_settings'], // hoặc License nếu bạn muốn
            'dashicons-admin-generic',
            1
        );
    
        // Submenu 1 - License
        add_submenu_page(
            't9suite',
            __('License', 't9suite'),
            '1. License',
            'manage_options',
            't9suite-license',
            [(new License_Settings()), 'render_page']
        );
    
        // Submenu 2 - Settings
        add_submenu_page(
            't9suite',
            __('Settings', 't9suite'),
            '2. Settings',
            'manage_options',
            't9suite-settings',
            [(new General_Settings()), 'render_page']
        );
    
        // Submenu 3 - Marketplace
        add_submenu_page(
            't9suite',
            __('Marketplace', 't9suite'),
            '3. Marketplace',
            'manage_options',
            't9suite-marketplace',
            [(new Marketplace_Settings()), 'render_page']
        );
    
        // 🔥 Ẩn submenu trùng với menu chính
        add_action('admin_head', function () {
            remove_submenu_page('t9suite', 't9suite');
        });
    }
    

    public function redirect_to_settings() {
        // Khi click vào menu chính "T9Suite", tự động redirect đến "Settings"
        wp_safe_redirect(admin_url('admin.php?page=t9suite-settings'));
        exit;
    }

    public function enqueue_assets($hook) {
        $allowed_hooks = [
            'toplevel_page_t9suite',
            't9suite_page_t9suite-license',
            't9suite_page_t9suite-settings',
            't9suite_page_t9suite-marketplace',
        ];

        if (!in_array($hook, $allowed_hooks)) return;

        wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css');
        wp_enqueue_style('t9suite-style', T9SUITE_PLUGIN_URL . 'assets/css/t9suite.css', [], T9SUITE_VERSION);

        wp_enqueue_script('jquery');
        wp_enqueue_media();

        wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js', ['jquery'], null, true);
        wp_enqueue_script('t9suite-script', T9SUITE_PLUGIN_URL . 'assets/js/t9suite.js', ['jquery', 'select2-js'], T9SUITE_VERSION, true);

        wp_localize_script('t9suite-script', 't9suiteData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('t9suite_nonce'),
        ]);
    }

    public function register_settings() {
        (new General_Settings())->register_settings();
    }

    public function enqueue_typography_styles() {
        (new General_Settings())->enqueue_typography_styles();
    }

    public function init_rest_apis() {
        (new Marketplace_Settings())->register_rest_routes();
    }

    public function handle_ajax_download() {
        (new Marketplace_Settings())->ajax_download_module();
    }
}
