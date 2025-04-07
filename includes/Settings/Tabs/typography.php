<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Fetch current options
$title_font_size = get_option('t9suite_title_font_size', '16');
$title_font_weight = get_option('t9suite_title_font_weight', '400');
$body_font_size = get_option('t9suite_body_font_size', '14');
$body_font_weight = get_option('t9suite_body_font_weight', '400');
$button_font_size = get_option('t9suite_button_font_size', '14');
$button_font_weight = get_option('t9suite_button_font_weight', '600');
$fonts_list = t9suite_get_google_fonts_list(); // Fetch Google Fonts list

/**
 * Fetch Google Fonts list from API
 *
 * @return array
 */
function t9suite_get_google_fonts_list() {
    $response = wp_remote_get('https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyA7ZqVm13us4-JIf-NegxT1qec7hc_fV6E');
    if (is_wp_error($response)) {
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    $fonts_data = json_decode($body, true);

    return $fonts_data['items'] ?? [];
}
?>

<table class="form-table">
    <!-- Title Typography -->
    <tr>
        <th><?php esc_html_e('Title Font Family', 't9suite'); ?></th>
        <td>
            <select name="t9suite_title_font_family" id="t9suite_title_font_family" class="t9suite-font-select regular-text">
                <?php foreach ($fonts_list as $font) : ?>
                    <option value="<?php echo esc_attr($font['family']); ?>" <?php selected(get_option('t9suite_title_font_family'), $font['family']); ?>>
                        <?php echo esc_html($font['family']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('Title Font Size', 't9suite'); ?></th>
        <td>
            <input type="number" name="t9suite_title_font_size" value="<?php echo esc_attr($title_font_size); ?>" class="small-text">
            <p class="description"><?php esc_html_e('Font size in pixels.', 't9suite'); ?></p>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('Title Font Weight', 't9suite'); ?></th>
        <td>
            <select name="t9suite_title_font_weight" id="t9suite_title_font_weight" class="regular-text">
                <?php foreach (['100', '200', '300', '400', '500', '600', '700', '800', '900'] as $weight) : ?>
                    <option value="<?php echo esc_attr($weight); ?>" <?php selected($title_font_weight, $weight); ?>>
                        <?php echo esc_html($weight); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <!-- Body Typography -->
    <tr>
        <th><?php esc_html_e('Body Font Family', 't9suite'); ?></th>
        <td>
            <select name="t9suite_body_font_family" id="t9suite_body_font_family" class="t9suite-font-select regular-text">
                <?php foreach ($fonts_list as $font) : ?>
                    <option value="<?php echo esc_attr($font['family']); ?>" <?php selected(get_option('t9suite_body_font_family'), $font['family']); ?>>
                        <?php echo esc_html($font['family']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('Body Font Size', 't9suite'); ?></th>
        <td>
            <input type="number" name="t9suite_body_font_size" value="<?php echo esc_attr($body_font_size); ?>" class="small-text">
            <p class="description"><?php esc_html_e('Font size in pixels.', 't9suite'); ?></p>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('Body Font Weight', 't9suite'); ?></th>
        <td>
            <select name="t9suite_body_font_weight" id="t9suite_body_font_weight" class="regular-text">
                <?php foreach (['100', '200', '300', '400', '500', '600', '700', '800', '900'] as $weight) : ?>
                    <option value="<?php echo esc_attr($weight); ?>" <?php selected($body_font_weight, $weight); ?>>
                        <?php echo esc_html($weight); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>

    <!-- Button Typography -->
    <tr>
        <th><?php esc_html_e('Button Font Family', 't9suite'); ?></th>
        <td>
            <select name="t9suite_button_font_family" id="t9suite_button_font_family" class="t9suite-font-select regular-text">
                <?php foreach ($fonts_list as $font) : ?>
                    <option value="<?php echo esc_attr($font['family']); ?>" <?php selected(get_option('t9suite_button_font_family'), $font['family']); ?>>
                        <?php echo esc_html($font['family']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('Button Font Size', 't9suite'); ?></th>
        <td>
            <input type="number" name="t9suite_button_font_size" value="<?php echo esc_attr($button_font_size); ?>" class="small-text">
            <p class="description"><?php esc_html_e('Font size in pixels.', 't9suite'); ?></p>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('Button Font Weight', 't9suite'); ?></th>
        <td>
            <select name="t9suite_button_font_weight" id="t9suite_button_font_weight" class="regular-text">
                <?php foreach (['100', '200', '300', '400', '500', '600', '700', '800', '900'] as $weight) : ?>
                    <option value="<?php echo esc_attr($weight); ?>" <?php selected($button_font_weight, $weight); ?>>
                        <?php echo esc_html($weight); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
</table>