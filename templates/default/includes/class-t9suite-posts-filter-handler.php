<?php

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'class-t9suite-posts-bulk-action-handler.php';
require_once plugin_dir_path(__FILE__) . 'class-t9suite-posts-search-handler.php';

class T9SuitePostsFilterHandler {

    private $post_type;
    private $bulk_action_handler;
    private $search_handler;

    public function __construct($post_type = 'post') {
        $this->post_type = post_type_exists($post_type) ? sanitize_text_field(wp_unslash($post_type)) : 'post';
        $this->bulk_action_handler = new T9SuitePostsBulkActionHandler($this->post_type);
        $this->search_handler = new T9SuitePostsSearchHandler($this->post_type);
    }

    /**
     * Render the filter layout
     */
    public function render_filters() {
        $current_url = esc_url(add_query_arg(null, null));
        $taxonomies = get_object_taxonomies($this->post_type, 'objects');
        ?>
        <div class="row mb-4 align-items-center">
            <!-- Left Section: Bulk Action & Filters -->
            <div class="col-md-6">
                <form method="get" action="<?php echo $current_url; ?>" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="page" value="<?php echo esc_attr($this->post_type); ?>">

                    <!-- Render Bulk Action -->
                    <?php $this->bulk_action_handler->render_bulk_action(); ?>

                    <!-- Month Filter -->
                    <select class="form-select w-auto" name="filter_month">
                        <option value=""><?php esc_html_e('All Months', 't9suite'); ?></option>
                        <?php
                        global $wpdb, $wp_locale;
                        $months = $wpdb->get_results("
                            SELECT DISTINCT YEAR(post_date) AS year, MONTH(post_date) AS month
                            FROM $wpdb->posts
                            WHERE post_type = '{$this->post_type}' AND post_status = 'publish'
                            ORDER BY post_date DESC
                        ");
                        foreach ($months as $month) {
                            printf(
                                '<option value="%s">%s</option>',
                                esc_attr(sprintf('%04d-%02d', $month->year, $month->month)),
                                esc_html($wp_locale->get_month($month->month) . ' ' . $month->year)
                            );
                        }
                        ?>
                    </select>

                    <!-- Taxonomy Filters -->
                    <?php if (!empty($taxonomies)) : ?>
                        <?php foreach ($taxonomies as $taxonomy_slug => $taxonomy) : ?>
                            <select class="form-select w-auto" name="filter_<?php echo esc_attr($taxonomy_slug); ?>">
                                <option value=""><?php printf(esc_html__('All %s', 't9suite'), esc_html($taxonomy->label)); ?></option>
                                <?php
                                $terms = get_terms(['taxonomy' => $taxonomy_slug, 'hide_empty' => false]);
                                foreach ($terms as $term) {
                                    echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                                }
                                ?>
                            </select>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary"><?php esc_html_e('Filter', 't9suite'); ?></button>
                </form>
            </div>

            <!-- Right Section: Search -->
            <div class="col-md-6 text-end">
                <?php $this->search_handler->render_search(); ?>
            </div>
        </div>
        <?php
    }
}
