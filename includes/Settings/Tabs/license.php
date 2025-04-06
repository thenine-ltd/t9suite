<?php
use T9AdminPro\License\T9Admin_License;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['t9_license_nonce'])) {
    if (wp_verify_nonce($_POST['t9_license_nonce'], 't9_save_license')) {
        $license_key = sanitize_text_field($_POST['t9admin_pro_license_key']);
        T9Admin_License::save_license($license_key);
        $license_status = T9Admin_License::check_license_status();
        $message = ($license_status === 'valid') 
            ? __('License activated successfully!', 't9admin-pro') 
            : __('Invalid license key. Please try again.', 't9admin-pro');
    }
}
$current_license = get_option('t9admin_pro_license_key', '');
?>

<div class="wrap">
    <h2><?php esc_html_e('License Activation', 't9admin-pro'); ?></h2>
    <form method="post">
        <?php wp_nonce_field('t9_save_license', 't9_license_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('License Key', 't9admin-pro'); ?></th>
                <td>
                    <input type="text" name="t9admin_pro_license_key" value="<?php echo esc_attr($current_license); ?>" class="regular-text" required>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php esc_attr_e('Save License', 't9admin-pro'); ?>">
        </p>
        <?php if (isset($message)) : ?>
            <p><strong><?php echo esc_html($message); ?></strong></p>
        <?php endif; ?>
    </form>
</div>
