<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9AdminProSupportHandler {

    /**
     * Render the support section dynamically.
     */
    public static function t9admin_pro_render_support_section() {
        // Fetch contact details from settings
        $contact_phone = get_option('t9admin_pro_contact_phone', '');
        $contact_email = get_option('t9admin_pro_contact_email', '');
        $contact_fb = get_option('t9admin_pro_contact_fb', '');

        // Determine the support contact link
        $support_link = '';
        $support_text = __('Contact Now', 't9admin-pro');

        if (!empty($contact_phone)) {
            $support_link = 'tel:' . esc_attr($contact_phone);
        } elseif (!empty($contact_email)) {
            $support_link = 'mailto:' . esc_attr($contact_email);
        } elseif (!empty($contact_fb)) {
            $support_link = esc_url($contact_fb);
            $support_text = __('Contact via Facebook', 't9admin-pro');
        }

        // Render the support section
        ?>
        <div class="t9admin-support-section" aria-labelledby="helpTitle">
            <h2 id="helpTitle" class="t9admin-support-title"><?php esc_html_e('Support', 't9admin-pro'); ?></h2>
            <p class="t9admin-support-text">
                <?php esc_html_e('Nếu có vấn đề trong sử dụng. Hãy liên hệ team CAP.', 't9admin-pro'); ?>
            </p>
            <?php if (!empty($support_link)) : ?>
                <a href="<?php echo esc_url($support_link); ?>" class="t9admin-support-button"><?php echo esc_html($support_text); ?></a>
            <?php else : ?>
                <button type="button" class="t9admin-support-button" disabled><?php esc_html_e('Contact Now', 't9admin-pro'); ?></button>
            <?php endif; ?>
        </div>
        <?php
    }
}
