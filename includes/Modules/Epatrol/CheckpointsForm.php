<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

/**
 * Checkpoints Form class for T9Admin Pro EPatrol Module.
 * Manages checkpoint data creation and editing.
 */
class T9AdminProCheckpointsForm {

    private $post_id;
    private $post;

    public function __construct($post_id = 0) {
        $this->post_id = absint($post_id);
        $this->post = $this->post_id ? get_post($this->post_id) : null;
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Enqueue scripts and styles if needed
     */
    public function enqueue_scripts() {
        // Có thể thêm enqueue nếu cần (ví dụ: Select2 hoặc custom JS/CSS)
    }

    /**
     * Hiển thị form cho CPT checkpoints
     */
    public function render_form() {
        $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/epatrol/checkpoints/form.php';
        if (file_exists($template_path)) {
            $data = [
                'post_id'         => $this->post_id,
                'site_name'       => $this->post ? esc_attr($this->post->post_title) : '',
                'status'          => get_post_meta($this->post_id, 'status', true),
                'label'           => get_post_meta($this->post_id, 'label', true),
                'use_coordinates' => get_post_meta($this->post_id, 'use_coordinates', true),
                'longitude'       => get_post_meta($this->post_id, 'longitude', true),
                'latitude'        => get_post_meta($this->post_id, 'latitude', true),
                'security_notes'  => get_post_meta($this->post_id, 'security_notes', true),
                'address'         => get_post_meta($this->post_id, 'address', true), 
            ];
            extract($data);
            include $template_path;
        } else {
            echo '<div class="alert alert-danger">' . esc_html__('EPatrol checkpoints form template not found.', 't9admin-pro') . '</div>';
        }
    }

    /**
     * Xử lý form submission
     */
    public function handle_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['t9_checkpoints_nonce']) || !wp_verify_nonce($_POST['t9_checkpoints_nonce'], 't9_checkpoints_save_action')) {
            wp_die(__('Nonce verification failed.', 't9admin-pro'));
        }

        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to edit checkpoints.', 't9admin-pro'));
        }

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $errors = [];

        $site_name = sanitize_text_field($_POST['site_name'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'active');
        $label = sanitize_text_field($_POST['label'] ?? '');
        $use_coordinates = isset($_POST['use_coordinates']) ? '1' : '0';
        $longitude = sanitize_text_field($_POST['longitude'] ?? '');
        $latitude = sanitize_text_field($_POST['latitude'] ?? '');
        $security_notes = sanitize_textarea_field($_POST['security_notes'] ?? '');
        $address = sanitize_text_field($_POST['address'] ?? ''); 

        if (empty($site_name)) {
            $errors['site_name'] = __('Site Name is required.', 't9admin-pro');
        }

        if (!empty($errors)) {
            wp_die(__('Please fix the following errors: ', 't9admin-pro') . implode(', ', $errors));
        }

        $post_data = [
            'ID'          => $post_id,
            'post_type'   => 'checkpoints',
            'post_title'  => $site_name,
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ];

        $result = $post_id ? wp_update_post($post_data, true) : wp_insert_post($post_data, true);
        if (!is_wp_error($result)) {
            $post_id = $post_id ?: $result;

            update_post_meta($post_id, 'status', $status);
            update_post_meta($post_id, 'label', $label);
            update_post_meta($post_id, 'use_coordinates', $use_coordinates);
            update_post_meta($post_id, 'longitude', $longitude);
            update_post_meta($post_id, 'latitude', $latitude);
            update_post_meta($post_id, 'security_notes', $security_notes);
            update_post_meta($post_id, 'address', $address); 

            wp_safe_redirect(add_query_arg([
                'post_type' => 'checkpoints',
                'post_id'   => $post_id,
                'message'   => 'success'
            ], home_url('/t9admin/post-type-create/')));
            exit;
        } else {
            wp_die(__('Failed to create/update checkpoint: ', 't9admin-pro') . ($result->get_error_message() ?? 'Unknown error'));
        }
    }
}

add_action('admin_post_t9_checkpoints_save', [new T9AdminProCheckpointsForm(), 'handle_form_submission']);