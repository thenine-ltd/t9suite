<?php

namespace T9Suite\Core;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class T9Suite_Auth {

    /**
     * Check if the user is logged in
     * 
     * @return bool
     */
    public function is_logged_in() {
        return is_user_logged_in();
    }

    /**
     * Log in the user with provided credentials
     * 
     * @param string $username
     * @param string $password
     * @return \WP_User|\WP_Error
     */
    public function login($username, $password) {
        $credentials = [
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        ];

        $user = wp_signon($credentials, false);

        if (is_wp_error($user)) {
            return $user;
        }

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        return $user;
    }

    /**
     * Log out the current user
     */
    public function logout() {
        $custom_route = \T9Suite\Settings\T9Suite_Settings::get_custom_route();
        wp_logout();
        wp_redirect(home_url("/{$custom_route}/login"));
        exit;
    }

    /**
     * Handle login form submission
     */
    public function handle_login() {
        $custom_route = \T9Suite\Settings\T9Suite_Settings::get_custom_route();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['t9_login_action'])) {
            // Verify nonce
            if (isset($_POST['_wpnonce'])) {
                $nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
                if (!wp_verify_nonce($nonce, 't9suite_login_action')) {
                    wp_die(esc_html__('Invalid request. Nonce verification failed.', 't9suite'));
                }
            } else {
                wp_die(esc_html__('Invalid request. Missing nonce.', 't9suite'));
            }

            // Sanitize and process login credentials
            $username = sanitize_text_field(wp_unslash($_POST['username'] ?? ''));
            $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));

            $login_result = $this->login($username, $password);

            if (is_wp_error($login_result)) {
                wp_redirect(add_query_arg('login_error', '1', home_url("/{$custom_route}/login")));
                exit;
            }

            wp_redirect(home_url("/{$custom_route}"));
            exit;
        }
    }

    /**
     * Check if the current user has access to the course builder
     */
    public function course_builder_access_check() {
        $current_user = wp_get_current_user();
        $allowed_roles = ['administrator', 'courses_manager', 'instructor'];

        if (!array_intersect($allowed_roles, $current_user->roles)) {
            wp_die(esc_html__('Sorry, you are not allowed to access this page.', 't9suite'));
        }
    }
}
