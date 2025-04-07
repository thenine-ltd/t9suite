<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use T9Suite\Core\T9Suite_Nonce_Handler;
use T9Suite\Settings\T9Suite_Settings;

require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-offcanvas-handler.php';
require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-pages-handler.php';
require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-mobile-menu-handler.php';

global $custom_route;
$custom_route = T9Suite_Settings::get_custom_route();

// Verify nonce if present
if (isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
    T9Suite_Nonce_Handler::verifyGetNonce('t9suite_page_action');
}

// Get route and current page
$action = get_query_var('action', '');
$page   = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'dashboard';

include T9SUITE_PLUGIN_DIR . 'templates/default/partials/header.php';
?>

<div id="main-wrapper">
    <?php include T9SUITE_PLUGIN_DIR . 'templates/default/partials/sidebar.php'; ?>
    <div class="page-wrapper">
        <?php include T9SUITE_PLUGIN_DIR . 'templates/default/partials/topbar.php'; ?>
        <div class="body-wrapper no-m">
            <div class="container-fluid">
                <?php
                switch ($action) {
                    case 'post-type-create':
                        include T9SUITE_PLUGIN_DIR . 'templates/default/pages/post-type-create.php';
                        break;

                    case 'course-builder':
                        include T9SUITE_PLUGIN_DIR . 'templates/default/pages/course-builder.php';
                        break;

                    default:
                        if (class_exists('T9SuitePagesHandler')) {
                            T9SuitePagesHandler::t9suite_render_page_content($page); // Sửa từ render_page_content thành t9suite_render_page_content
                        }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div class="dark-transparent sidebartoggler"></div>

<?php
// Offcanvas menu (tùy kích hoạt)
if (class_exists('T9SuiteMobileMenuHandler')) {
    T9SuiteMobileMenuHandler::render_mobile_menu();
}

include T9SUITE_PLUGIN_DIR . 'templates/default/partials/footer.php';