<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class T9AdminProBreadcrumbHandler {

    /**
     * Render the breadcrumb
     */
    public function t9admin_pro_render_breadcrumb($post_type, $action) {
        $breadcrumb = [
            [
                'label' => esc_html__('Dashboard', 't9admin-pro'),
                'url'   => esc_url(home_url('/t9admin/')),
                'active' => false,
            ],
        ];

        if ($post_type && post_type_exists($post_type)) {
            $post_type_obj = get_post_type_object($post_type);
            $breadcrumb[] = [
                'label' => sprintf(esc_html__('Manage %s', 't9admin-pro'), $post_type_obj->label),
                'url'   => esc_url(home_url('/t9admin/?page=' . $post_type)),
                'active' => ($action === 'manage'),
            ];

            if ($action === 'post-type-create') {
                $breadcrumb[] = [
                    'label' => sprintf(esc_html__('Create New %s', 't9admin-pro'), $post_type_obj->label),
                    'url'   => '',
                    'active' => true,
                ];
            } elseif ($action === 'edit') {
                $breadcrumb[] = [
                    'label' => esc_html__('Edit', 't9admin-pro'),
                    'url'   => '',
                    'active' => true,
                ];
            }
        }

        $this->t9admin_pro_render_breadcrumb_html($breadcrumb);
    }

    /**
     * Generate the breadcrumb HTML
     */
    private function t9admin_pro_render_breadcrumb_html($breadcrumb) {
        ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php foreach ($breadcrumb as $item) : ?>
                    <li class="breadcrumb-item <?php echo $item['active'] ? 'active' : ''; ?>">
                        <?php if (!$item['active']) : ?>
                            <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
                        <?php else : ?>
                            <?php echo esc_html($item['label']); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php
    }
}
