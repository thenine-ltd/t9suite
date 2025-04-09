<?php

namespace T9Suite\Core;

use T9Suite\Settings\T9Suite_Settings;
use T9Suite\Utils\T9Suite_Helpers;
use T9Suite\Core\T9Suite_Rewrite;
use T9Suite\Core\T9Suite_Auth;
use T9Suite\Core\T9Suite_Nonce_Handler;
use T9Suite\Forms\T9Suite_Form_Handler;
use T9Suite\Forms\T9Suite_Profile_Form;
use T9Suite\License\T9Suite_License;

// Prevent direct access to this file.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core initialization class for T9Suite plugin.
 * Implements Singleton pattern to ensure a single instance.
 */
class T9Suite_Init {

    private static $instance;

    /**
     * Initialize the plugin with a singleton instance.
     *
     * @return self The singleton instance of the class.
     */
    public static function init() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to enforce singleton pattern and register classes.
     */
    private function __construct() {
        $this->register_classes();
    }

    /**
     * Register and initialize required classes based on context.
     * Uses WordPress hooks to optimize performance by loading classes only when needed.
     */
    private function register_classes() {
        // Core classes loaded immediately.
        new T9Suite_Helpers();
        new T9Suite_License();
        new T9Suite_Rewrite();
        $this->load_active_modules();

        // Admin-specific classes.
        new T9Suite_Settings();
        

        // Classes loaded on 'init' hook for front-end/back-end compatibility.
        add_action('init', function () {
            new T9Suite_Auth();
            new T9Suite_Form_Handler();
            new T9Suite_Profile_Form();
            new T9Suite_Nonce_Handler();
        });

        add_action('wp_ajax_t9suite_add_department', [$this, 'handle_add_department']);
    }

    /**
     * Automatically detect and load all modules from includes/Modules/.
     */
    private function load_active_modules() {
        $modules_dir = T9SUITE_PLUGIN_DIR . 'includes/Modules/';
        if (!is_dir($modules_dir)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('T9Suite: Modules directory not found - ' . $modules_dir);
            }
            return;
        }

        $module_folders = array_filter(glob($modules_dir . '*'), 'is_dir');
        if (empty($module_folders)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('T9Suite: No modules found in directory - ' . $modules_dir);
            }
            return;
        }

        foreach ($module_folders as $folder) {
            $module_name = strtolower(basename($folder));
            $class_name = "\\T9Suite\\Modules\\" . ucfirst($module_name) . "\\" . ucfirst($module_name) . "Module";
            try {
                if (class_exists($class_name)) {
                    new $class_name();
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("T9Suite: Successfully loaded module - $class_name");
                    }
                } else {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("T9Suite: Module class not found - $class_name");
                    }
                }
            } catch (\Exception $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("T9Suite: Error loading module $module_name - " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Handle AJAX request to add department taxonomy.
     */
    public function handle_add_department() {
        check_ajax_referer('t9suite_add_department_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to add departments.', 't9suite')]);
        }

        $department_name = sanitize_text_field($_POST['department_name'] ?? '');
        if (empty($department_name)) {
            wp_send_json_error(['message' => __('Department name is required.', 't9suite')]);
        }

        $term = wp_insert_term($department_name, 'department');
        if (is_wp_error($term)) {
            wp_send_json_error(['message' => $term->get_error_message()]);
        }

        wp_send_json_success([
            'term_id' => $term['term_id'],
            'name'    => $department_name
        ]);
    }
}
