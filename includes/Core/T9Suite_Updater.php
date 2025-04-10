<?php
namespace T9Suite\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class T9Suite_Updater handles plugin updates using WordPress Update API and WooCommerce REST API.
 */
class T9Suite_Updater {

    private static $consumer_key = 'ck_c793bb5a3263a02fb2bb850a2d41488b5989a75e';
    private static $consumer_secret = 'cs_9c20858934d618e57ff9beafa58654a2aff2badc';
    /**
     * Initialize the updater.
     */
    public function __construct() {
        // Hook into WordPress Update API.
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_plugin_update']);
        // Add admin notice for manual update (optional).
        add_action('admin_notices', [$this, 'display_update_notice']);
    }

    /**
     * Check for plugin updates using WordPress Update API.
     *
     * @param object $transient The update_plugins transient.
     * @return object Updated transient with plugin update info.
     */
    public function check_for_plugin_update($transient) {
        if (empty($transient) || !is_object($transient)) {
            error_log('T9Suite Updater: Transient is empty or not an object.');
            return $transient;
        }

        $latest_version_info = $this->get_latest_version_info();
        if (is_wp_error($latest_version_info)) {
            error_log('T9Suite Updater: Failed to get latest version info - ' . $latest_version_info->get_error_message());
            // Hiển thị thông báo lỗi trong admin
            add_action('admin_notices', function () use ($latest_version_info) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <?php
                        printf(
                            esc_html__('T9Suite Updater: Failed to check for updates - %s', 't9suite'),
                            esc_html($latest_version_info->get_error_message())
                        );
                        ?>
                    </p>
                </div>
                <?php
            });
            return $transient;
        }

        $current_version = T9SUITE_VERSION;
        $latest_version  = $latest_version_info['version'] ?? '0.0.0';

        // Log giá trị phiên bản để kiểm tra
        error_log("T9Suite Updater: Comparing versions - Current: $current_version, Latest: $latest_version");

        // Compare versions.
        if (version_compare($latest_version, $current_version, '>')) {
            $plugin_slug = plugin_basename(T9SUITE_PLUGIN_FILE);
            error_log('T9Suite Updater: Transient before update - ' . print_r($transient->response, true));
            $transient->response[$plugin_slug] = (object) [
                'slug'        => 't9suite',
                'new_version' => $latest_version,
                'url'         => 'https://t9suite.thenine.vn',
                'package'     => $latest_version_info['download_url'],
            ];
            // Store update info in a transient for manual update page (if needed).
            set_transient('t9suite_update_available', $latest_version_info, 12 * HOUR_IN_SECONDS);
            error_log("T9Suite Updater: Update available - Current: $current_version, Latest: $latest_version");
        } else {
            delete_transient('t9suite_update_available');
            error_log("T9Suite Updater: No update available - Current: $current_version, Latest: $latest_version");
        }

        return $transient;
    }

    /**
     * Get the latest version information from the server using WooCommerce REST API.
     *
     * @return array|WP_Error Array containing version and download URL, or WP_Error on failure.
     */
    private function get_latest_version_info() {
        $url = 'https://thenine.vn/wp-json/wc/v3/products/' . T9SUITE_PRODUCT_ID;
        $auth = base64_encode(self::$consumer_key . ':' . self::$consumer_secret);

        // Gọi API để lấy thông tin sản phẩm chính
        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type'  => 'application/json',
                'Cache-Control' => 'no-cache',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['id'])) {
            return new \WP_Error('invalid_response', 'Invalid product data from server.');
        }

        // Log toàn bộ dữ liệu để kiểm tra
        error_log('T9Suite Updater: Product API response - ' . print_r($data, true));

        // Extract version from meta_data (ACF field _version)
        $version = '';
        if (isset($data['meta_data']) && is_array($data['meta_data'])) {
            foreach ($data['meta_data'] as $meta) {
                if (isset($meta['key']) && $meta['key'] === '_version') {
                    $version = sanitize_text_field($meta['value']);
                }
            }
        }

        if (empty($version)) {
            return new \WP_Error('missing_version', 'Version (_version) not found in product meta data.');
        }

        // Kiểm tra định dạng phiên bản
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            return new \WP_Error('invalid_version', 'Invalid version format in _version field: ' . $version);
        }

        // Lấy danh sách biến thể (variations)
        if (empty($data['variations']) || !is_array($data['variations'])) {
            return new \WP_Error('no_variations', 'No variations found for this variable product.');
        }

        // Lấy thông tin biến thể đầu tiên (hoặc bạn có thể chọn biến thể cụ thể)
        $variation_id = $data['variations'][0]; // Lấy biến thể đầu tiên
        $variation_url = 'https://thenine.vn/wp-json/wc/v3/products/' . $variation_id;

        // Gọi API để lấy thông tin biến thể
        $variation_response = wp_remote_get($variation_url, [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type'  => 'application/json',
                'Cache-Control' => 'no-cache',
            ],
        ]);

        if (is_wp_error($variation_response)) {
            return $variation_response;
        }

        $variation_body = wp_remote_retrieve_body($variation_response);
        $variation_data = json_decode($variation_body, true);

        // Log dữ liệu biến thể để kiểm tra
        error_log('T9Suite Updater: Variation API response - ' . print_r($variation_data, true));

        // Lấy danh sách downloads từ biến thể
        if (empty($variation_data['downloads']) || !is_array($variation_data['downloads'])) {
            return new \WP_Error('no_downloads', 'No downloadable files found in variation.');
        }

        // Lấy URL từ file tải xuống đầu tiên
        $download_url = '';
        foreach ($variation_data['downloads'] as $download) {
            if (isset($download['file'])) {
                $download_url = esc_url_raw($download['file']);
                break; // Lấy file đầu tiên
            }
        }

        if (empty($download_url)) {
            return new \WP_Error('missing_download_url', 'Download URL not found in variation downloads.');
        }

        // Log giá trị version và download_url
        error_log("T9Suite Updater: Extracted version - $version, download_url - $download_url");

        return [
            'version'      => $version,
            'download_url' => $download_url,
        ];
    }

    /**
     * Display an admin notice if an update is available.
     */
    public function display_update_notice() {
        $update_info = get_transient('t9suite_update_available');
        if (!$update_info) {
            return;
        }

        $current_version = T9SUITE_VERSION;
        $latest_version  = $update_info['version'];

        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php
                printf(
                    esc_html__('A new version of T9Suite is available! Current version: %s, Latest version: %s. Please update from the Plugins page.', 't9suite'),
                    esc_html($current_version),
                    esc_html($latest_version)
                );
                ?>
                <a href="<?php echo esc_url(admin_url('update-core.php')); ?>">
                    <?php esc_html_e('Go to Updates', 't9suite'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}