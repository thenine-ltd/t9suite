<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

/**
 * Customers Form class for T9Admin Pro CRM Module.
 * Manages customer data (no user association).
 */
class T9AdminProCustomersForm {

    private $post_id;
    private $post;

    public function __construct($post_id = 0) {
        $this->post_id = absint($post_id);
        $this->post = $this->post_id ? get_post($this->post_id) : null;
    }

    /**
     * Hiển thị form cho CPT customers
     */
    public function render_form() {
        $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/crm/form.php';
        if (file_exists($template_path)) {
            $data = [
                'post_id'         => $this->post_id,
                'customer_name'   => $this->post ? esc_attr($this->post->post_title) : '',
                'status'          => get_post_meta($this->post_id, 'status', true),
                'representative'  => get_post_meta($this->post_id, 'representative', true),
                'phone'           => get_post_meta($this->post_id, 'phone', true),
                'email'           => get_post_meta($this->post_id, 'email', true),
                'notes'           => get_post_meta($this->post_id, 'notes', true),
            ];
            extract($data);
            include $template_path;
        } else {
            echo '<div class="alert alert-danger">' . esc_html__('CRM customer form template not found.', 't9admin-pro') . '</div>';
        }
    }

    /**
     * Xử lý form submission
     */
    public function handle_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['t9_customers_nonce']) || !wp_verify_nonce($_POST['t9_customers_nonce'], 't9_customers_save_action')) {
            wp_die(__('Nonce verification failed.', 't9admin-pro'));
        }

        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to edit customers.', 't9admin-pro'));
        }

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $errors = [];

        $customer_name = sanitize_text_field($_POST['customer_name'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'active');
        $representative = sanitize_text_field($_POST['representative'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (empty($customer_name)) {
            $errors['customer_name'] = __('Customer Name is required.', 't9admin-pro');
        }

        if (!empty($errors)) {
            wp_die(__('Please fix the following errors: ', 't9admin-pro') . implode(', ', $errors));
        }

        $post_data = [
            'ID'          => $post_id,
            'post_type'   => 'customers',
            'post_title'  => $customer_name,
            'post_status' => 'publish',
            'post_author' => get_current_user_id(), // Gán user hiện tại làm author
        ];

        $result = $post_id ? wp_update_post($post_data, true) : wp_insert_post($post_data, true);
        if (!is_wp_error($result)) {
            $post_id = $post_id ?: $result;
        
            if (!empty($_FILES['logo']['name'])) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
        
                $attachment_id = media_handle_upload('logo', $post_id);
                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }
        
            update_post_meta($post_id, 'status', $status);
            update_post_meta($post_id, 'representative', $representative);
            update_post_meta($post_id, 'phone', $phone);
            update_post_meta($post_id, 'email', $email);
            update_post_meta($post_id, 'notes', $notes);
        
            wp_safe_redirect(add_query_arg([
                'post_type' => 'customers',
                'post_id'   => $post_id,
                'message'   => 'success'
            ], home_url('/t9admin/post-type-create/')));
            exit;
        } else {
            wp_die(__('Failed to create/update customer: ', 't9admin-pro') . ($result->get_error_message() ?? 'Unknown error'));
        }
    }
}

add_action('admin_post_t9_customers_save', [new T9AdminProCustomersForm(), 'handle_form_submission']);