<?php

if (!defined('ABSPATH')) exit;

class T9AdminProAssetsManager {

    private $assets;

    public function __construct() {
        $assets_url = plugins_url('templates/default/assets/', T9ADMIN_PRO_PLUGIN_FILE);
        $version = defined('T9ADMIN_PRO_VERSION') ? T9ADMIN_PRO_VERSION : '1.0.0';

        $this->assets = [
            'css' => [
                ['handle' => 't9admin_pro_bootstrap_css', 'src' => $assets_url . 'css/bootstrap.min.css', 'ver' => '5.3.0'],
                ['handle' => 't9admin_pro_bootstrap_icons_css', 'src' => $assets_url . 'css/bootstrap-icons.min.css', 'ver' => '1.11.3'],
                ['handle' => 't9admin_pro_lineicons_css', 'src' => $assets_url . 'css/lineicons.css', 'ver' => '3.0.0'],
                ['handle' => 't9admin_pro_styles_css', 'src' => $assets_url . 'css/styles.css', 'ver' => $version],
                ['handle' => 't9admin_pro_select2_css', 'src' => $assets_url . '/libs/select2/dist/css/select2.min.css', 'ver' => $version],
                ['handle' => 't9admin_pro_dataTables_css', 'src' => $assets_url . 'libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css', 'ver' => $version],
            ],
            'js' => [
                // Thêm jQuery từ CDN
                ['handle' => 't9admin_pro_jquery_js', 'src' => 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', 'ver' => '3.6.0'],
                ['handle' => 't9admin_pro_bootstrap_js', 'src' => $assets_url . 'js/bootstrap.bundle.min.js', 'ver' => '5.3.0'],
                ['handle' => 't9admin_pro_simplebar_js', 'src' => $assets_url . 'libs/simplebar/dist/simplebar.min.js', 'ver' => $version],
                ['handle' => 't9admin_pro_dataTables_js', 'src' => $assets_url . 'libs/datatables.net/js/jquery.dataTables.min.js', 'ver' => $version],
                ['handle' => 't9admin_pro_default_theme_js', 'src' => $assets_url . 'js/main.js', 'ver' => $version],
                ['handle' => 't9admin_pro_tinymce_js', 'src' => $assets_url . 'js/tinymce/tinymce.min.js', 'ver' => '7.6.1'],
                ['handle' => 't9admin_pro_dropzone_js', 'src' => $assets_url . 'libs/dropzone/dist/min/dropzone.min.js', 'ver' => '7.6.1'],
                ['handle' => 't9admin_pro_app_init_js', 'src' => $assets_url . 'js/theme/app.init.js', 'ver' => $version],
                ['handle' => 't9admin_pro_app_js', 'src' => $assets_url . 'js/theme/app.min.js', 'ver' => $version],
                ['handle' => 't9admin_pro_theme_js', 'src' => $assets_url . 'js/theme/theme.js', 'ver' => $version],
                ['handle' => 't9admin_pro_lineicons_js', 'src' => $assets_url . 'js/lineicons.js', 'ver' => '3.0.0'],
                ['handle' => 't9admin_pro_apexcharts_js', 'src' => $assets_url . 'libs/apexcharts/dist/apexcharts.min.js', 'ver' => $version],
                ['handle' => 't9admin_pro_dashboard_js', 'src' => $assets_url . 'js/dashboards/dashboard1.js', 'ver' => $version],
                ['handle' => 't9admin_pro_select2_js', 'src' => $assets_url . 'libs/select2/dist/js/select2.min.js', 'ver' => $version],
                                ['handle' => 't9admin_pro_calendar_js', 'src' => $assets_url . 'libs/fullcalendar/index.global.min.js', 'ver' => '6.1.9'],
            ],
        ];

        add_action('wp_enqueue_scripts', [$this, 't9admin_pro_enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 't9admin_pro_enqueue_admin_assets']);
    }

    public function t9admin_pro_enqueue_admin_assets() {
        foreach ($this->assets['css'] as $css) {
            wp_enqueue_style($css['handle'], $css['src'], [], $css['ver']);
        }

        // Enqueue jQuery từ CDN trước
        wp_enqueue_script(
            't9admin_pro_jquery_js',
            'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js',
            [],
            '3.6.0',
            true
        );

        // Enqueue các script khác với dependency là jQuery
        foreach ($this->assets['js'] as $js) {
            if ($js['handle'] !== 't9admin_pro_jquery_js') { // Bỏ qua jQuery vì đã enqueue riêng
                wp_enqueue_script($js['handle'], $js['src'], ['t9admin_pro_jquery_js'], $js['ver'], true);
            }
        }

        wp_localize_script('t9admin_pro_default_theme_js', 't9adminProData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('t9admin_pro_action'),
        ]);
    }

    /**
     * Render only CSS in the <head> tag
     */
    public function t9admin_pro_render_css() {
        foreach ($this->assets['css'] as $css) {
            echo '<link rel="stylesheet" href="' . esc_url($css['src']) . '?ver=' . esc_attr($css['ver']) . '" type="text/css" media="all">' . PHP_EOL;
        }
    }

    /**
     * Render JS explicitly if needed (e.g., in footer)
     */
    public function t9admin_pro_render_js() {
        foreach ($this->assets['js'] as $js) {
            echo '<script src="' . esc_url($js['src']) . '?ver=' . esc_attr($js['ver']) . '" type="text/javascript" defer></script>' . PHP_EOL;
        }
    }
}