<?php
namespace T9AdminPro\Core;

use T9AdminPro\Settings\T9Admin_Settings;
use T9AdminPro\Utils\T9Admin_Helpers;
use T9AdminPro\Core\T9Admin_Rewrite;
use T9AdminPro\Core\T9Admin_Auth;
use T9AdminPro\Core\T9Admin_Nonce_Handler;
use T9AdminPro\Forms\T9Admin_Form_Handler;
use T9AdminPro\Forms\T9Admin_Profile_Form;
use T9AdminPro\License\T9Admin_License;

// Prevent direct access to this file.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core initialization class for T9Admin Pro plugin.
 * Implements Singleton pattern to ensure a single instance.
 */
class T9Admin_Init {

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
        new T9Admin_Helpers();
        new T9Admin_License();
        new T9Admin_Rewrite();
                    $this->load_active_modules(); 

        // Admin-specific classes.
        if (is_admin()) {
            new T9Admin_Settings();
        }

        // Classes loaded on 'init' hook for front-end/back-end compatibility.
        add_action('init', function () {
            new T9Admin_Auth();
            new T9Admin_Form_Handler();
            new T9Admin_Profile_Form();
            new T9Admin_Nonce_Handler();

        });
        
        add_action('wp_ajax_t9admin_pro_add_department', [$this, 'handle_add_department']);
    }

    /**
     * Automatically detect and load active modules from includes/Modules/.
     */
    /**
     * Automatically detect and load all modules from includes/Modules/.
     * Temporarily activates all modules regardless of settings.
     */
    private function load_active_modules() {
        $modules_dir = T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/';
        if (!is_dir($modules_dir)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('T9Admin Pro: Modules directory not found - ' . $modules_dir);
            }
            return;
        }

        $module_folders = array_filter(glob($modules_dir . '*'), 'is_dir');
        if (empty($module_folders)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('T9Admin Pro: No modules found in directory - ' . $modules_dir);
            }
            return;
        }

        foreach ($module_folders as $folder) {
            $module_name = strtolower(basename($folder));
            $class_name = "\\T9AdminPro\\Modules\\" . ucfirst($module_name) . "\\" . ucfirst($module_name) . "Module";
            try {
                if (class_exists($class_name)) {
                    new $class_name();
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("T9Admin Pro: Successfully loaded module - $class_name");
                    }
                } else {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log("T9Admin Pro: Module class not found - $class_name");
                    }
                }
            } catch (\Exception $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("T9Admin Pro: Error loading module $module_name - " . $e->getMessage());
                }
            }
        }
    }
    
    public function handle_add_department() {
        check_ajax_referer('t9admin_pro_add_department_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to add departments.', 't9admin-pro')]);
        }
    
        $department_name = sanitize_text_field($_POST['department_name'] ?? '');
        if (empty($department_name)) {
            wp_send_json_error(['message' => __('Department name is required.', 't9admin-pro')]);
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