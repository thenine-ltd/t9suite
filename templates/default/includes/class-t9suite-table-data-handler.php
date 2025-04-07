<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'class-t9suite-posts-filter-handler.php';
require_once plugin_dir_path(__FILE__) . 'class-t9suite-pagination-handler.php';

class T9SuiteTableDataHandler {

    private $post_type;
    private $paged;
    private $posts_per_page = 20;
    private $filter_handler;
    private $pagination_handler;
    private $taxonomies;

    public function __construct($post_type = 'post') {
        $this->post_type = post_type_exists($post_type) ? sanitize_text_field(wp_unslash($post_type)) : 'post';
        $this->paged = isset($_GET['paged']) ? absint(wp_unslash($_GET['paged'])) : 1;

        $this->taxonomies = get_object_taxonomies($this->post_type, 'objects');
        $this->filter_handler = new T9SuitePostsFilterHandler($this->post_type);

        $query_args = $this->build_query_args();
        $this->pagination_handler = new T9SuitePaginationHandler($query_args, $this->paged);
    }

    public function build_query_args() {
        $query_args = [
            'post_type'      => $this->post_type,
            'posts_per_page' => $this->posts_per_page,
            'paged'          => $this->paged,
        ];

        $filter_month = isset($_GET['filter_month']) ? sanitize_text_field(wp_unslash($_GET['filter_month'])) : '';
        if (!empty($filter_month)) {
            $month_year = explode('-', $filter_month);
            if (count($month_year) === 2 && ctype_digit($month_year[0]) && ctype_digit($month_year[1])) {
                $query_args['date_query'] = [
                    [
                        'year'  => (int) $month_year[0],
                        'month' => (int) $month_year[1],
                    ],
                ];
            }
        }

        if (!empty($this->taxonomies)) {
            $query_args['tax_query'] = ['relation' => 'AND'];
            foreach ($this->taxonomies as $taxonomy_slug => $taxonomy) {
                $filter_key = "filter_$taxonomy_slug";
                if (!empty($_GET[$filter_key])) {
                    $query_args['tax_query'][] = [
                        'taxonomy' => $taxonomy_slug,
                        'field'    => 'id',
                        'terms'    => intval($_GET[$filter_key]),
                    ];
                }
            }
        }

        return $query_args;
    }

    public function render_table() {
        $query_args = $this->build_query_args();
        $query = new WP_Query($query_args);

        if (!$query->have_posts()) {
            echo '<div class="alert alert-info text-center">';
            esc_html_e('No posts available yet. Please add a new one.', 't9suite');
            echo '</div>';
            return;
        }

        $this->filter_handler->render_filters();
        $this->render_table_data($query);
        $this->pagination_handler->render_pagination_and_total($query);

        wp_reset_postdata();
    }

    public function render_table_data($query) {
        wp_nonce_field('t9suite_bulk_action', '_wpnonce_bulk');

        $template_base = T9SUITE_PLUGIN_DIR . 'templates/default/includes/modules/';
        $module_templates = [
            'staffs'     => 'hrm/table.php',
            'customers'  => 'crm/table.php',
            'sites'      => 'epatrol/table.php',
            'checkpoints'=> 'epatrol/checkpoints/table.php',
            'tours'      => 'epatrol/tours/table.php',
        ];

        if (isset($module_templates[$this->post_type])) {
            $path = $template_base . $module_templates[$this->post_type];
            if (file_exists($path)) {
                include $path;
                return;
            }
        }

        // Default template
        ?>
        <div class="table-responsive mb-4 border rounded-1">
            <table class="table table-hover w-100 m-0 display text-nowrap align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="t9suite_select_all"></th>
                        <th width="30%"><a href="?orderby=title"><?php esc_html_e('Title', 't9suite'); ?></a></th>
                        <th><?php esc_html_e('Author', 't9suite'); ?></th>
                        <?php if (!empty($this->taxonomies)) : ?>
                            <?php foreach ($this->taxonomies as $taxonomy_slug => $taxonomy) : ?>
                                <th><?php echo esc_html($taxonomy->label); ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <th><a href="?orderby=date"><?php esc_html_e('Published Date', 't9suite'); ?></a></th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <tr>
                            <td><input type="checkbox" class="t9suite_bulk_checkbox" name="post_ids[]" value="<?php echo esc_attr(get_the_ID()); ?>"></td>
                            <td>
                                <a href="<?php echo esc_url(home_url("{$GLOBALS['custom_route']}/post-type-create/?post_type={$this->post_type}&post_id=" . get_the_ID())); ?>">
                                    <?php echo esc_html(get_the_title()); ?>
                                </a>
                                <div class="t9suite_post_tools" style="display: none;">
                                    <a href="#" class="t9suite_edit"><?php esc_html_e('Edit', 't9suite'); ?></a> |
                                    <a href="#" class="t9suite_delete"><?php esc_html_e('Delete', 't9suite'); ?></a>
                                </div>
                            </td>
                            <td><?php echo esc_html(get_the_author()); ?></td>
                            <?php if (!empty($this->taxonomies)) : ?>
                                <?php foreach ($this->taxonomies as $taxonomy_slug => $taxonomy) : ?>
                                    <td>
                                        <?php
                                        $terms = get_the_terms(get_the_ID(), $taxonomy_slug);
                                        if (!empty($terms) && !is_wp_error($terms)) {
                                            echo esc_html(implode(', ', wp_list_pluck($terms, 'name')));
                                        } else {
                                            esc_html_e('—', 't9suite');
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <td><?php echo esc_html(get_the_date('Y-m-d') . ' ' . get_the_time('H:i:s')); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
