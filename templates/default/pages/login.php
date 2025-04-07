<?php

use T9Suite\Forms\T9Suite_Auth_Form;
use T9Suite\Settings\T9Suite_Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Xử lý form đăng nhập
T9Suite_Auth_Form::handleLoginForm();

$custom_route = T9Suite_Settings::get_custom_route();
$logo_light_url = get_option('t9suite_logo_light', T9SUITE_PLUGIN_URL . 'assets/images/default-logo-light.svg');
$assets_url = T9SUITE_PLUGIN_URL . 'assets/';

include T9SUITE_PLUGIN_DIR . 'templates/default/partials/header.php'; 
?>

<div id="main-wrapper">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100">
        <div class="position-relative z-index-5">
            <div class="row gx-0">

                <!-- Left Side: Form -->
                <div class="col-lg-6 col-xl-5 col-xxl-4">
                    <div class="min-vh-100 bg-body row justify-content-center align-items-center p-5">
                        <div class="col-12 auth-card">
                            <div class="mb-4">
                                <img src="<?php echo esc_url($logo_light_url); ?>" alt="<?php esc_attr_e('Company Logo', 't9suite'); ?>" width="88">
                            </div>
                            <h2 class="mb-2 mt-4 fs-7 fw-bolder"><?php esc_html_e('Sign In', 't9suite'); ?></h2>
                            <p class="mb-9"><?php esc_html_e('Please input account information', 't9suite'); ?></p>

                            <form method="POST">
                                <?php wp_nonce_field('t9suite_login_action'); ?>

                                <div class="form-floating mb-3">
                                    <input type="text" name="username" id="username" class="form-control" placeholder="<?php esc_attr_e('Username', 't9suite'); ?>" required autocomplete="username" />
                                    <label for="username"><?php esc_html_e('Username', 't9suite'); ?></label>
                                </div>

                                <div class="form-floating mb-4">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="<?php esc_attr_e('Password', 't9suite'); ?>" required autocomplete="current-password" />
                                    <label for="password"><?php esc_html_e('Password', 't9suite'); ?></label>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember" />
                                        <label class="form-check-label" for="rememberMe"><?php esc_html_e('Remember Me', 't9suite'); ?></label>
                                    </div>
                                    <a class="text-primary fw-medium" href="#"><?php esc_html_e('Forgot Password?', 't9suite'); ?></a>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-8 rounded-2">
                                    <?php esc_html_e('Login', 't9suite'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Branding/Description -->
                <div class="col-lg-6 col-xl-7 col-xxl-8 position-relative overflow-hidden bg-dark d-none d-lg-block">
                    <div class="d-flex align-items-center h-n80 z-index-5 position-relative">
                        <div class="row justify-content-center w-100">
                            <div class="col-lg-6 text-center text-white">
                                <h2><?php esc_html_e('Welcome to T9Suite', 't9suite'); ?></h2>
                                <p><?php esc_html_e('Your powerful, customizable admin dashboard.', 't9suite'); ?></p>
                                <a href="#" class="btn btn-primary"><?php esc_html_e('Learn More', 't9suite'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
include T9SUITE_PLUGIN_DIR . 'templates/default/partials/footer.php'; 
?>