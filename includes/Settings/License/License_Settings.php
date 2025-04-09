<?php
namespace T9Suite\Settings\License;

use T9Suite\License\T9Suite_License;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Controller xử lý trang License cho T9Suite Settings.
 */
class License_Settings {

    public function register_menu() {
        add_menu_page(
            __('T9Suite License', 't9suite'),
            '1. License',
            'manage_options',
            't9suite-license',
            [$this, 'render_page'],
            '',
            61
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 't9suite'));
        }
    
        $active_menu = $_GET['license_tab'] ?? 'license';
        ?>
        <div class="wrap" id="t9-license-wrapper">
            <h1 class="wp-heading-inline">🔐 License Management</h1>
            <div style="display: flex; gap: 30px; margin-top: 20px;">
                <!-- Sidebar -->
                <div style="width: 200px; border-right: 1px solid #ddd;">
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=t9suite-license&license_tab=license')); ?>"
                               class="t9-license-menu <?php echo $active_menu === 'license' ? 'active' : ''; ?>">
                                📄 License
                            </a>
                        </li>
                        <li>
                            <a href="https://thenine.vn/client" target="_blank" class="t9-license-menu">
                                📘 Documentation
                            </a>
                        </li>
                    </ul>
                </div>
    
                <!-- Content Area -->
                <div style="flex: 1;">
                    <?php if ($active_menu === 'license') : ?>
                        <?php $this->render_license_cards(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    
        <style>
            .t9-license-menu {
                display: block;
                padding: 10px 15px;
                background: #f9f9f9;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 500;
                color: #333;
            }
            .t9-license-menu.active {
                background: #0073aa;
                color: white;
            }
        </style>
        <?php
    }

    private function render_license_cards() {
        // Handle license form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['t9suite_license_nonce']) &&
            wp_verify_nonce($_POST['t9suite_license_nonce'], 't9suite_save_license')) {
    
            $submitted_key = sanitize_text_field($_POST['license_key'] ?? '');
            \T9Suite\License\T9Suite_License::save_license($submitted_key);
    
            if (empty($submitted_key)) {
                add_settings_error('t9suite_license', 'license_detached', __('License detached successfully.', 't9suite'), 'updated');
            } elseif (\T9Suite\License\T9Suite_License::is_license_valid()) {
                add_settings_error('t9suite_license', 'license_success', __('License activated successfully!', 't9suite'), 'success');
            } else {
                add_settings_error('t9suite_license', 'license_invalid', __('Invalid license key or does not match the product.', 't9suite'), 'error');
            }
        }
    
        $license_key    = get_option('t9suite_license_key', '');
        $license_data   = \T9Suite\License\T9Suite_License::check_license_status();
        $is_valid       = $license_data['status'] === 'valid';
        $version_status = T9SUITE_VERSION === '3.4.8' ? 'up-to-date' : 'update-required';
    
        // Status text
        switch ($license_data['status']) {
            case 'valid':
                $status_text = '<span style="color:green;font-weight:bold;">✅ Activated</span>';
                break;
            case 'expired':
                $status_text = '<span style="color:orange;font-weight:bold;">⚠️ Expired</span>';
                break;
            case 'wrong_product':
                $status_text = '<span style="color:red;font-weight:bold;">❌ Wrong Product</span>';
                break;
            default:
                $status_text = '<span style="color:red;font-weight:bold;">❌ Not Activated</span>';
                break;
        }
    
        // Days left text
        $days_left_text = '';
        if (!empty($license_data['expires_at'])) {
            $expires_at = strtotime($license_data['expires_at']);
            $now        = time();
            $days_left  = floor(($expires_at - $now) / (60 * 60 * 24));
    
            if ($days_left > 0) {
                $days_left_text = sprintf(__('⏳ %d day(s) left until expiration.', 't9suite'), $days_left);
            } elseif ($days_left === 0) {
                $days_left_text = __('⚠️ Expires today!', 't9suite');
            } else {
                $days_left_text = __('❌ Already expired.', 't9suite');
            }
        }
    
        settings_errors('t9suite_license');
        ?>
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
    
            <!-- License Card -->
            <div style="flex:1; min-width:300px; background:#fff; padding:20px; border-radius:10px; border:1px solid #eee;">
                <h2>🔐 License</h2>
                <p><strong>Status:</strong> <?php echo $status_text; ?></p>
    
                <?php if (!empty($license_data['activated_at'])): ?>
                    <p><strong>Activated At:</strong> <?php echo esc_html(date('Y-m-d', strtotime($license_data['activated_at']))); ?></p>
                <?php endif; ?>
    
                <?php if (!empty($license_data['expires_at'])): ?>
                    <p><strong>Expires At:</strong> <?php echo esc_html(date('Y-m-d', strtotime($license_data['expires_at']))); ?></p>
                    <p style="color:<?php echo ($days_left_text && $days_left <= 3) ? 'red' : '#0073aa'; ?>">
                        <?php echo esc_html($days_left_text); ?>
                    </p>
                <?php endif; ?>
    
                <?php if ($is_valid): ?>
                    <form method="post">
                        <?php wp_nonce_field('t9suite_save_license', 't9suite_license_nonce'); ?>
                        <input type="hidden" name="license_key" value="">
                        <?php submit_button(__('Detach License', 't9suite'), 'delete'); ?>
                    </form>
                <?php else: ?>
                    <form method="post">
                        <?php wp_nonce_field('t9suite_save_license', 't9suite_license_nonce'); ?>
                        <input type="text" name="license_key" value="" class="regular-text" placeholder="Enter license key">
                        <?php submit_button(__('Activate License', 't9suite')); ?>
                    </form>
                <?php endif; ?>
            </div>
    
            <!-- Version Card -->
            <div style="flex:1; min-width:300px; background:#fff; padding:20px; border-radius:10px; border:1px solid #eee;">
                <h2>📦 Version</h2>
                <?php if ($version_status === 'update-required') : ?>
                    <div style="background:#fff3cd; padding:10px; border-radius:5px; border:1px solid #ffeeba;">
                        <strong>Update Required</strong><br>
                        You are using <code>v<?php echo T9SUITE_VERSION; ?></code>. Please update to <code>v3.4.8</code>.
                    </div>
                <?php else : ?>
                    <div style="background:#d4edda; padding:10px; border-radius:5px; border:1px solid #c3e6cb;">
                        <strong>No Issues</strong><br>
                        You’re using the latest version.
                    </div>
                <?php endif; ?>
            </div>
    
        </div>
        <?php
    }
    
    
    
    
    
}
