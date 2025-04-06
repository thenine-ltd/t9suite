<?php
use T9AdminPro\Settings\T9Admin_Settings;

if (!defined('ABSPATH')) {
    exit;
}

class T9AdminProMenuHandler {

    private $custom_route;
    private $current_page;
    private $current_post_type;
    private $menu_items;

    public function __construct() {
        $this->custom_route = T9Admin_Settings::get_custom_route();
        $this->current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'dashboard';
        $this->current_post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
        $this->menu_items = get_option('t9admin_pro_menu_items', []);
    }

    /**
     * Render the navigation menu
     * @param array $mini_nav_items Các parent menu từ settings với children
     */
    public function t9admin_pro_render_nav_menu($mini_nav_items = []) {
        $base_url = home_url("/{$this->custom_route}/");

        // Nav cố định cho Dashboard
        ?>
        <nav class="sidebar-nav" id="nav-1" data-simplebar>
            <ul class="sidebar-menu" id="sidebarnav">
                <?php
                $this->t9admin_pro_render_menu_item('dashboard', esc_html__('Dashboard', 't9admin-pro'), 'bi-speedometer2', $base_url);
                $current_user = wp_get_current_user();
                if (in_array('student', $current_user->roles) || in_array('instructor', $current_user->roles)) {
                    $this->t9admin_pro_render_menu_item('my-course', esc_html__('My Courses', 't9admin-pro'), 'lni lni-book-1', $base_url, true);
                    $this->t9admin_pro_render_menu_item('homework', esc_html__('My Homework', 't9admin-pro'), 'lni lni-bookmark-1', $base_url, true);
                }
                ?>
            </ul>
        </nav>
        <?php

        // Các nav từ settings
        foreach ($mini_nav_items as $parent) {
            $nav_id = "nav-" . ($parent['index'] + 2); // Dùng index gốc từ settings + 2
            ?>
            <nav class="sidebar-nav" id="<?php echo esc_attr($nav_id); ?>" data-simplebar>
                <ul class="sidebar-menu" id="sidebarnav">
                    <?php
                    if (!empty($parent['children'])) {
                        foreach ($parent['children'] as $item) {
                            if ($item['post_type'] !== 'label' && $item['post_type'] !== 'hr') {
                                $this->t9admin_pro_render_menu_item($item['post_type'], $item['label'], $item['icon'] ?? 'lni-menu', $base_url, true);
                            } else {
                                $this->t9admin_pro_render_menu_item($item['post_type'], $item['label'], '', $base_url, true);
                            }
                        }
                    } else {
                        echo '<li><span class="nav-text">' . esc_html__('No child menu items available.', 't9admin-pro') . '</span></li>';
                    }
                    ?>
                </ul>
            </nav>
            <?php
        }
    }

    /**
     * Render a single menu item
     */
    private function t9admin_pro_render_menu_item($slug, $label, $icon_class, $base_url, $is_child = false) {
        $class = $is_child ? 'child-menu' : '';
        if ($slug === 'label') {
            ?>
            <li class="nav-small-cap menu-label-item <?php echo esc_attr($class); ?>" data-menu-type="label">
                <span class="hide-menu"><?php echo esc_html($label); ?></span>
            </li>
            <?php
        } elseif ($slug === 'hr') {
            ?>
            <li class="menu-hr-item <?php echo esc_attr($class); ?>" data-menu-type="hr">
                <span class="sidebar-divider lg"></span>
            </li>
            <?php
        } else {
            $active_class = ($this->current_page === $slug || $this->current_post_type === $slug) ? 'active' : '';
            ?>
            <li class="sidebar-item <?php echo esc_attr($class); ?>">
                <a href="<?php echo esc_url(add_query_arg('page', $slug, $base_url)); ?>" 
                   class="sidebar-link <?php echo esc_attr($active_class); ?>" 
                   aria-expanded="false">
                    <i class="<?php echo esc_attr($icon_class); ?>"></i>
                    <span class="nav-text hide-menu"><?php echo esc_html($label); ?></span>
                </a>
            </li>
            <?php
        }
    }
}