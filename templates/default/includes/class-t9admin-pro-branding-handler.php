<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class T9AdminProBrandingHandler {

    /**
     * Render the branding section with logo switching functionality.
     */
    public static function t9admin_pro_render_branding() {
        // Get branding settings
        $logoDarkUrl = get_option('t9admin_pro_logo_dark');
        $logoLightUrl = get_option('t9admin_pro_logo_light');
        $logoWidth = get_option('t9admin_pro_logo_width', '120'); // Default width if not set
        $companyName = get_option('t9admin_pro_company_name', esc_html__('Default Team Name', 't9admin-pro'));

        ?>
        <div class="brand-logo d-flex align-items-center nav-logo">
            <a href="#" class="text-nowrap logo-img">
                <img
                    src="<?php echo esc_url($logoLightUrl); ?>"
                    alt="<?php echo esc_attr($companyName); ?> Logo"
                    class="t9admin-pro-logo"
                    data-dark-logo="<?php echo esc_url($logoDarkUrl); ?>"
                    data-light-logo="<?php echo esc_url($logoLightUrl); ?>"
                    width="<?php echo esc_attr($logoWidth); ?>"
                />
            </a>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const htmlTag = document.documentElement;
                const logoImg = document.querySelector('.t9admin-pro-logo');

                function switchLogo() {
                    const theme = htmlTag.getAttribute('data-bs-theme') || 'light';
                    logoImg.src = theme === 'dark'
                        ? logoImg.getAttribute('data-dark-logo')
                        : logoImg.getAttribute('data-light-logo');
                }

                // Initial setup when the page loads
                switchLogo();

                // Observe changes in the theme
                const observer = new MutationObserver(() => switchLogo());
                observer.observe(htmlTag, { attributes: true, attributeFilter: ['data-bs-theme'] });
            });
        </script>
        <?php
    }
}
