<?php
namespace T9AdminPro\Modules\Hrm;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * HRM Module for T9AdminPro.
 * Manages Staffs and related functionalities.
 */
class HrmModule {

    public function __construct() {
        // Check if required constants are defined
        if (!defined('T9ADMIN_PRO_PLUGIN_DIR')) {
            return; // Exit if plugin directory constant is not defined
        }

        // Load Staffs CPT
        require_once T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/Hrm/Staffs/StaffsCPT.php';
        new Staffs\StaffsCPT();

        // Load Staffs Metabox
        require_once T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/Hrm/Staffs/StaffsMetabox.php';
        Staffs\StaffsMetabox::init();
    }
}

// Initialize HRM Module
new HrmModule();