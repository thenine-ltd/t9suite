<?php
/*
Plugin Name: T9Suite
Plugin URI: https://t9suite.thenine.com/
Description: Pro extension for T9Admin with advanced settings and customization options.
Version: 1.0.0
Author: The Nine
Author URI: https://thenine.vn
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: t9admin-pro
Domain Path: /languages
Requires at least: 5.8
Requires PHP: 7.4
*/

namespace T9AdminPro;

// Prevent direct access to this file.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants with T9ADMIN_PRO prefix globally.
if (!defined('T9ADMIN_PRO_PLUGIN_DIR')) {
    define('T9ADMIN_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('T9ADMIN_PRO_PLUGIN_URL')) {
    define('T9ADMIN_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('T9ADMIN_PRO_PLUGIN_FILE')) {
    define('T9ADMIN_PRO_PLUGIN_FILE', __FILE__);
}
if (!defined('T9ADMIN_PRO_VERSION')) {
    define('T9ADMIN_PRO_VERSION', '1.0.0');
}

/**
 * Class Plugin handles plugin initialization and requirements checking.
 */
class Plugin {

    /**
     * Bootstrap the plugin by registering autoloader and initializing core.
     */
    public static function bootstrap() {
        self::register_autoloader();
        add_action('plugins_loaded', [__CLASS__, 'initialize'], 5);
    }

    /**
     * Register autoloader for plugin classes.
     */
    private static function register_autoloader() {
        spl_autoload_register(function ($class) {
            $prefix   = __NAMESPACE__ . '\\';
            $base_dir = T9ADMIN_PRO_PLUGIN_DIR . 'includes/';

            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                return;
            }

            $relative_class = substr($class, strlen($prefix));
            $file           = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file)) {
                require_once $file;
            } elseif (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("T9Admin Pro: Class file not found - $file");
            }
        });
    }

    /**
     * Check requirements and initialize the plugin core.
     */
    public static function initialize() {
        if (!self::check_requirements()) {
            return;
        }

        load_plugin_textdomain('t9admin-pro', false, basename(T9ADMIN_PRO_PLUGIN_DIR) . '/languages/');
        Core\T9Admin_Init::init();
    }

    /**
     * Check PHP version and optionally T9Admin dependency.
     *
     * @return bool True if requirements are met, false otherwise.
     */
    private static function check_requirements() {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function () {
                echo '<div class="error"><p>' . esc_html__('T9Admin Pro requires PHP 7.4 or higher. Current version: ', 't9admin-pro') . PHP_VERSION . '</p></div>';
            });
            return false;
        }
        return true;
    }

    /**
     * Handle plugin uninstallation cleanup.
     */
    public static function uninstall() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        delete_option('t9admin_pro_settings');
    }
}

// Bootstrap the plugin.
Plugin::bootstrap();

// Register uninstall hook.
register_uninstall_hook(T9ADMIN_PRO_PLUGIN_FILE, [Plugin::class, 'uninstall']);