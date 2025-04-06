<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9AdminProPaginationHandler {

    private $query_args;
    private $paged;

    public function __construct($query_args, $paged) {
        $this->query_args = $query_args;
        $this->paged = max(1, intval($paged));
    }

    /**
     * Render pagination and total posts
     */
    public function t9admin_pro_render_pagination_and_total($query) {
        // Total posts calculation
        $count_query = new WP_Query(array_merge($this->query_args, [
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]));
        $total_posts = $count_query->found_posts;


        echo '<div class="d-flex align-items-stretch justify-content-between">';
            $this->t9admin_pro_render_pagination($query);
            echo '<div class="text-end mt-2">';
            printf(esc_html__('Total: %d', 't9admin-pro'), intval($total_posts));
            echo '</div>';
        echo '</div>';
    }

    /**
     * Render pagination
     */
    private function t9admin_pro_render_pagination($query) {
        $pagination = paginate_links([
            'base'      => add_query_arg(array_merge($_GET, ['paged' => '%#%'])),
            'format'    => '',
            'current'   => $this->paged,
            'total'     => $query->max_num_pages,
            'type'      => 'array',
            'prev_text' => esc_html__('&laquo;', 't9admin-pro'),
            'next_text' => esc_html__('&raquo;', 't9admin-pro'),
        ]);

        if ($pagination) {
            echo '<nav aria-label="Page navigation">';
            echo '<ul class="pagination">';
            foreach ($pagination as $page) {
                echo '<li class="page-item' . (strpos($page, 'current') !== false ? ' active' : '') . '">';
                echo str_replace('page-numbers', 'page-link', $page);
                echo '</li>';
            }
            echo '</ul>';
            echo '</nav>';
        }
    }
}
