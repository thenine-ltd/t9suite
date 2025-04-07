<?php

namespace T9AdminPro\Forms;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class T9Admin_CPT_Form {

    /**
     * Xử lý form Create, Edit, Delete cho CPT.
     *
     * @param string $cpt Tên Custom Post Type (product, order, v.v.)
     * @param string $operation Hành động (create, edit, delete)
     */
    public static function handleCPTForm($cpt, $operation) {
        switch ($operation) {
            case 'create':
                self::createCPT($cpt);
                break;
            case 'edit':
                self::editCPT($cpt);
                break;
            case 'delete':
                self::deleteCPT($cpt);
                break;
            default:
                wp_die(esc_html__('Invalid operation for CPT.', 't9admin-pro'));
        }
    }

    /**
     * Tạo mới CPT.
     *
     * @param string $cpt Tên Custom Post Type.
     */
    private static function createCPT($cpt) {
        $post_data = [
            'post_title'    => sanitize_text_field($_POST['post_title'] ?? ''),
            'post_content'  => sanitize_textarea_field($_POST['post_content'] ?? ''),
            'post_status'   => 'publish',
            'post_type'     => $cpt,
        ];

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            wp_die(esc_html__('Failed to create post.', 't9admin-pro'));
        }

        // Thêm meta fields nếu có
        self::saveMetaFields($post_id);
        wp_redirect(home_url("/{$cpt}-list"));
        exit;
    }

    /**
     * Chỉnh sửa CPT.
     *
     * @param string $cpt Tên Custom Post Type.
     */
    private static function editCPT($cpt) {
        $post_id = absint($_POST['post_id']);
        $post_data = [
            'ID'            => $post_id,
            'post_title'    => sanitize_text_field($_POST['post_title'] ?? ''),
            'post_content'  => sanitize_textarea_field($_POST['post_content'] ?? ''),
        ];

        $updated = wp_update_post($post_data);

        if (is_wp_error($updated)) {
            wp_die(esc_html__('Failed to update post.', 't9admin-pro'));
        }

        // Lưu meta fields nếu có
        self::saveMetaFields($post_id);
        wp_redirect(home_url("/{$cpt}-list"));
        exit;
    }

    /**
     * Xóa CPT.
     *
     * @param string $cpt Tên Custom Post Type.
     */
    private static function deleteCPT($cpt) {
        $post_id = absint($_POST['post_id']);
        if (wp_delete_post($post_id, true)) {
            wp_redirect(home_url("/{$cpt}-list?deleted=1"));
        } else {
            wp_die(esc_html__('Failed to delete post.', 't9admin-pro'));
        }
    }

    /**
     * Lưu meta fields cho post.
     *
     * @param int $post_id ID của post cần lưu meta.
     */
    private static function saveMetaFields($post_id) {
        if (!empty($_POST['meta_fields'])) {
            foreach ($_POST['meta_fields'] as $key => $value) {
                update_post_meta($post_id, sanitize_key($key), sanitize_text_field($value));
            }
        }
    }
}
