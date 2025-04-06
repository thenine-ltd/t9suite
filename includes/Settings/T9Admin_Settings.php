<?php
namespace T9AdminPro\Settings;

use T9AdminPro\License\T9Admin_License;
use T9AdminPro\Rewrite\T9AdminProRewrite;

// Prevent direct access to this file.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages T9Admin Pro settings, including admin menu, assets, and typography.
 */
class T9Admin_Settings {

    private $tabs = [
        'general'    => 'General',
        'login'      => 'Login',
        'style'      => 'Style',
        'typography' => 'Typography',
        'menu'       => 'Menu',
    ];

    public function __construct() {
        add_action('admin_menu', [$this, 'handle_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_typography_styles']);
        add_action('update_option_t9admin_pro_custom_route', [$this, 'flush_rewrite_rules_on_save'], 10, 2);
        
        // REST API cho marketplace
        add_action('rest_api_init', [$this, 'register_marketplace_rest_api']);
    }

    public function register_marketplace_rest_api() {
        register_rest_route('t9suite/v1', '/marketplace', [
            'methods' => 'GET',
            'callback' => [$this, 'fetch_marketplace_products'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function fetch_marketplace_products($request) {
        $category_slug = sanitize_text_field($request->get_param('category'));
        $category_map = [
            'templates' => 73, // thay bằng ID thật
            'modules'   => 71,
            'addons'    => 72,
        ];
        $category_id = $category_map[$category_slug] ?? null;

        if (!$category_id) {
            return new \WP_Error('invalid_category', __('Invalid category', 't9admin-pro'));
        }

        $ck = 'ck_c793bb5a3263a02fb2bb850a2d41488b5989a75e';
        $cs = 'cs_9c20858934d618e57ff9beafa58654a2aff2badc';
        $url = add_query_arg([
            'category' => $category_id,
            'per_page' => 20
        ], get_site_url() . '/wp-json/wc/v3/products');

        // Debugging: Log the request URL
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Request URL: ' . $url);
        }

        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("$ck:$cs"),
            ],
        ]);

        if (is_wp_error($response)) {
            return new \WP_Error('api_error', __('Error fetching products', 't9admin-pro'));
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function flush_rewrite_rules_on_save($old_value, $new_value) {
        if (class_exists(T9AdminProRewrite::class)) {
            $rewrite = new T9AdminProRewrite();
            $rewrite->flush_rewrite_rules();
        }
    }

    public static function get_custom_route() {
        return sanitize_title(get_option('t9admin_pro_custom_route', 't9admin'));
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_t9admin-pro-settings') {
            return;
        }

        if (isset($_GET['t9admin_pro_nonce']) && !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['t9admin_pro_nonce'])), 't9admin_pro_action')) {
            wp_die(esc_html__('Invalid request. Nonce verification failed.', 't9admin-pro'));
        }

        wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css', [], '4.1.0');
        // Sửa lỗi: Dùng T9ADMIN_PRO_PLUGIN_URL thay vì \T9AdminPro\PLUGIN_URL
        wp_enqueue_style('t9admin-pro-style', T9ADMIN_PRO_PLUGIN_URL . 'assets/css/t9admin-pro.css', [], T9ADMIN_PRO_VERSION);

        wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js', ['jquery'], '4.1.0', true);
        // Sửa lỗi: Dùng T9ADMIN_PRO_PLUGIN_URL thay vì \T9AdminPro\PLUGIN_URL
        wp_enqueue_script('t9admin-pro-script', T9ADMIN_PRO_PLUGIN_URL . 'assets/js/t9admin-pro.js', ['jquery', 'select2-js'], T9ADMIN_PRO_VERSION, true);

        wp_localize_script('t9admin-pro-script', 't9adminProData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('t9admin_pro_nonce'),
        ]);

        wp_add_inline_style('t9admin-pro-style', '#toplevel_page_t9admin-pro-settings .wp-first-item { display: none; }');
    }

    public function enqueue_typography_styles() {
        if (get_option('t9admin_pro_typography_enabled', 'no') !== 'yes') {
            return;
        }

        $title_font   = get_option('t9admin_pro_title_font_family', 'Roboto');
        $body_font    = get_option('t9admin_pro_body_font_family', 'Roboto');
        $button_font  = get_option('t9admin_pro_button_font_family', 'Roboto');

        $google_fonts_url = add_query_arg([
            'family'  => urlencode("$title_font|$body_font|$button_font"),
            'display' => 'swap',
        ], 'https://fonts.googleapis.com/css');

        wp_enqueue_style('t9admin-pro-google-fonts', $google_fonts_url, [], md5("$title_font|$body_font|$button_font"));

        $custom_styles = sprintf(
            'h1, h2, h3, h4, h5, h6 { font-family: %s; font-size: %spx; font-weight: %s; } body { font-family: %s; font-size: %spx; font-weight: %s; } button { font-family: %s; font-size: %spx; font-weight: %s; }',
            esc_attr($title_font), esc_attr(get_option('t9admin_pro_title_font_size', 16)), esc_attr(get_option('t9admin_pro_title_font_weight', 400)),
            esc_attr($body_font), esc_attr(get_option('t9admin_pro_body_font_size', 14)), esc_attr(get_option('t9admin_pro_body_font_weight', 400)),
            esc_attr($button_font), esc_attr(get_option('t9admin_pro_button_font_size', 14)), esc_attr(get_option('t9admin_pro_button_font_weight', 600))
        );
        wp_add_inline_style('t9admin-pro-google-fonts', $custom_styles);
    }

    public function handle_admin_menu() {
        add_menu_page(
            __('T9Admin Pro', 't9admin-pro'),
            __('T9Admin Pro', 't9admin-pro'),
            'manage_options',
            't9admin-pro-settings',
            [$this, 'render_settings'],
            'dashicons-admin-generic',
            51
        );

        // ✅ Thêm submenu Marketplace
        add_submenu_page(
            't9admin-pro-settings',
            __('Marketplace', 't9admin-pro'),
            __('Marketplace', 't9admin-pro'),
            'manage_options',
            't9admin-pro-marketplace',
            [$this, 'render_marketplace_page']
        );

        add_submenu_page(
            't9admin-pro-settings',
            __('License', 't9admin-pro'),
            __('License', 't9admin-pro'),
            'manage_options',
            't9admin-pro-license',
            [$this, 'render_license_page']
        );
    }

    public function render_marketplace_page() {
        ?>
        <div class="wrap" id="t9-marketplace">
            <h1><?php esc_html_e('Marketplace', 't9admin-pro'); ?></h1>
            <div style="display: flex;">
                <!-- Sidebar -->
                <div style="width: 220px; padding-right: 20px; border-right: 1px solid #ddd;">
                    <ul style="list-style: none; padding-left: 0;">
                        <li><a href="#" class="t9-tab active" data-tab="templates">Templates</a></li>
                        <li><a href="#" class="t9-tab" data-tab="modules">Modules</a></li>
                        <li><a href="#" class="t9-tab" data-tab="addons">Addons</a></li>
                    </ul>
                </div>
    
                <!-- Main Content -->
                <div style="flex-grow: 1; padding-left: 20px;">
                    <div id="t9-content-area"><p>Loading...</p></div>
                </div>
            </div>
    
            <!-- Modal nhập license -->
            <div id="t9-license-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
                <div style="background:white; padding:30px; max-width:400px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.3);">
                    <h2>Enter License Key</h2>
                    <input type="text" id="t9-license-key" style="width:100%; padding:10px; margin-top:10px;">
                    <button id="t9-submit-license" class="button button-primary" style="margin-top:10px;">Confirm & Download</button>
                    <button id="t9-close-modal" class="button" style="margin-top:10px;">Cancel</button>
                </div>
            </div>
    
            <script>
            document.addEventListener("DOMContentLoaded", function () {
                function loadMarketplace(category) {
                    document.getElementById("t9-content-area").innerHTML = '<p>Loading ' + category + '...</p>';
                    fetch(`/wp-json/t9admin/v1/marketplace?category=${category}`)
                        .then(response => response.json())
                        .then(data => {
                            let html = '<div style="display:flex; flex-wrap:wrap; gap:20px;">';
                            data.forEach(item => {
                                html += `
                                    <div style="width:300px; border:1px solid #ddd; border-radius:8px; padding:10px;">
                                        <img src="${item.images[0]?.src}" style="width:100%; border-radius:4px;">
                                        <h3>${item.name}</h3>
                                        <p>${item.short_description}</p>
                                        <a href="${item.external_url || '#'}" target="_blank" class="button">Demo</a>
                                        <button class="button button-primary t9-download" data-id="${item.id}">Download</button>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            document.getElementById("t9-content-area").innerHTML = html;
    
                            document.querySelectorAll(".t9-download").forEach(btn => {
                                btn.addEventListener("click", function () {
                                    document.getElementById("t9-license-modal").style.display = "flex";
                                });
                            });
                        });
                }
    
                document.querySelectorAll(".t9-tab").forEach(tab => {
                    tab.addEventListener("click", function (e) {
                        e.preventDefault();
                        document.querySelectorAll(".t9-tab").forEach(t => t.classList.remove("active"));
                        tab.classList.add("active");
                        loadMarketplace(tab.dataset.tab);
                    });
                });
    
                document.getElementById("t9-close-modal").onclick = () => {
                    document.getElementById("t9-license-modal").style.display = "none";
                };
    
                document.getElementById("t9-submit-license").onclick = () => {
                    let key = document.getElementById("t9-license-key").value;
                    alert("Sending license: " + key); // TODO: replace with real validation
                    document.getElementById("t9-license-modal").style.display = "none";
                };
    
                loadMarketplace('templates');
            });
            </script>
    
            <style>
                .t9-tab { display:block; padding:10px; color:#333; text-decoration:none; }
                .t9-tab.active { background: #007cba; color: white; border-radius: 4px; }
            </style>
        </div>
        <?php
    }
    

    public function render_license_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 't9admin-pro'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['t9admin_license_nonce']) && wp_verify_nonce($_POST['t9admin_license_nonce'], 't9admin_save_license')) {
            $license_key = sanitize_text_field($_POST['license_key']);
            T9Admin_License::save_license($license_key);

            if (T9Admin_License::is_license_valid()) {
                add_settings_error('t9admin_pro_license', 'license_success', __('License activated successfully!', 't9admin-pro'), 'success');
                wp_safe_redirect(admin_url('admin.php?page=t9admin-pro-settings&tab=pro&pro_tab=general'));
                exit;
            } else {
                add_settings_error('t9admin_pro_license', 'license_error', __('Invalid license key. Please try again.', 't9admin-pro'), 'error');
            }
        }

        $license_key = get_option('t9admin_pro_license_key', '');
        $status = T9Admin_License::is_license_valid() ? '<span style="color:green;font-weight:bold;">' . esc_html__('Activated', 't9admin-pro') . '</span>' : '<span style="color:red;font-weight:bold;">' . esc_html__('Not Activated', 't9admin-pro') . '</span>';
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('License Activation', 't9admin-pro'); ?></h2>
            <p><?php echo sprintf(__('Current Status: %s', 't9admin-pro'), $status); ?></p>
            <?php settings_errors('t9admin_pro_license'); ?>
            <form method="post" action="">
                <?php wp_nonce_field('t9admin_save_license', 't9admin_license_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('License Key:', 't9admin-pro'); ?></th>
                        <td><input type="text" name="license_key" value="<?php echo esc_attr($license_key); ?>" class="regular-text"></td>
                    </tr>
                </table>
                <?php submit_button(__('Save License', 't9admin-pro')); ?>
            </form>
        </div>
        <?php
    }

    public function render_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 't9admin-pro'));
        }

        if (!T9Admin_License::is_license_valid()) {
            wp_safe_redirect(admin_url('admin.php?page=t9admin-pro-license'));
            exit;
        }

        if (isset($_POST['the9_settings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['the9_settings_nonce'])), 't9_settings_action')) {
            update_option('t9admin_pro_logo_dark', esc_url_raw(wp_unslash($_POST['t9admin_pro_logo_dark'] ?? '')));
            update_option('t9admin_pro_logo_light', esc_url_raw(wp_unslash($_POST['t9admin_pro_logo_light'] ?? '')));
            add_settings_error('t9admin_pro_settings', 'settings_updated', __('Settings saved successfully.', 't9admin-pro'), 'success');
        }

        $active_tab = isset($_GET['pro_tab']) ? sanitize_text_field(wp_unslash($_GET['pro_tab'])) : 'general';
        if (!array_key_exists($active_tab, $this->tabs)) {
            $active_tab = 'general';
        }
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('T9Admin Pro Settings', 't9admin-pro'); ?></h2>
            <?php settings_errors('t9admin_pro_settings'); ?>
            <h2 class="nav-tab-wrapper">
                <?php foreach ($this->tabs as $tab => $label) : ?>
                    <a href="<?php echo esc_url(add_query_arg(['page' => 't9admin-pro-settings', 'tab' => 'pro', 'pro_tab' => $tab])); ?>" class="nav-tab <?php echo $active_tab === $tab ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html__($label, 't9admin-pro'); ?>
                    </a>
                <?php endforeach; ?>
            </h2>
            <div class="t9admin-pro-tab-content">
                <?php $this->load_tab_file($active_tab); ?>
            </div>
        </div>
        <?php
    }

    private function load_tab_file($tab) {
        $file_path = T9ADMIN_PRO_PLUGIN_DIR . "includes/Settings/Tabs/{$tab}.php";
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            echo '<p>' . esc_html__('Tab content not found.', 't9admin-pro') . '</p>';
        }
    }

    public function register_settings() {
        $settings = [
            't9admin_pro_general_settings' => [
                't9admin_pro_custom_route'     => 'sanitize_text_field',
                't9admin_pro_company_name'     => 'sanitize_text_field',
                't9admin_pro_logo_dark'        => 'esc_url_raw',
                't9admin_pro_logo_light'       => 'esc_url_raw',
                't9admin_pro_logo_width'       => 'absint',
                't9admin_pro_contact_email'    => 'sanitize_email',
                't9admin_pro_contact_phone'    => 'sanitize_text_field',
                't9admin_pro_contact_fb'       => 'esc_url_raw',
                't9admin_pro_dark_mode_toggle' => [$this, 't9admin_pro_sanitize_toggle_option'],
            ],
            't9admin_pro_typography_settings' => [
                't9admin_pro_typography_enabled' => [$this, 't9admin_pro_sanitize_toggle_option'],
                't9admin_pro_title_font_family'  => 'sanitize_text_field',
                't9admin_pro_title_font_size'    => [$this, 'sanitize_font_size'],
                't9admin_pro_title_font_weight'  => [$this, 'sanitize_font_weight'],
                't9admin_pro_body_font_family'   => 'sanitize_text_field',
                't9admin_pro_body_font_size'     => [$this, 'sanitize_font_size'],
                't9admin_pro_body_font_weight'   => [$this, 'sanitize_font_weight'],
                't9admin_pro_button_font_family' => 'sanitize_text_field',
                't9admin_pro_button_font_size'   => [$this, 'sanitize_font_size'],
                't9admin_pro_button_font_weight' => [$this, 'sanitize_font_weight'],
            ],
            't9admin_pro_menu_settings' => [
                't9admin_pro_menu_items' => [$this, 'sanitize_menu_items'],
            ],
        ];

        foreach ($settings as $group => $options) {
            foreach ($options as $option => $sanitize_callback) {
                register_setting($group, $option, [
                    'type'              => is_array($sanitize_callback) ? 'string' : (in_array($option, ['t9admin_pro_logo_width']) ? 'integer' : 'string'),
                    'sanitize_callback' => $sanitize_callback,
                ]);
            }
        }
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

    public function sanitize_font_size($size) {
        $size = absint($size);
        return ($size < 10 || $size > 100) ? 16 : $size;
    }

    public function sanitize_font_weight($weight) {
        $valid_weights = ['100', '200', '300', '400', '500', '600', '700', '800', '900'];
        return in_array($weight, $valid_weights, true) ? $weight : '400';
    }

    public function t9admin_pro_sanitize_toggle_option($value) {
        return ($value === 'yes') ? 'yes' : 'no';
    }

    private function get_bootstrap_icons() {
        return [
            'bi-alarm', 'bi-bag', 'bi-basket', 'bi-calendar', 'bi-camera', 'bi-chat', 'bi-check-circle',
            'bi-clipboard', 'bi-cloud', 'bi-code', 'bi-cup', 'bi-envelope', 'bi-gear', 'bi-heart',
            'bi-house', 'bi-image', 'bi-info-circle', 'bi-lightning', 'bi-list', 'bi-lock',
            'bi-music-note', 'bi-pencil', 'bi-person', 'bi-phone', 'bi-search', 'bi-star',
            'bi-trash', 'bi-upload', 'bi-wallet',
        ];
    }
}