<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

class T9Suite_Account_Settings {

    public function __construct() {
        // Hook xử lý AJAX cho việc upload avatar
        add_action('wp_ajax_t9suite_upload_avatar', [$this, 'upload_avatar']);
    }

    /**
     * Xử lý upload avatar và lưu vào Media Library.
     */
    public function upload_avatar() {
        // Kiểm tra nonce
        check_ajax_referer('t9suite_action', 'security');

        // Kiểm tra file hợp lệ
        if (empty($_FILES['avatar']['name'])) {
            wp_send_json_error(['message' => __('No file uploaded.', 't9suite')]);
        }

        // Giới hạn kích thước 800KB
        $max_size = 800 * 1024;
        if ($_FILES['avatar']['size'] > $max_size) {
            wp_send_json_error(['message' => __('File size exceeds 800KB.', 't9suite')]);
        }

        // Chỉ cho phép định dạng JPEG, PNG, GIF
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['avatar']['type'], $allowed_mime_types)) {
            wp_send_json_error(['message' => __('Invalid file type. Allowed types: JPG, PNG, GIF.', 't9suite')]);
        }

        // Xử lý upload
        $uploaded_file = $_FILES['avatar'];
        $upload = wp_handle_upload($uploaded_file, ['test_form' => false]);

        if (isset($upload['error'])) {
            wp_send_json_error(['message' => $upload['error']]);
        }

        // Tạo attachment trong Media Library
        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name($uploaded_file['name']),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment($attachment, $upload['file']);
        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_generate_attachment_metadata($attach_id, $upload['file']);

        $avatar_url = wp_get_attachment_url($attach_id);
        wp_send_json_success(['avatar_url' => $avatar_url]);
    }
}

// Khởi tạo class
new T9Suite_Account_Settings();
