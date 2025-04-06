<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9AdminProPostsSearchHandler {

    private $post_type;

    public function __construct($post_type = 'post') {
        $this->post_type = post_type_exists($post_type) ? sanitize_text_field(wp_unslash($post_type)) : 'post';
    }

    /**
     * Render Search Input
     */
    public function t9admin_pro_render_search() {
        $current_url = esc_url(add_query_arg(null, null));
        $search_query = isset($_GET['search_query']) ? sanitize_text_field($_GET['search_query']) : '';
        ?>
        <form method="get" action="<?php echo $current_url; ?>" class="d-flex justify-content-end">
            <input type="hidden" name="page" value="<?php echo esc_attr($this->post_type); ?>">
            <div class="input-group w-50">
                <input type="text" name="search_query" class="form-control w-auto" placeholder="<?php esc_attr_e('Search Posts', 't9admin-pro'); ?>" value="<?php echo esc_attr($search_query); ?>">
                <button type="submit" class="btn bg-primary-subtle text-primary">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
        <?php
    }
}
