<?php
namespace T9Suite\Settings;

use T9Suite\License\T9Suite_License;
use T9Suite\Rewrite\T9SuiteRewrite;

// Prevent direct access to this file.
if (!defined('ABSPATH')) {
}

/**
 * Manages T9Admin Pro settings, including admin menu, assets, and typography.
 */
class T9Suite_Settings {

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
        add_action('wp_ajax_t9admin_download_module', [$this, 'ajax_download_module']);

    }

    public function get_installed_modules() {
        $modules_dir = plugin_dir_path(__DIR__) . 'Modules';
        $modules = [];

        if (!is_dir($modules_dir)) return $modules;

        foreach (scandir($modules_dir) as $module_folder) {
            if ($module_folder === '.' || $module_folder === '..') continue;

            $index_path = $modules_dir . '/' . $module_folder . '/index.php';
            if (file_exists($index_path)) {
                $headers = [
                    'Module Name' => 'Module Name',
                    'Module Slug' => 'Module Slug',
                    'Version'     => 'Version',
                    'Description' => 'Description',
                    'Author'      => 'Author',
                    'Author URI'  => 'Author URI',
                    'Demo URI'    => 'Demo URI',
                ];

                $data = get_file_data($index_path, $headers);
                $slug = $data['Module Slug'] ?? sanitize_title($module_folder);
                $data['dir'] = $modules_dir . '/' . $module_folder;

                if (!empty($slug)) {
                    $modules[$slug] = $data;
                }
            }
        }

        return $modules;
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
            'templates' => 73,
            'modules'   => 71,
            'addons'    => 72,
        ];
    
        $category_id = $category_map[$category_slug] ?? null;
    
        if (!$category_id) {
            return new \WP_Error('invalid_category', __('Invalid category', 't9admin-pro'));
        }
    
        // API từ thenine.vn
        $ck = 'ck_c793bb5a3263a02fb2bb850a2d41488b5989a75e';
        $cs = 'cs_9c20858934d618e57ff9beafa58654a2aff2badc';
    
        $url = add_query_arg([
            'consumer_key'    => $ck,
            'consumer_secret' => $cs,
            'category'        => $category_id,
            'per_page'        => 20
        ], 'https://thenine.vn/wp-json/wc/v3/products');
    
        $response = wp_remote_get($url);
    
        if (is_wp_error($response)) {
            return new \WP_Error('api_error', __('Cannot fetch product from thenine.vn', 't9admin-pro'));
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        if (!is_array($data)) {
            return new \WP_Error('invalid_response', __('Invalid response from thenine.vn', 't9admin-pro'));
        }
    
        return rest_ensure_response($data);
    }
        
    public function ajax_download_module() {
        // Security check
        check_ajax_referer('t9suite_nonce', 'nonce'); // Updated from t9admin_pro_nonce
    
        // Input validation
        $license_key = sanitize_text_field($_POST['license'] ?? '');
        $product_id  = absint($_POST['product_id'] ?? 0);
    
        if (empty($license_key) || !$product_id) {
            return wp_send_json_error(['message' => 'Missing required parameters.']);
        }
    
        // Log product_id
        error_log('Requested product_id: ' . $product_id);
    
        // Auth credentials for License Manager
        $lmfwc_ck = 'ck_67273c2b881d76a0cf3a7ae0339bbe758c3c1c23';
        $lmfwc_cs = 'cs_27dc86b4ec25d8946f50e71ed42ab00797f6b09a';
        $lmfwc_auth_header = 'Basic ' . base64_encode("{$lmfwc_ck}:{$lmfwc_cs}");
    
        // Auth credentials for WooCommerce (thay bằng key của bạn)
        $wc_ck = 'ck_c793bb5a3263a02fb2bb850a2d41488b5989a75e'; // Thay bằng key của WooCommerce
        $wc_cs = 'cs_9c20858934d618e57ff9beafa58654a2aff2badc'; // Thay bằng secret của WooCommerce
        $wc_auth_header = 'Basic ' . base64_encode("{$wc_ck}:{$wc_cs}");
    
        // Validate license key
        $validate_url = "https://thenine.vn/wp-json/lmfwc/v2/licenses/validate/{$license_key}";
        $response = wp_remote_get($validate_url, [
            'headers' => [
                'Authorization' => $lmfwc_auth_header,
                'Content-Type'  => 'application/json'
            ],
            'timeout' => 15
        ]);
    
        if (is_wp_error($response)) {
            error_log('License validation error: ' . $response->get_error_message());
            return wp_send_json_error(['message' => 'License validation failed (connection error).']);
        }
    
        $license_data = json_decode(wp_remote_retrieve_body($response), true);
        error_log('License validation response: ' . print_r($license_data, true));
    
        if (empty($license_data['success']) || !$license_data['success']) {
            return wp_send_json_error(['message' => 'Invalid or inactive license key.']);
        }
    
        // Try to get order_id
        $order_id = $license_data['order_id'] ?? $license_data['orderId'] ?? $license_data['data']['order_id'] ?? $license_data['data']['orderId'] ?? 0;
    
        if (!$order_id) {
            $license_url = "https://thenine.vn/wp-json/lmfwc/v2/licenses/{$license_key}";
            $license_response = wp_remote_get($license_url, [
                'headers' => [
                    'Authorization' => $lmfwc_auth_header,
                    'Content-Type'  => 'application/json'
                ],
                'timeout' => 15
            ]);
    
            if (is_wp_error($license_response)) {
                error_log('License fetch error: ' . $license_response->get_error_message());
                return wp_send_json_error(['message' => 'Cannot fetch license details.']);
            }
    
            $license_details = json_decode(wp_remote_retrieve_body($license_response), true);
            error_log('License details response: ' . print_r($license_details, true));
    
            $order_id = $license_details['data']['orderId'] ?? $license_details['order_id'] ?? $license_details['orderId'] ?? 0;
        }
    
        if (!$order_id) {
            return wp_send_json_error(['message' => 'Order ID not found for this license.']);
        }
    
        // Get order info using WooCommerce API key
        $order_url = "https://thenine.vn/wp-json/wc/v3/orders/{$order_id}?consumer_key={$wc_ck}&consumer_secret={$wc_cs}";
        $order_res = wp_remote_get($order_url, [
            'timeout' => 15,
            'headers' => [
                'Authorization' => $wc_auth_header
            ]
        ]);
    
        if (is_wp_error($order_res)) {
            error_log('Order fetch error: ' . $order_res->get_error_message());
            return wp_send_json_error(['message' => 'Cannot fetch order data: ' . $order_res->get_error_message()]);
        }
    
        $order = json_decode(wp_remote_retrieve_body($order_res), true);
        error_log('Order data: ' . print_r($order, true));
    
        $found = false;
        foreach ($order['line_items'] as $item) {
            $item_product_id = (int) ($item['product_id'] ?? 0);
            $item_variation_id = (int) ($item['variation_id'] ?? 0);
    
            error_log("Comparing: requested product_id=$product_id with item product_id=$item_product_id, variation_id=$item_variation_id");
    
            if ($item_product_id === $product_id || ($item_variation_id && $item_variation_id === $product_id)) {
                $found = true;
                break;
            }
        }
    
        if (!$found) {
            return wp_send_json_error(['message' => 'This license does not belong to this product.']);
        }
    
        // Fetch product details to get downloadable file
        $product_url = "https://thenine.vn/wp-json/wc/v3/products/{$product_id}?consumer_key={$wc_ck}&consumer_secret={$wc_cs}&context=edit";
        $product_res = wp_remote_get($product_url, [
            'timeout' => 15,
            'headers' => [
                'Authorization' => $wc_auth_header
            ]
        ]);
    
        if (is_wp_error($product_res)) {
            error_log('Product fetch error: ' . $product_res->get_error_message());
            return wp_send_json_error(['message' => 'Cannot fetch product data.']);
        }
    
        $product = json_decode(wp_remote_retrieve_body($product_res), true);
        error_log('Product data: ' . print_r($product, true));
    
        if (!isset($product['downloads']) || empty($product['downloads'])) {
            return wp_send_json_error(['message' => 'No downloadable file found in product.']);
        }
    
        $download_url = $product['downloads'][0]['file'] ?? '';
        if (empty($download_url)) {
            return wp_send_json_error(['message' => 'Download URL is empty.', 'downloads' => $product['downloads']]);
        }
    
        // Download file with authentication
        $upload_dir = plugin_dir_path(__DIR__) . 'Modules/';
        error_log('Upload dir: ' . $upload_dir);
    
        // Kiểm tra quyền thư mục
        if (!is_dir($upload_dir)) {
            if (!wp_mkdir_p($upload_dir)) {
                return wp_send_json_error(['message' => 'Failed to create directory: ' . $upload_dir]);
            }
        }
    
        if (!is_writable($upload_dir)) {
            return wp_send_json_error(['message' => 'Upload directory is not writable: ' . $upload_dir]);
        }
    
        $download_response = wp_remote_get($download_url, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => $wc_auth_header
            ],
            'stream' => true,
            'filename' => $upload_dir . 'temp_download.zip'
        ]);
    
        if (is_wp_error($download_response)) {
            error_log('Download error: ' . $download_response->get_error_message());
            $tmp_file = download_url($download_url, 30);
            if (is_wp_error($tmp_file)) {
                return wp_send_json_error(['message' => 'Failed to download file: ' . $tmp_file->get_error_message()]);
            }
        } else {
            $tmp_file = $upload_dir . 'temp_download.zip';
        }
    
        // Kiểm tra file tạm
        if (!file_exists($tmp_file)) {
            return wp_send_json_error(['message' => 'Temp file not found: ' . $tmp_file]);
        }
    
        // Sử dụng WP_Filesystem để giải nén
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
        global $wp_filesystem;
    
        if (!$wp_filesystem->is_writable($upload_dir)) {
            return wp_send_json_error(['message' => 'WP_Filesystem cannot write to directory: ' . $upload_dir]);
        }
    
        $result = unzip_file($tmp_file, $upload_dir);
        unlink($tmp_file);
    
        if (is_wp_error($result)) {
            error_log('Unzip error: ' . $result->get_error_message());
            return wp_send_json_error(['message' => 'Failed to unzip file: ' . $result->get_error_message()]);
        }
    
        return wp_send_json_success(['message' => '✅ Module downloaded & extracted successfully.']);
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
        if (strpos($hook, 't9suite') === false) { // Updated from 't9admin-pro'
            return;
        }
    
        if (isset($_GET['t9suite_nonce']) && !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['t9suite_nonce'])), 't9suite_action')) { // Updated nonce
            wp_die(esc_html__('Invalid request. Nonce verification failed.', 't9suite'));
        }
    
        wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css', [], '4.1.0');
        wp_enqueue_style('t9suite-style', T9SUITE_PLUGIN_URL . 'assets/css/t9suite.css', [], T9SUITE_VERSION); // Updated
    
        wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js', ['jquery'], '4.1.0', true);
        wp_enqueue_script('t9suite-script', T9SUITE_PLUGIN_URL . 'assets/js/t9suite.js', ['jquery', 'select2-js'], T9SUITE_VERSION, true); // Updated
    
        wp_localize_script('t9suite-script', 't9suiteData', [ // Updated from t9adminProData
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('t9suite_nonce'), // Updated nonce
        ]);
    
        wp_add_inline_style('t9suite-style', '#toplevel_page_t9suite-settings .wp-first-item { display: none; }'); // Updated
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
            <h1>Marketplace</h1>
            <div style="display: flex;">
                <div style="width: 190px; padding-right: 20px; border-right: 1px solid #ddd;">
                    <ul style="list-style: none; padding-left: 0;">
                        <li><a href="#" class="t9-tab active" data-tab="templates">Templates</a></li>
                        <li><a href="#" class="t9-tab" data-tab="modules">Modules</a></li>
                        <li><a href="#" class="t9-tab" data-tab="addons">Addons</a></li>
                    </ul>
                </div>
                <div style="flex-grow: 1; padding-left: 20px;">
                    <div id="t9-content-area"><p>Loading...</p></div>
                </div>
            </div>

            <div id="t9-license-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
                <div style="background:white; padding:30px; max-width:400px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.3);">
                    <h2>Enter License Key</h2>
                    <input type="text" id="t9-license-key" style="width:100%; padding:10px; margin-top:10px;">
                    <button id="t9-submit-license" class="button button-primary" style="margin-top:10px;">Confirm & Download</button>
                    <button id="t9-close-modal" class="button" style="margin-top:10px;">Cancel</button>
                    <div id="t9-download-status" style="margin-top:10px; font-style: italic; color: #555;"></div>
                </div>
            </div>

            <script>
            document.addEventListener("DOMContentLoaded", function () {
                function loadMarketplace(category) {
                    document.getElementById("t9-content-area").innerHTML = '<p>Loading ' + category + '...</p>';
                    fetch(`/wp-json/t9suite/v1/marketplace?category=${category}`)
                        .then(response => response.json())
                        .then(data => {
                            let html = '<div style="display:flex; flex-wrap:wrap; gap:20px;">';
                            data.forEach(item => {
                                const localVersion = localStorage.getItem("t9module_version_" + item.id);
                                const remoteVersionMeta = item.meta_data?.find(m => m.key === '_version');
                                const remoteVersion = remoteVersionMeta?.value || '1.0.0';

                                let actionBtn = `<button class="button button-primary t9-download" data-id="${item.id}" data-version="${remoteVersion}">Download</button>`;
                                if (localVersion) {
                                    if (remoteVersion !== localVersion) {
                                        actionBtn = `<button class="button t9-update" data-id="${item.id}" data-version="${remoteVersion}">Update</button>`;
                                    } else {
                                        actionBtn = `<button class="button" disabled>Installed</button>`;
                                    }
                                }

                                html += `
                                    <div style="width:300px; border:1px solid #ddd; border-radius:8px; padding:10px;">
                                        <img src="${item.images[0]?.src}" style="width:100%; border-radius:4px;">
                                        <h3>${item.name}</h3>
                                        <p>${item.short_description}</p>
                                        <a href="${item.permalink}" target="_blank" class="button">Demo</a>
                                        ${actionBtn}
                                    </div>
                                `;
                            });
                            html += '</div>';
                            document.getElementById("t9-content-area").innerHTML = html;

                            document.querySelectorAll(".t9-download").forEach(btn => {
                                btn.addEventListener("click", function () {
                                    const productId = btn.dataset.id;
                                    const version = btn.dataset.version;
                                    document.getElementById("t9-license-modal").style.display = "flex";
                                    document.getElementById("t9-license-key").value = '';
                                    document.getElementById("t9-download-status").innerHTML = '';
                                    document.getElementById("t9-submit-license").dataset.productId = productId;
                                    document.getElementById("t9-submit-license").dataset.version = version;
                                });
                            });

                            document.querySelectorAll(".t9-update").forEach(btn => {
                                btn.addEventListener("click", function () {
                                    const productId = btn.dataset.id;
                                    const version = btn.dataset.version;
                                    const license = prompt("Enter license to update");
                                    if (!license) return;
                                    btn.innerText = 'Updating...';
                                    btn.disabled = true;

                                    fetch(t9adminProData.ajaxUrl, {
                                        method: "POST",
                                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                        body: new URLSearchParams({
                                            action: "t9admin_download_module",
                                            nonce: t9adminProData.nonce,
                                            license: license,
                                            product_id: productId
                                        })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            btn.innerText = 'Updated';
                                            localStorage.setItem("t9module_version_" + productId, version);
                                        } else {
                                            alert("❌ " + (data.data?.message || "Update failed"));
                                            btn.innerText = 'Update';
                                            btn.disabled = false;
                                        }
                                    });
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
                    const key = document.getElementById("t9-license-key").value;
                    const productId = document.getElementById("t9-submit-license").dataset.productId;
                    const version = document.getElementById("t9-submit-license").dataset.version;
                    const statusDiv = document.getElementById("t9-download-status");
                    statusDiv.innerHTML = "🔄 Sending...";

                    fetch(t9adminProData.ajaxUrl, {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: new URLSearchParams({
                            action: "t9admin_download_module",
                            nonce: t9adminProData.nonce,
                            license: key,
                            product_id: productId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            statusDiv.innerHTML = "✅ Module activated & downloaded";
                            const btn = document.querySelector(`.t9-download[data-id="${productId}"]`);
                            if (btn) {
                                btn.textContent = "Installed";
                                btn.disabled = true;
                                btn.classList.remove("button-primary");
                            }
                            localStorage.setItem("t9module_version_" + productId, version);
                            setTimeout(() => {
                                document.getElementById("t9-license-modal").style.display = "none";
                            }, 1000);
                        } else {
                            statusDiv.innerHTML = "❌ " + (data.data?.message || "Wrong license key");
                        }
                    })
                    .catch(() => {
                        statusDiv.innerHTML = "❌ Network error";
                    });
                };

                loadMarketplace('templates');
            });
            </script>
        </div>
        <?php
    }
    

    public function render_license_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 't9admin-pro'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['t9admin_license_nonce']) && wp_verify_nonce($_POST['t9admin_license_nonce'], 't9admin_save_license')) {
            $license_key = sanitize_text_field($_POST['license_key']);
            T9Suite_License::save_license($license_key);

            if (T9Suite_License::is_license_valid()) {
                add_settings_error('t9admin_pro_license', 'license_success', __('License activated successfully!', 't9admin-pro'), 'success');
                wp_safe_redirect(admin_url('admin.php?page=t9admin-pro-settings&tab=pro&pro_tab=general'));
                exit;
            } else {
                add_settings_error('t9admin_pro_license', 'license_error', __('Invalid license key. Please try again.', 't9admin-pro'), 'error');
            }
        }

        $license_key = get_option('t9admin_pro_license_key', '');
        $status = T9Suite_License::is_license_valid() ? '<span style="color:green;font-weight:bold;">' . esc_html__('Activated', 't9admin-pro') . '</span>' : '<span style="color:red;font-weight:bold;">' . esc_html__('Not Activated', 't9admin-pro') . '</span>';
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

        if (!T9Suite_License::is_license_valid()) {
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
        $file_path = T9SUITE_PLUGIN_DIR . "includes/Settings/Tabs/{$tab}.php";
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