<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use T9AdminPro\Core\T9Admin_Nonce_Handler;
use T9AdminPro\Settings\T9Admin_Settings;

require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-offcanvas-handler.php';
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-pages-handler.php';
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-mobile-menu-handler.php';

global $custom_route;
$custom_route = T9Admin_Settings::get_custom_route();

include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/partials/header.php'; 

if (isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
    T9Admin_Nonce_Handler::verifyGetNonce('t9admin_pro_page_action');
} 

$action = get_query_var('action', '');
$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'dashboard';
?>

  <div id="main-wrapper">
    <?php include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/partials/sidebar.php'; ?>
    <div class="page-wrapper">
      <?php include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/partials/topbar.php'; ?>
      <div class="body-wrapper no-m">
        <div class="container-fluid">
          <?php
            switch ($action) {
                case 'post-type-create':
                    include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/pages/post-type-create.php';
                    break;
                case 'course-builder':
                    include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/pages/course-builder.php';
                    break;
                default:
                    if (class_exists('T9AdminProPagesHandler')) {
                        T9AdminProPagesHandler::t9admin_pro_render_page_content($page);
                    }
            }
            ?>
      </div>
    </div>

  </div>
  <div class="dark-transparent sidebartoggler"></div>
<?php
// if (class_exists('T9AdminProOffcanvasHandler')) {
//     T9AdminProOffcanvasHandler::t9admin_pro_render_offcanvas_menu();
// }
if (class_exists('T9AdminProMobileMenuHandler')) {
    T9AdminProMobileMenuHandler::t9admin_pro_render_mobile_menu();
}
include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/partials/footer.php'; 