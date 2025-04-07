<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9AdminProTagsHandler {

    private $post_type;

    public function __construct($post_type) {
        $this->post_type = sanitize_text_field($post_type);
    }

    /**
     * Render tag field
     */
    public function t9admin_pro_render_tag_field() {
        if (!taxonomy_exists('post_tag')) {
            return;
        }

        ?>
        <div class="mb-3">
            <div class="card">
                <div class="border-bottom title-part-padding">
                    <label for="post-tags" class="card-title form-label"><?php esc_html_e('Tags', 't9admin-pro'); ?></label>
                </div>
                <div class="card-body">
                    <input 
                    type="text" 
                    name="tags" 
                    id="post-tags" 
                    class="form-control" 
                    placeholder="<?php esc_attr_e('Enter tags, separated by commas', 't9admin-pro'); ?>">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save tags
     */
    public function t9admin_pro_save_tags($post_id) {
        if (isset($_POST['tags']) && !empty($_POST['tags'])) {
            $tags = explode(',', sanitize_text_field(wp_unslash($_POST['tags'])));
            wp_set_post_tags($post_id, $tags);
        }
    }
}
