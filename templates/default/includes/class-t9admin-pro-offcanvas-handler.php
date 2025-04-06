<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-menu-handler.php';

class T9AdminProOffcanvasHandler {

    public static function t9admin_pro_render_offcanvas_menu() {
        // Get settings
        $logo_url = get_option('t9admin_pro_logo_dark', ''); // Default dark logo
        $company_name = get_option('t9admin_pro_company_name', esc_html__('Default Company', 't9admin-pro'));
        $custom_route = T9ProSettings::get_custom_route();

        ?>
        <!-- Offcanvas Sidebar -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="t9adminSidebar" aria-labelledby="t9adminSidebarLabel">
            <div class="offcanvas-header">
                <!-- Branding Section -->
                <div class="t9admin-branding d-flex align-items-center">
                    <?php if ($logo_url): ?>
                        <img src="<?php echo esc_url(wp_get_attachment_url($logo_url)); ?>" alt="<?php echo esc_attr($company_name); ?>" class="t9admin-corp-avatar rounded-circle me-3" width="42" height="42">
                    <?php endif; ?>
                    <div>
                        <h1 class="t9admin-corp-name h5 mb-0"><?php echo esc_html($company_name); ?></h1>
                        <span class="t9admin-corp-type text-muted"><?php esc_html_e('Workspace', 't9admin-pro'); ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="<?php esc_html_e('Close', 't9admin-pro'); ?>"></button>
            </div>
            <div class="offcanvas-body">
                <!-- Menu Section -->
                <nav class="t9admin-menu">
                        <?php
                        if (class_exists('T9AdminProMenuHandler')) {
                            $menu_handler = new T9AdminProMenuHandler();
                            $menu_handler->t9admin_pro_render_nav_menu();
                        } 
                        ?>
                </nav>
            </div>
            <div class="offcanvas-footer">
                <!-- Footer Section -->
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-outline-primary w-100">
                    <?php esc_html_e('Back to Homepage', 't9admin-pro'); ?>
                </a>
            </div>
        </div>
        <?php
    }
}
