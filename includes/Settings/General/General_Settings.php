<?php
namespace T9Suite\Settings\General;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Controller xử lý menu "Settings" (General Settings) cho T9Suite.
 */
class General_Settings {

    private $tabs = [
        'general'    => 'General',
        'login'      => 'Login',
        'style'      => 'Style',
        'typography' => 'Typography',
        'menu'       => 'Menu',
    ];

    public function register_menu() {
        add_menu_page(
            __('T9Suite Settings', 't9suite'),
            '2. Settings',
            'manage_options',
            't9suite-settings',
            [$this, 'render_page'],
            '',
            62
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 't9suite'));
        }
    
        $active_tab = $_GET['settings_tab'] ?? 'general';
        if (!array_key_exists($active_tab, $this->tabs)) {
            $active_tab = 'general';
        }
    
        if (isset($_POST['the9_settings_nonce']) && wp_verify_nonce($_POST['the9_settings_nonce'], 't9_settings_action')) {
            update_option('t9suite_logo_dark', esc_url_raw($_POST['t9suite_logo_dark'] ?? ''));
            update_option('t9suite_logo_light', esc_url_raw($_POST['t9suite_logo_light'] ?? ''));
            add_settings_error('t9suite_settings', 'settings_updated', __('Settings saved successfully.', 't9suite'), 'success');
        }
    
        ?>
        <div class="wrap" id="t9-settings-wrapper">
            <h1 class="wp-heading-inline">⚙️ T9Suite Settings</h1>
            <div style="display: flex; gap: 30px; margin-top: 20px;">
                <!-- Sidebar -->
                <div style="width: 200px; border-right: 1px solid #ddd;">
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($this->tabs as $slug => $label): ?>
                            <li style="margin-bottom: 10px;">
                                <a href="<?php echo esc_url(add_query_arg(['page' => 't9suite-settings', 'settings_tab' => $slug])); ?>"
                                   class="t9-settings-menu <?php echo ($slug === $active_tab) ? 'active' : ''; ?>">
                                    <?php echo esc_html($label); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
    
                <!-- Content -->
                <div style="flex: 1;">
                    <?php $this->load_tab_file($active_tab); ?>
                </div>
            </div>
        </div>
    
        <style>
            .t9-settings-menu {
                display: block;
                padding: 10px 15px;
                background: #f9f9f9;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 500;
                color: #333;
            }
            .t9-settings-menu.active {
                background: #0073aa;
                color: white;
            }
        </style>
        <?php
    }
    

    private function load_tab_file($tab) {
        $file_path = plugin_dir_path(__DIR__) . "General/Tabs/{$tab}.php";
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            echo '<p>' . esc_html__('Tab content not found.', 't9suite') . '</p>';
        }
    }

    public function register_settings() {
        $settings = [
            't9suite_general_settings' => [
                't9suite_custom_route'     => 'sanitize_text_field',
                't9suite_company_name'     => 'sanitize_text_field',
                't9suite_logo_dark'        => 'esc_url_raw',
                't9suite_logo_light'       => 'esc_url_raw',
                't9suite_logo_width'       => 'absint',
                't9suite_contact_email'    => 'sanitize_email',
                't9suite_contact_phone'    => 'sanitize_text_field',
                't9suite_contact_fb'       => 'esc_url_raw',
                't9suite_dark_mode_toggle' => [$this, 'sanitize_toggle'],
            ],
            't9suite_typography_settings' => [
                't9suite_typography_enabled' => [$this, 'sanitize_toggle'],
                't9suite_title_font_family'  => 'sanitize_text_field',
                't9suite_title_font_size'    => [$this, 'sanitize_font_size'],
                't9suite_title_font_weight'  => [$this, 'sanitize_font_weight'],
                't9suite_body_font_family'   => 'sanitize_text_field',
                't9suite_body_font_size'     => [$this, 'sanitize_font_size'],
                't9suite_body_font_weight'   => [$this, 'sanitize_font_weight'],
                't9suite_button_font_family' => 'sanitize_text_field',
                't9suite_button_font_size'   => [$this, 'sanitize_font_size'],
                't9suite_button_font_weight' => [$this, 'sanitize_font_weight'],
            ],
            't9suite_menu_settings' => [
                't9suite_menu_items' => [$this, 'sanitize_menu_items'],
            ],
        ];

        foreach ($settings as $group => $options) {
            foreach ($options as $option => $callback) {
                register_setting($group, $option, [
                    'sanitize_callback' => $callback,
                ]);
            }
        }
    }

    public function enqueue_typography_styles() {
        if (get_option('t9suite_typography_enabled', 'no') !== 'yes') {
            return;
        }

        $title_font   = get_option('t9suite_title_font_family', 'Roboto');
        $body_font    = get_option('t9suite_body_font_family', 'Roboto');
        $button_font  = get_option('t9suite_button_font_family', 'Roboto');

        $google_fonts_url = add_query_arg([
            'family'  => urlencode("{$title_font}|{$body_font}|{$button_font}"),
            'display' => 'swap',
        ], 'https://fonts.googleapis.com/css');

        wp_enqueue_style('t9suite-google-fonts', $google_fonts_url, [], md5("{$title_font}|{$body_font}|{$button_font}"));

        $custom_css = sprintf(
            'h1,h2,h3,h4,h5,h6{font-family:%s;font-size:%spx;font-weight:%s}body{font-family:%s;font-size:%spx;font-weight:%s}button{font-family:%s;font-size:%spx;font-weight:%s}',
            esc_attr($title_font), esc_attr(get_option('t9suite_title_font_size', 16)), esc_attr(get_option('t9suite_title_font_weight', 400)),
            esc_attr($body_font), esc_attr(get_option('t9suite_body_font_size', 14)), esc_attr(get_option('t9suite_body_font_weight', 400)),
            esc_attr($button_font), esc_attr(get_option('t9suite_button_font_size', 14)), esc_attr(get_option('t9suite_button_font_weight', 600))
        );

        wp_add_inline_style('t9suite-google-fonts', $custom_css);
    }

    public function sanitize_toggle($value) {
        return $value === 'yes' ? 'yes' : 'no';
    }

    public function sanitize_font_size($size) {
        $size = absint($size);
        return ($size >= 10 && $size <= 100) ? $size : 14;
    }

    public function sanitize_font_weight($weight) {
        return in_array($weight, ['100','200','300','400','500','600','700','800','900'], true)
            ? $weight : '400';
    }

    public function sanitize_menu_items($menu_items) {
        if (!is_array($menu_items)) {
            return [];
        }

        $sanitized = [];
        foreach ($menu_items as $key => $item) {
            $sanitized[$key] = [
                'post_type' => sanitize_text_field($item['post_type'] ?? ''),
                'label'     => sanitize_text_field($item['label'] ?? ''),
                'icon'      => sanitize_text_field($item['icon'] ?? ''),
            ];
        }

        return $sanitized;
    }
}
