<?php
namespace T9Suite\Settings\Marketplace;

use T9Suite\License\T9Suite_License;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Controller cho Marketplace trong T9Suite Settings.
 */
class Marketplace_Settings {

    public function register_menu() {
        add_menu_page(
            'T9Suite Marketplace',
            '3. Marketplace',
            'manage_options',
            't9suite-marketplace',
            [$this, 'render_page'],
            '',
            63
        );
    }

    public function render_page() {
        include __DIR__ . '/Templates/marketplace-ui.php';
    }

    public function register_rest_routes() {
        register_rest_route('t9suite/v1', '/marketplace', [
            'methods'  => 'GET',
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
            return new \WP_Error('invalid_category', __('Invalid category', 't9suite'));
        }

        $ck = 'ck_c793bb5a3263a02fb2bb850a2d41488b5989a75e'; // 🔒 Replace with secure env or option
        $cs = 'cs_9c20858934d618e57ff9beafa58654a2aff2badc';

        $url = add_query_arg([
            'consumer_key'    => $ck,
            'consumer_secret' => $cs,
            'category'        => $category_id,
            'per_page'        => 20,
        ], 'https://thenine.vn/wp-json/wc/v3/products');

        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return new \WP_Error('api_error', __('Cannot fetch product from thenine.vn', 't9suite'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return is_array($data) ? rest_ensure_response($data) : new \WP_Error('invalid_response', __('Invalid response from thenine.vn', 't9suite'));
    }

    public function ajax_download_module() {
        check_ajax_referer('t9suite_nonce', 'nonce');

        $license_key = sanitize_text_field($_POST['license'] ?? '');
        $product_id  = absint($_POST['product_id'] ?? 0);

        if (!$license_key || !$product_id) {
            wp_send_json_error(['message' => 'Missing parameters.']);
        }

        $validated = $this->validate_license($license_key, $product_id);
        if (is_wp_error($validated)) {
            wp_send_json_error(['message' => $validated->get_error_message()]);
        }

        $download_url = $this->get_download_url($product_id);
        if (is_wp_error($download_url)) {
            wp_send_json_error(['message' => $download_url->get_error_message()]);
        }

        $result = $this->download_and_extract($download_url, $product_id);
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => '✅ Module downloaded & extracted successfully.']);
    }

    private function validate_license($license_key, $product_id) {
        // This can be replaced by actual license validation logic
        // Should query the API to validate the license and product match
        // Return true or WP_Error
        return true;
    }

    private function get_download_url($product_id) {
        // Giả định dùng WooCommerce API để lấy URL
        $ck = 'ck_67273c2b881d76a0cf3a7ae0339bbe758c3c1c23'; // 🔒
        $cs = 'cs_27dc86b4ec25d8946f50e71ed42ab00797f6b09a';

        $product_url = "https://thenine.vn/wp-json/wc/v3/products/{$product_id}?consumer_key={$ck}&consumer_secret={$cs}";
        $res = wp_remote_get($product_url);

        if (is_wp_error($res)) return $res;

        $product = json_decode(wp_remote_retrieve_body($res), true);
        if (!isset($product['downloads'][0]['file'])) {
            return new \WP_Error('no_download', 'No downloadable file found in product.');
        }

        return esc_url_raw($product['downloads'][0]['file']);
    }

    private function download_and_extract($download_url, $product_id) {
        $upload_dir = plugin_dir_path(__DIR__) . '../../Modules/';
        $tmp_file = $upload_dir . 'temp_download_' . $product_id . '.zip';

        if (!wp_mkdir_p($upload_dir)) {
            return new \WP_Error('dir_create_fail', 'Cannot create module directory.');
        }

        $res = wp_remote_get($download_url, [
            'timeout'  => 30,
            'stream'   => true,
            'filename' => $tmp_file
        ]);

        if (is_wp_error($res)) return $res;
        if (!file_exists($tmp_file)) return new \WP_Error('download_fail', 'Downloaded file not found.');

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
        global $wp_filesystem;

        $result = unzip_file($tmp_file, $upload_dir);
        unlink($tmp_file);

        if (is_wp_error($result)) {
            return $result;
        }

        return true;
    }
}
