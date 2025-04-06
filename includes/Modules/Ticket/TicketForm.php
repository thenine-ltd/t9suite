<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

/**
 * Ticket Form class for T9Admin Pro.
 * Manages the creation and editing of tickets.
 */
class T9AdminProTicketForm {

    private $post_id;
    private $post;

    public function __construct($post_id = 0) {
        $this->post_id = absint($post_id);
        $this->post = $this->post_id ? get_post($this->post_id) : null;
    }

    /**
     * Hiển thị form bằng cách gọi template.
     */
    public function render_form() {
        // Chuẩn bị dữ liệu cho template
        $data = [
            'post_id'   => $this->post_id,
            'title'     => $this->post ? esc_attr($this->post->post_title) : '',
            'content'   => $this->post ? $this->post->post_content : '',
            'status'    => get_post_meta($this->post_id, '_t9_ticket_status', true),
            'assignee'  => get_post_meta($this->post_id, '_t9_ticket_assignee', true),
            'priority'  => get_post_meta($this->post_id, '_t9_ticket_priority', true),
            'customer'  => get_post_meta($this->post_id, '_t9_ticket_customer', true),
            'site'      => get_post_meta($this->post_id, '_t9_ticket_site', true),
            'staffs'    => get_posts(['post_type' => 't9_staff', 'numberposts' => -1, 'post_status' => 'publish']),
            'customers' => get_posts(['post_type' => 't9_customer', 'numberposts' => -1, 'post_status' => 'publish']),
            'sites'     => get_posts(['post_type' => 't9_site', 'numberposts' => -1, 'post_status' => 'publish']),
        ];

        // Gọi template
        $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/ticket/form.php';
        if (file_exists($template_path)) {
            extract($data);
            include $template_path;
        } else {
            echo '<p>' . esc_html__('Template not foundsa.', 't9admin-pro') . '</p>';
        }
    }

    /**
     * Xử lý form submission.
     */
    public function handle_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['t9_ticket_nonce']) || !wp_verify_nonce($_POST['t9_ticket_nonce'], 't9_ticket_save_action')) {
            wp_die(__('Nonce verification failed.', 't9admin-pro'));
        }

        $title     = sanitize_text_field($_POST['ticket_title']);
        $content   = sanitize_textarea_field($_POST['ticket_content']);
        $status    = sanitize_text_field($_POST['ticket_status']);
        $assignee  = absint($_POST['ticket_assignee']);
        $priority  = sanitize_text_field($_POST['ticket_priority']);
        $customer  = absint($_POST['ticket_customer']);
        $site      = absint($_POST['ticket_site']);
        $post_id   = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        $post_data = [
            'ID'           => $post_id,
            'post_type'    => 't9_ticket',
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
        ];

        if ($post_id) {
            wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if (!is_wp_error($post_id) && $post_id > 0) {
            update_post_meta($post_id, '_t9_ticket_status', $status);
            update_post_meta($post_id, '_t9_ticket_assignee', $assignee);
            update_post_meta($post_id, '_t9_ticket_priority', $priority);
            update_post_meta($post_id, '_t9_ticket_customer', $customer);
            update_post_meta($post_id, '_t9_ticket_site', $site);

            wp_redirect(add_query_arg([
                'post_type' => 't9_ticket',
                'post_id'   => $post_id,
                'message'   => 'success'
            ], home_url('/t9admin/post-type-create/')));
            exit;
        } else {
            wp_die(__('Failed to create/update ticket.', 't9admin-pro'));
        }
    }
}

// Hook xử lý form submission
add_action('admin_post_t9_ticket_save', [new T9AdminProTicketForm(), 'handle_form_submission']);