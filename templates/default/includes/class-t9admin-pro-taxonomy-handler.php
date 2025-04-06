<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9AdminProTaxonomyHandler {

    private $post_type;

    public function __construct($post_type) {
        $this->post_type = sanitize_text_field($post_type);
        add_action('wp_ajax_t9admin_pro_add_taxonomy_term', [$this, 't9admin_pro_add_taxonomy_term']);
    }

    /**
     * Render taxonomy fields inside Bootstrap card
     */
    public function t9admin_pro_render_taxonomy_fields() {
        $taxonomies = get_object_taxonomies($this->post_type, 'objects');
        if (empty($taxonomies)) {
            return;
        }

        foreach ($taxonomies as $taxonomy) {
            ?>
            <div class="card mb-3">
                <!-- Card Header -->
                <div class="card-header">
                    <h5 class="mb-0"><?php echo esc_html($taxonomy->label); ?></h5>
                </div>

                <!-- Card Body -->
                <div class="card-body">
                    <?php $terms = get_terms([
                        'taxonomy'   => $taxonomy->name,
                        'hide_empty' => false,
                    ]); ?>

                    <?php if (!empty($terms)) : ?>
                        <ul class="taxonomy-list list-unstyled">
                            <?php $this->t9admin_pro_render_term_hierarchy($terms); ?>
                        </ul>
                    <?php else : ?>
                        <p><?php esc_html_e('No terms available.', 't9admin-pro'); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Card Footer -->
                <div class="card-footer">
                    <a href="#" class="t9admin-add-new-term-link btn btn-outline-primary btn-sm" data-taxonomy="<?php echo esc_attr($taxonomy->name); ?>">
                        <?php esc_html_e('+ Add New Category', 't9admin-pro'); ?>
                    </a>
                    <div class="t9admin-add-term-form d-none mt-3" data-taxonomy="<?php echo esc_attr($taxonomy->name); ?>">
                        <input type="text" name="term_name" class="form-control mb-2" placeholder="<?php esc_attr_e('Enter category name', 't9admin-pro'); ?>" required>
                        <select name="parent" class="form-select mb-2">
                            <option value=""><?php esc_html_e('-- Parent Category --', 't9admin-pro'); ?></option>
                            <?php foreach ($terms as $term) : ?>
                                <option value="<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-primary t9admin-save-term" data-taxonomy="<?php echo esc_attr($taxonomy->name); ?>">
                            <?php esc_html_e('Add New Category', 't9admin-pro'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Render taxonomy list with hierarchy
     */
    private function t9admin_pro_render_term_hierarchy($terms, $parent_id = 0) {
        foreach ($terms as $term) {
            if ($term->parent == $parent_id) {
                ?>
                <li>
                    
                    <div class="form-check">
                        <input id="flexCheckDefault" class="form-check-input" type="checkbox" name="tax_input[<?php echo esc_attr($term->taxonomy); ?>][]" value="<?php echo esc_attr($term->term_id); ?>">
                        <label class="form-check-label" for="flexCheckDefault">
                            <?php echo esc_html($term->name); ?>
                        </label>
                    </div>
                    <?php
                    $child_terms = array_filter($terms, function ($t) use ($term) {
                        return $t->parent == $term->term_id;
                    });
                    if (!empty($child_terms)) {
                        echo '<ul class="ms-3">';
                        $this->t9admin_pro_render_term_hierarchy($terms, $term->term_id);
                        echo '</ul>';
                    }
                    ?>
                </li>
                <?php
            }
        }
    }

    /**
     * AJAX handler to add new taxonomy term
     */
    
     public function t9admin_pro_add_taxonomy_term() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 't9admin_pro_add_term_action')) {
            wp_send_json_error(['message' => 'Nonce verification failed.'], 400);
        }
    
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
        $term_name = isset($_POST['term_name']) ? sanitize_text_field($_POST['term_name']) : '';
        $parent = isset($_POST['parent']) ? intval($_POST['parent']) : 0;
    
        // Log data received
        error_log(print_r($_POST, true));
    
        if (empty($taxonomy) || empty($term_name)) {
            wp_send_json_error([
                'message' => 'Invalid data.',
                'debug' => [
                    'taxonomy' => $taxonomy,
                    'term_name' => $term_name,
                    'parent' => $parent,
                ],
            ], 400);
        }
    
        if (!taxonomy_exists($taxonomy)) {
            wp_send_json_error([
                'message' => 'Taxonomy does not exist.',
                'debug' => ['taxonomy' => $taxonomy],
            ], 400);
        }
    
        $term = wp_insert_term($term_name, $taxonomy, ['parent' => $parent]);
    
        if (is_wp_error($term)) {
            wp_send_json_error([
                'message' => $term->get_error_message(),
                'debug' => [
                    'taxonomy' => $taxonomy,
                    'term_name' => $term_name,
                    'parent' => $parent,
                ],
            ], 400);
        }
    
        wp_send_json_success([
            'term_id' => $term['term_id'],
            'term_name' => $term_name,
        ]);
    }
    
    
    
    

    
}
