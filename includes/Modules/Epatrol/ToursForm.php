<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

/**
 * Tours Form class for T9Admin Pro EPatrol Module.
 * Manages tour data creation and editing.
 */
class T9AdminProToursForm {

    private $post_id;
    private $post;

    public function __construct($post_id = 0) {
        $this->post_id = absint($post_id);
        $this->post = $this->post_id ? get_post($this->post_id) : null;
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_load_sites_by_customer', [$this, 'load_sites_by_customer']);
        add_action('wp_ajax_load_site_data', [$this, 'load_site_data']);
    }

    public function enqueue_scripts() {
        // Không cần Select2 nữa, chỉ cần jQuery
        wp_enqueue_script('jquery');
    }

    public function render_form() {
        $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/epatrol/tours/form.php';
        if (file_exists($template_path)) {
            $data = [
                'post_id'          => $this->post_id,
                'tour_name'        => $this->post ? esc_attr($this->post->post_title) : '',
                'tour_start_date'  => get_post_meta($this->post_id, 'tour_start_date', true),
                'tour_end_date'    => get_post_meta($this->post_id, 'tour_end_date', true),
                'customer'         => get_post_meta($this->post_id, 'customer', true),
                'selecting_site'   => get_post_meta($this->post_id, 'selecting_site', true),
                'selecting_guard'  => get_post_meta($this->post_id, 'selecting_guard', true),
                'start_time'       => get_post_meta($this->post_id, 'start_time', true),
                'end_time'         => get_post_meta($this->post_id, 'end_time', true),
                'interval'         => get_post_meta($this->post_id, 'interval', true),
                'repeat'           => get_post_meta($this->post_id, 'repeat', true),
                'customers'        => get_posts(['post_type' => 'customers', 'numberposts' => -1]),
                'sites'            => get_posts(['post_type' => 'sites', 'numberposts' => -1]),
                'staffs'           => get_users(['role' => 'staff']),
                'all_checkpoints'  => get_posts(['post_type' => 'checkpoints', 'numberposts' => -1]),
            ];
            extract($data);
            include $template_path;
        } else {
            echo '<div class="alert alert-danger">' . esc_html__('EPatrol tours form template not found.', 't9admin-pro') . '</div>';
        }
    }

    public function handle_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['t9_tours_nonce']) || !wp_verify_nonce($_POST['t9_tours_nonce'], 't9_tours_save_action')) {
            wp_die(__('Nonce verification failed.', 't9admin-pro'));
        }

        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to edit tours.', 't9admin-pro'));
        }

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $errors = [];

        $tour_name = sanitize_text_field($_POST['tour_name'] ?? '');
        $tour_start_date = sanitize_text_field($_POST['tour_start_date'] ?? '');
        $tour_end_date = sanitize_text_field($_POST['tour_end_date'] ?? '');
        $start_time = sanitize_text_field($_POST['start_time'] ?? '');
        $end_time = sanitize_text_field($_POST['end_time'] ?? '');
        $interval = absint($_POST['interval'] ?? 0);
        $repeat = sanitize_text_field($_POST['repeat'] ?? 'daily');
        $customer = absint($_POST['customer'] ?? 0);
        $selecting_site = absint($_POST['selecting_site'] ?? 0);
        $selecting_guard = absint($_POST['selecting_guard'] ?? 0);
        $tour_rounds = $_POST['tour_rounds'] ?? [];

        if (empty($tour_name)) {
            $errors['tour_name'] = __('Tour Name is required.', 't9admin-pro');
        }

        if (!empty($errors)) {
            wp_die(__('Please fix the following errors: ', 't9admin-pro') . implode(', ', $errors));
        }

        $post_data = [
            'ID'          => $post_id,
            'post_type'   => 'tours',
            'post_title'  => $tour_name,
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ];

        $result = $post_id ? wp_update_post($post_data, true) : wp_insert_post($post_data, true);
        if (!is_wp_error($result)) {
            $post_id = $post_id ?: $result;

            update_post_meta($post_id, 'tour_start_date', $tour_start_date);
            update_post_meta($post_id, 'tour_end_date', $tour_end_date);
            update_post_meta($post_id, 'start_time', $start_time);
            update_post_meta($post_id, 'end_time', $end_time);
            update_post_meta($post_id, 'interval', $interval);
            update_post_meta($post_id, 'repeat', $repeat);
            update_post_meta($post_id, 'customer', $customer);
            update_post_meta($post_id, 'selecting_site', $selecting_site);
            update_post_meta($post_id, 'selecting_guard', $selecting_guard);
            update_post_meta($post_id, 'tour_rounds', $tour_rounds);

            wp_safe_redirect(add_query_arg([
                'post_type' => 'tours',
                'post_id'   => $post_id,
                'message'   => 'success'
            ], home_url('/t9admin/post-type-create/')));
            exit;
        } else {
            wp_die(__('Failed to create/update tour: ', 't9admin-pro') . ($result->get_error_message() ?? 'Unknown error'));
        }
    }

    public function load_sites_by_customer() {
        // Kiểm tra nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 't9admin_pro_action')) {
            wp_send_json_error('Invalid nonce');
            wp_die();
        }

        $customer_id = isset($_POST['customer_id']) ? absint($_POST['customer_id']) : 0;
        if (!$customer_id) {
            wp_send_json_error('Invalid customer ID');
            wp_die();
        }

        $sites = get_posts([
            'post_type' => 'sites',
            'meta_key' => 'related_customer',
            'meta_value' => $customer_id,
            'numberposts' => -1,
        ]);

        $response = array_map(function($site) {
            return ['id' => $site->ID, 'title' => $site->post_title];
        }, $sites);

        wp_send_json_success($response);
        wp_die();
    }

    public function load_site_data() {
        // Kiểm tra nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 't9admin_pro_action')) {
            wp_send_json_error('Invalid nonce');
            wp_die();
        }

        $site_id = isset($_POST['site_id']) ? absint($_POST['site_id']) : 0;
        if (!$site_id) {
            wp_send_json_error('Invalid site ID');
            wp_die();
        }

        $patrol_staff = get_post_meta($site_id, 'related_patrol_staff', true) ?: [];
        $checkpoints = get_post_meta($site_id, 'related_checkpoints', true) ?: [];

        $response = [
            'staffs' => array_map(function($staff_id) {
                $staff = get_userdata($staff_id);
                return $staff ? ['id' => $staff->ID, 'name' => $staff->display_name] : null;
            }, array_filter($patrol_staff)),
            'checkpoints' => array_map(function($checkpoint_id) {
                $checkpoint = get_post($checkpoint_id);
                return $checkpoint ? ['id' => $checkpoint->ID, 'title' => $checkpoint->post_title] : null;
            }, array_filter($checkpoints)),
        ];

        wp_send_json_success($response);
        wp_die();
    }
}

add_action('admin_post_t9_tours_save', [new T9AdminProToursForm(), 'handle_form_submission']);