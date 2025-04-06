<?php
use T9AdminPro\Settings\T9Admin_Settings; // Thêm namespace đúng

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9AdminProMobileMenuHandler {

    /**
     * Render the mobile footer menu
     */
    public static function t9admin_pro_render_mobile_menu() {
        // Fetch custom route and badge count dynamically
        $custom_route = T9Admin_Settings::get_custom_route(); // Sửa từ T9ProSettings thành T9Admin_Settings
        $notification_count = self::get_unread_notifications_count();
        ?>
        <nav class="t9admin-mobile-menu navbar fixed-bottom navbar-light bg-white d-md-none">
            <div class="container-fluid">
                <ul class="nav flex-nowrap justify-content-between w-100">
                    <!-- Home Button -->
                    <li class="nav-item">
                        <a href="<?php echo esc_url(home_url("/{$custom_route}/?page=dashboard")); ?>" class="nav-link text-center">
                            <i class="bi bi-house"></i>
                        </a>
                    </li>

                    <!-- Menu Button -->
                    <li class="nav-item">
                        <a class="nav-link nav-icon-hover-bg rounded-circle  sidebartoggler " id="headerCollapse" href="javascript:void(0)">
                            <i class="bi bi-list"></i> 
                        </a>
                    </li>

                    <!-- Highlighted Button -->
                    <li class="nav-item">
                        <a href="#" class="text-center btn btn-primary px-0 rounded-circle t9admin-highlighted-button">
                            <i class="bi bi-qr-code-scan"></i>
                        </a>
                    </li>

                    <!-- Notification Button -->
                    <li class="nav-item">
                        <a href="<?php echo esc_url(home_url("/{$custom_route}/?page=notifications")); ?>" class="nav-link text-center position-relative">
                            <i class="bi bi-bell"></i>
                            <?php if ($notification_count > 0) : ?>
                                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                    <?php echo esc_html($notification_count); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Profile Button -->
                    <li class="nav-item">
                        <a href="" class="nav-link text-center">
                            <i class="bi bi-chat"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <?php
    }

    /**
     * Get the count of unread notifications
     *
     * @return int The count of unread notifications
     */
    private static function get_unread_notifications_count() {
        // Replace this with your actual logic for fetching unread notifications
        return apply_filters('t9admin_pro_unread_notifications_count', 0);
    }
}