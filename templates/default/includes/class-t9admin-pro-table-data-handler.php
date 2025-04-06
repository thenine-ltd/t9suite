<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'class-t9admin-pro-posts-filter-handler.php';
require_once plugin_dir_path(__FILE__) . 'class-t9admin-pro-pagination-handler.php';

class T9AdminProTableDataHandler {

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
        $this->filter_handler = new T9AdminProPostsFilterHandler($this->post_type);

        $query_args = $this->t9admin_pro_build_query_args();
        $this->pagination_handler = new T9AdminProPaginationHandler($query_args, $this->paged);
    }

    public function t9admin_pro_build_query_args() {
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
                if (isset($_GET[$filter_key]) && !empty($_GET[$filter_key])) {
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

    public function t9admin_pro_render_table() {
        $query_args = $this->t9admin_pro_build_query_args();
        $query = new WP_Query($query_args);

        if (!$query->have_posts()) {
            echo '<div class="alert alert-info text-center">';
            esc_html_e('No posts available yet. Please add a new one.', 't9admin-pro');
            echo '</div>';
            return;
        }

        $this->filter_handler->t9admin_pro_render_filters();
        $this->t9admin_pro_render_table_data($query);
        $this->pagination_handler->t9admin_pro_render_pagination_and_total($query);

        wp_reset_postdata();
    }

    public function t9admin_pro_render_table_data($query) {
        wp_nonce_field('t9admin_pro_bulk_action', '_wpnonce_bulk');

        if ($this->post_type === 'staffs') {
            $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/hrm/table.php';
            if (file_exists($template_path)) {
                include $template_path;
                return;
            }
        } elseif ($this->post_type === 'customers') {
            $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/crm/table.php';
            if (file_exists($template_path)) {
                include $template_path;
                return;
            }
        } elseif ($this->post_type === 'sites') {
            $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/epatrol/table.php';
            if (file_exists($template_path)) {
                include $template_path;
                return;
            }
        } elseif ($this->post_type === 'checkpoints') {
            $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/epatrol/checkpoints/table.php';
            if (file_exists($template_path)) {
                include $template_path;
                return;
            }
        } elseif ($this->post_type === 'tours') {
            $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/epatrol/tours/table.php';
            if (file_exists($template_path)) {
                include $template_path;
                return;
            }
        } else {
            // Template mặc định cho các post_type khác
            ?>
            <div class="table-responsive mb-4 border rounded-1">
                <table class="table table-hover w-100 m-0 display text-nowrap align-middle">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="t9admin_pro_select_all"></th>
                            <th class="sorting sorting_asc" tabindex="0" aria-controls="show_hide_col" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Name: activate to sort column descending" width="30%"><a href="?orderby=title"><?php esc_html_e('Title', 't9admin-pro'); ?></a></th>
                            <th><?php esc_html_e('Author', 't9admin-pro'); ?></th>
                            <?php if (!empty($this->taxonomies)) : ?>
                                <?php foreach ($this->taxonomies as $taxonomy_slug => $taxonomy) : ?>
                                    <th><?php echo esc_html($taxonomy->label); ?></th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <th><a href="?orderby=date"><?php esc_html_e('Published Date', 't9admin-pro'); ?></a></th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                            <tr>
                                <td><input type="checkbox" class="t9admin_pro_bulk_checkbox" name="post_ids[]" value="<?php echo esc_attr(get_the_ID()); ?>"></td>
                                <td>
                                    <a href="<?php echo esc_url(home_url("{$GLOBALS['custom_route']}/post-type-create/?post_type={$this->post_type}&post_id=" . get_the_ID())); ?>">
                                        <?php echo esc_html(get_the_title()); ?>
                                    </a>
                                    <div class="t9admin_pro_post_tools" style="display: none;">
                                        <a href="#" class="t9admin_pro_edit"><?php esc_html_e('Edit', 't9admin-pro'); ?></a> |
                                        <a href="#" class="t9admin_pro_delete"><?php esc_html_e('Delete', 't9admin-pro'); ?></a>
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
                                                esc_html_e('—', 't9admin-pro');
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <td><?php echo esc_html(get_the_date('Y-m-d') . ' at ' . get_the_time('H:i:s')); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }
}