<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

/**
 * Sites Form class for T9Admin Pro EPatrol Module.
 * Manages site data with logo upload.
 */
class T9AdminProSitesForm {

    private $post_id;
    private $post;

    public function __construct($post_id = 0) {
        $this->post_id = absint($post_id);
        $this->post = $this->post_id ? get_post($this->post_id) : null;
    }

    public function render_form() {
        $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/epatrol/form.php';
        if (file_exists($template_path)) {
            $data = [
                'post_id'             => $this->post_id,
                'site_name'           => $this->post ? esc_attr($this->post->post_title) : '',
                'status'              => get_post_meta($this->post_id, 'status', true),
                'representative'      => get_post_meta($this->post_id, 'representative', true),
                'phone'               => get_post_meta($this->post_id, 'phone', true),
                'email'               => get_post_meta($this->post_id, 'email', true),
                'address'             => get_post_meta($this->post_id, 'address', true),
                'notes'               => get_post_meta($this->post_id, 'notes', true),
                'city'                => get_post_meta($this->post_id, 'city', true),
                'district'            => get_post_meta($this->post_id, 'district', true),
                'related_leader'      => get_post_meta($this->post_id, 'related_leader', true),
                'related_patrol_staff' => get_post_meta($this->post_id, 'related_patrol_staff', true),
                'related_checkpoints' => get_post_meta($this->post_id, 'related_checkpoints', true) ?: [],
                'captains'            => get_users(['role' => 'captain']),
                'staffs'              => get_users(['role' => 'staff']),
                'customers'           => get_posts(['post_type' => 'customers', 'numberposts' => -1]),
                'checkpoints'         => get_posts(['post_type' => 'checkpoints', 'numberposts' => -1]),
            ];
            extract($data);
            include $template_path;
        } else {
            echo '<div class="alert alert-danger">' . esc_html__('EPatrol sites form template not found.', 't9admin-pro') . '</div>';
        }
    }

    public function handle_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['t9_sites_nonce']) || !wp_verify_nonce($_POST['t9_sites_nonce'], 't9_sites_save_action')) {
            wp_die(__('Nonce verification failed.', 't9admin-pro'));
        }

        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to edit sites.', 't9admin-pro'));
        }

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $errors = [];

        $site_name = sanitize_text_field($_POST['site_name'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'active');
        $representative = sanitize_text_field($_POST['representative'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $address = sanitize_textarea_field($_POST['address'] ?? '');
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        $city = sanitize_text_field($_POST['city'] ?? '');
        $district = sanitize_text_field($_POST['district'] ?? '');
        $related_leader = absint($_POST['related_leader'] ?? 0);
        $related_patrol_staff = array_map('absint', $_POST['related_patrol_staff'] ?? []);
        $related_checkpoints = array_map('absint', $_POST['related_checkpoints'] ?? []);
        $related_customer = absint($_POST['related_customer'] ?? 0);

        if (empty($site_name)) {
            $errors['site_name'] = __('Site Name is required.', 't9admin-pro');
        }

        if (!empty($errors)) {
            wp_die(__('Please fix the following errors: ', 't9admin-pro') . implode(', ', $errors));
        }

        $post_data = [
            'ID'          => $post_id,
            'post_type'   => 'sites',
            'post_title'  => $site_name,
            'post_status' => 'publish',
            'post_author' => get_current_user_id(), // Gán user hiện tại làm author
        ];

        $result = $post_id ? wp_update_post($post_data, true) : wp_insert_post($post_data, true);
        if (!is_wp_error($result)) {
            $post_id = $post_id ?: $result;
        
            update_post_meta($post_id, 'status', $status);
            update_post_meta($post_id, 'representative', $representative);
            update_post_meta($post_id, 'phone', $phone);
            update_post_meta($post_id, 'email', $email);
            update_post_meta($post_id, 'address', $address);
            update_post_meta($post_id, 'notes', $notes);
            update_post_meta($post_id, 'city', $city);
            update_post_meta($post_id, 'district', $district);
            update_post_meta($post_id, 'related_leader', $related_leader);
            update_post_meta($post_id, 'related_patrol_staff', $related_patrol_staff);
            update_post_meta($post_id, 'related_checkpoints', $related_checkpoints);
            update_post_meta($post_id, 'related_customer', $related_customer);            
        
            wp_safe_redirect(add_query_arg([
                'post_type' => 'sites',
                'post_id'   => $post_id,
                'message'   => 'success'
            ], home_url('/t9admin/post-type-create/')));
            exit;
        } else {
            wp_die(__('Failed to create/update site: ', 't9admin-pro') . ($result->get_error_message() ?? 'Unknown error'));
        }
    }
}

add_action('admin_post_t9_sites_save', [new T9AdminProSitesForm(), 'handle_form_submission']);