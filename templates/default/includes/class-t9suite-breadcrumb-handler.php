<?php

if (!defined('ABSPATH')) exit;

class T9SuiteBreadcrumbHandler {

    /**
     * Render the breadcrumb trail.
     *
     * @param string $post_type
     * @param string $action
     */
    public function render_breadcrumb($post_type, $action) {
        $breadcrumb = [
            [
                'label' => esc_html__('Dashboard', 't9suite'),
                'url'   => esc_url(home_url('/t9suite/')),
                'active' => false,
            ],
        ];

        if ($post_type && post_type_exists($post_type)) {
            $post_type_obj = get_post_type_object($post_type);
            $breadcrumb[] = [
                'label' => sprintf(esc_html__('Manage %s', 't9suite'), $post_type_obj->label),
                'url'   => esc_url(home_url('/t9suite/?page=' . $post_type)),
                'active' => ($action === 'manage'),
            ];

            if ($action === 'post-type-create') {
                $breadcrumb[] = [
                    'label' => sprintf(esc_html__('Create New %s', 't9suite'), $post_type_obj->label),
                    'url'   => '',
                    'active' => true,
                ];
            } elseif ($action === 'edit') {
                $breadcrumb[] = [
                    'label' => esc_html__('Edit', 't9suite'),
                    'url'   => '',
                    'active' => true,
                ];
            }
        }

        $this->render_breadcrumb_html($breadcrumb);
    }

    /**
     * Render breadcrumb HTML.
     *
     * @param array $breadcrumb
     */
    private function render_breadcrumb_html($breadcrumb) {
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
