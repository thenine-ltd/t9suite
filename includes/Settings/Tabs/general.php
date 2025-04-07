<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
settings_fields('t9suite_general_settings');
?>
<table class="form-table">
    <!-- Custom Route -->
    <tr>
        <th><?php esc_html_e('Custom Route', 't9suite'); ?></th>
        <td>
            <input type="text" name="t9suite_custom_route" value="<?php echo esc_attr(get_option('t9suite_custom_route', 't9suite')); ?>" class="regular-text">
            <p class="description"><?php esc_html_e('Default: t9suite', 't9suite'); ?></p>
        </td>
    </tr>

    <!-- Company Name -->
    <tr>
        <th><?php esc_html_e('Company Name', 't9suite'); ?></th>
        <td>
            <input type="text" name="t9suite_company_name" value="<?php echo esc_attr(get_option('t9suite_company_name', '')); ?>" class="regular-text">
            <p class="description"><?php esc_html_e('Enter your company name.', 't9suite'); ?></p>
        </td>
    </tr>

    <!-- Logo Dark -->
    <tr>
        <th><?php esc_html_e('Logo Dark', 't9suite'); ?></th>
        <td>
            <?php 
            $logo_dark = get_option('t9suite_logo_dark', '');
            ?>
            <input type="hidden" name="t9suite_logo_dark" id="t9suite_logo_dark" value="<?php echo esc_attr($logo_dark); ?>">
            <button type="button" class="button t9suite-upload-button" data-target="t9suite_logo_dark">
                <?php esc_html_e('Upload Logo', 't9suite'); ?>
            </button>
            <div id="t9suite_logo_dark_preview" style="margin-top: 10px;">
                <?php
                if ($logo_dark) {
                    echo '<img src="' . esc_url($logo_dark) . '" style="max-width: 100px;" alt="Logo Dark Preview">';
                }
                ?>
            </div>
        </td>
    </tr>

    <!-- Logo Light -->
    <tr>
        <th><?php esc_html_e('Logo Light', 't9suite'); ?></th>
        <td>
            <?php 
            $logo_light = get_option('t9suite_logo_light', '');
            ?>
            <input type="hidden" name="t9suite_logo_light" id="t9suite_logo_light" value="<?php echo esc_attr($logo_light); ?>">
            <button type="button" class="button t9suite-upload-button" data-target="t9suite_logo_light">
                <?php esc_html_e('Upload Logo', 't9suite'); ?>
            </button>
            <div id="t9suite_logo_light_preview" style="margin-top: 10px;">
                <?php
                if ($logo_light) {
                    echo '<img src="' . esc_url($logo_light) . '" style="max-width: 100px;" alt="Logo Light Preview">';
                }
                ?>
            </div>
        </td>
    </tr>

    <!-- Logo Width -->
    <tr>
        <th><?php esc_html_e('Logo Width', 't9suite'); ?></th>
        <td>
            <input type="number" name="t9suite_logo_width" value="<?php echo esc_attr(get_option('t9suite_logo_width', 100)); ?>" class="small-text">
            <p class="description"><?php esc_html_e('Set logo width in pixels.', 't9suite'); ?></p>
        </td>
    </tr>

    <!-- Contact Email -->
    <tr>
        <th><?php esc_html_e('Contact Email', 't9suite'); ?></th>
        <td>
            <input type="email" name="t9suite_contact_email" value="<?php echo esc_attr(get_option('t9suite_contact_email')); ?>" class="regular-text">
        </td>
    </tr>

    <!-- Contact Phone -->
    <tr>
        <th><?php esc_html_e('Contact Phone', 't9suite'); ?></th>
        <td>
            <input type="text" name="t9suite_contact_phone" value="<?php echo esc_attr(get_option('t9suite_contact_phone')); ?>" class="regular-text">
        </td>
    </tr>

    <!-- Contact Facebook -->
    <tr>
        <th><?php esc_html_e('Contact Facebook', 't9suite'); ?></th>
        <td>
            <input type="url" name="t9suite_contact_fb" value="<?php echo esc_url(get_option('t9suite_contact_fb')); ?>" class="regular-text">
        </td>
    </tr>

    <!-- Dark Mode Toggle -->
    <tr>
        <th><?php esc_html_e('Dark Mode Toggle', 't9suite'); ?></th>
        <td>
            <label class="switch">
                <input type="checkbox" name="t9suite_dark_mode_toggle" value="yes" <?php checked(get_option('t9suite_dark_mode_toggle'), 'yes'); ?>>
                <span class="slider round"></span>
            </label>
        </td>
    </tr>
</table>