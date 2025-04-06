<?php

namespace T9AdminPro\Core;

if (!defined('ABSPATH')) {
    exit;
}

class T9Admin_Rewrite {

    private $custom_route;

    public function __construct() {
        $this->custom_route = get_option('t9admin_pro_custom_route', 't9admin');

        add_action('init', [$this, 't9admin_pro_add_rewrite_rules']);

        register_activation_hook(T9ADMIN_PRO_PLUGIN_FILE, [$this, 't9admin_pro_flush_rewrite_rules']);
        register_deactivation_hook(T9ADMIN_PRO_PLUGIN_FILE, 'flush_rewrite_rules');

        add_action('template_redirect', [$this, 't9admin_pro_template_redirect']);
    }

    /**
     * Thêm rewrite rules.
     */
    public function t9admin_pro_add_rewrite_rules() {
        $route = $this->custom_route;

        // Rewrite rules
        add_rewrite_rule("^{$route}/?$", 'index.php?t9admin=1', 'top');
        add_rewrite_rule("^{$route}/login/?$", 'index.php?t9admin=1&login=1', 'top');
        add_rewrite_rule("^{$route}/logout/?$", 'index.php?t9admin=1&logout=1', 'top');
        add_rewrite_rule("^{$route}/post-type-create/?$", 'index.php?t9admin=1&action=post-type-create', 'top');
        add_rewrite_rule("^{$route}/post-type-create/([^/]+)/?$", 'index.php?t9admin=1&action=post-type-create&post_type=$matches[1]', 'top');

        add_rewrite_rule("^{$route}/course-builder/?$", 'index.php?t9admin=1&action=course-builder', 'top');
        add_rewrite_rule("^{$route}/course-builder/step-([0-9]+)/?$", 'index.php?t9admin=1&action=course-builder&step=$matches[1]', 'top');
        add_rewrite_rule("^{$route}/create-curriculum/?$", 'index.php?t9admin=1&action=create-curriculum', 'top');
        add_rewrite_rule("^{$route}/review-homework/?$", 'index.php?t9admin=1&action=review-homework', 'top');
        add_rewrite_rule("^{$route}/profile/([0-9]+)/?$", 'index.php?t9admin=1&action=profile&user_id=$matches[1]', 'top');
        add_rewrite_rule("^{$route}/review-homework/?submission_id=([0-9]+)/?$", 'index.php?t9admin=1&action=review-homework&submission_id=$matches[1]', 'top');
        add_rewrite_rule("^{$route}/manage-students/?$", 'index.php?t9admin=1&action=manage-students', 'top');
        add_rewrite_rule("^t9admin/course-study/?$", 'index.php?t9admin=1&action=course-study', 'top');

        // Rewrite tags
        add_rewrite_tag('%t9admin%', '1');
        add_rewrite_tag('%login%', '1');
        add_rewrite_tag('%logout%', '1');
        add_rewrite_tag('%action%', '([^&]+)');
        add_rewrite_tag('%post_type%', '([^&]+)');
        add_rewrite_tag('%course_id%', '([0-9]+)');
        add_rewrite_tag('%submission_id%', '([0-9]+)');
        add_rewrite_tag('%user_id%', '([0-9]+)');
    }

    /**
     * Làm mới rewrite rules.
     */
    public function t9admin_pro_flush_rewrite_rules() {
        $this->t9admin_pro_add_rewrite_rules();
        flush_rewrite_rules();
    }

    /**
     * Xử lý template redirect cho custom routes.
     */
    public function t9admin_pro_template_redirect() {
        $is_t9admin = get_query_var('t9admin');
        $is_login = get_query_var('login');
        $is_logout = get_query_var('logout');
        $action = get_query_var('action');
        $is_logged_in = is_user_logged_in();
        $post_type = get_query_var('post_type');
        $submission_id = get_query_var('submission_id');

        // Xử lý logout
        if ($is_logout) {
            wp_logout();
            wp_redirect(home_url("/{$this->custom_route}/login"));
            exit;
        }

        // Xử lý login
        if ($is_login) {
            if ($is_logged_in) {
                wp_redirect(home_url("/{$this->custom_route}"));
                exit;
            }
            include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/pages/login.php';
            exit;
        }

        // Nếu chưa đăng nhập
        if ($is_t9admin && !$is_logged_in) {
            wp_redirect(home_url("/{$this->custom_route}/login"));
            exit;
        }

        if ($is_t9admin) {
            switch ($action) {
                case 'post-type-create':
                    include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/index.php';
                    exit;
                case 'course-builder':
                    include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/pages/course-builder.php';
                    exit;
                default:
                    include T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/index.php';
                    exit;
            }
        }
    }
}
