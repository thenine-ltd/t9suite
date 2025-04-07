<?php

if (!defined('ABSPATH')) exit;

class T9SuitePostsBulkActionHandler {

    private $post_type;

    public function __construct($post_type = 'post') {
        $this->post_type = post_type_exists($post_type) ? sanitize_text_field(wp_unslash($post_type)) : 'post';
    }

    /**
     * Render Bulk Action Dropdown.
     */
    public function render_bulk_action() {
        ?>
        <div class="input-group flex-nowrap">
            <select class="form-select w-auto" name="bulk_action">
                <option value=""><?php esc_html_e('Bulk Actions', 't9suite'); ?></option>
                <option value="delete"><?php esc_html_e('Delete', 't9suite'); ?></option>
            </select>
            <button type="submit" class="btn bg-primary-subtle text-primary">
                <i class="bi bi-arrow-right-short"></i>
            </button>
        </div>
        <?php
    }

    /**
     * Handle Bulk Action (like delete).
     */
    public function handle_bulk_action() {
        if (!isset($_GET['bulk_action']) || $_GET['bulk_action'] !== 'delete') {
            return;
        }

        if (!isset($_GET['post_ids']) || empty($_GET['post_ids'])) {
            return;
        }

        // Check nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 't9suite_bulk_action')) {
            wp_die(esc_html__('Invalid request. Nonce verification failed.', 't9suite'));
        }

        $post_ids = array_map('intval', $_GET['post_ids']);

        foreach ($post_ids as $post_id) {
            if (get_post_type($post_id) === $this->post_type) {
                wp_trash_post($post_id); // Move to trash
            }
        }

        // Clean URL and redirect
        wp_redirect(remove_query_arg(['bulk_action', 'post_ids', '_wpnonce']));
        exit;
    }
}
