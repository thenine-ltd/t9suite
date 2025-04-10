<?php
use T9Suite\License\T9Suite_License;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['t9suite_license_nonce'])) {
    if (wp_verify_nonce($_POST['t9suite_license_nonce'], 't9suite_save_license')) {
        $license_key = sanitize_text_field($_POST['t9suite_license_key']);
        T9Suite_License::save_license($license_key);
        $license_status = T9Suite_License::check_license_status();
        $message = ($license_status === 'valid') 
            ? __('License activated successfully!', 't9suite') 
            : __('Invalid license key. Please try again.', 't9suite');
    }
}
$current_license = get_option('t9suite_license_key', '');
?>

<div class="wrap">
    <h2><?php esc_html_e('License Activation', 't9suite'); ?></h2>
    <form method="post">
        <?php wp_nonce_field('t9suite_save_license', 't9suite_license_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('License Key', 't9suite'); ?></th>
                <td>
                    <input type="text" name="t9suite_license_key" value="<?php echo esc_attr($current_license); ?>" class="regular-text" required>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php esc_attr_e('Save License', 't9suite'); ?>">
        </p>
        <?php if (isset($message)) : ?>
            <p><strong><?php echo esc_html($message); ?></strong></p>
        <?php endif; ?>
    </form>
</div>