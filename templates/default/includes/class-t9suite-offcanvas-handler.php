<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-menu-handler.php';

class T9SuiteOffcanvasHandler {

    public static function render_offcanvas_menu() {
        // Lấy logo, tên công ty, route
        $logo_url     = get_option('t9suite_logo_dark', '');
        $company_name = get_option('t9suite_company_name', esc_html__('Default Company', 't9suite'));
        $custom_route = \T9Suite\Settings\T9Suite_Settings::get_custom_route();
        ?>

        <!-- Offcanvas Sidebar -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="t9suiteSidebar" aria-labelledby="t9suiteSidebarLabel">
            <div class="offcanvas-header">
                <!-- Branding Section -->
                <div class="t9suite-branding d-flex align-items-center">
                    <?php if ($logo_url): ?>
                        <img src="<?php echo esc_url(wp_get_attachment_url($logo_url)); ?>" alt="<?php echo esc_attr($company_name); ?>" class="t9suite-corp-avatar rounded-circle me-3" width="42" height="42">
                    <?php endif; ?>
                    <div>
                        <h1 class="t9suite-corp-name h5 mb-0"><?php echo esc_html($company_name); ?></h1>
                        <span class="t9suite-corp-type text-muted"><?php esc_html_e('Workspace', 't9suite'); ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="<?php esc_html_e('Close', 't9suite'); ?>"></button>
            </div>

            <div class="offcanvas-body">
                <!-- Menu Section -->
                <nav class="t9suite-menu">
                    <?php
                    if (class_exists('T9SuiteMenuHandler')) {
                        $menu_handler = new T9SuiteMenuHandler();
                        $menu_handler->render_nav_menu();
                    }
                    ?>
                </nav>
            </div>

            <div class="offcanvas-footer">
                <!-- Footer Section -->
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-outline-primary w-100">
                    <?php esc_html_e('Back to Homepage', 't9suite'); ?>
                </a>
            </div>
        </div>

        <?php
    }
}
