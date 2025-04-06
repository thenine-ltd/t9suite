<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9AdminProUploadHandler {

    /**
     * Render featured image upload field
     */
    public function t9admin_pro_render_featured_image_field() {
        ?>
        <div class="mb-3">
            <div class="card">
                <div class="border-bottom title-part-padding">
                    <label for="featured-image" class="card-title form-label"><?php esc_html_e('Featured Image', 't9admin-pro'); ?></label>
                </div>
                <div class="card-body">
                    <input 
                    type="file" 
                    name="featured_image" 
                    id="featured-image" 
                    class="dropzone form-control">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save featured image
     */
    public function t9admin_pro_save_featured_image($post_id) {
        if (isset($_FILES['featured_image']) && !empty($_FILES['featured_image']['tmp_name'])) {
            $file = $_FILES['featured_image'];
            $upload = wp_handle_upload($file, ['test_form' => false]);

            if (isset($upload['file'])) {
                $attachment_id = wp_insert_attachment([
                    'guid'           => $upload['url'],
                    'post_mime_type' => $upload['type'],
                    'post_title'     => sanitize_file_name($upload['file']),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                ], $upload['file'], $post_id);

                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_data);

                set_post_thumbnail($post_id, $attachment_id);
            }
        }
    }
}
