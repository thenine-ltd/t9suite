<?php

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'class-t9suite-breadcrumb-handler.php';

class T9SuiteTitleHeadingHandler {

    private $post_type;
    private $action;
    private $breadcrumb_handler;

    public function __construct($post_type = '', $action = 'manage') {
        $this->post_type = post_type_exists($post_type) ? sanitize_text_field(wp_unslash($post_type)) : '';
        $this->action = sanitize_text_field(wp_unslash($action));
        $this->breadcrumb_handler = new T9SuiteBreadcrumbHandler();
    }

    /**
     * Render the heading and breadcrumb.
     */
    public function render_heading() {
        ?>
        <div class="content-header mb-4">
            <!-- Breadcrumb -->
            <?php $this->breadcrumb_handler->render_breadcrumb($this->post_type, $this->action); ?>

            <!-- Title and Add New Button -->
            <div class="d-flex justify-content-between align-items-center">
                <h1>
                    <?php echo esc_html($this->get_title()); ?>
                </h1>

                <?php if ($this->post_type) : ?>
                    <a href="<?php echo esc_url(home_url('/t9suite/post-type-create/?post_type=' . $this->post_type)); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-plus-lg"></i> <?php esc_html_e('Add New', 't9suite'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get dynamic title based on post type and action.
     */
    private function get_title() {
        $post_type_obj = $this->post_type ? get_post_type_object($this->post_type) : null;

        switch ($this->action) {
            case 'post-type-create':
                return sprintf(esc_html__('Create New %s', 't9suite'), $post_type_obj->labels->singular_name ?? esc_html__('Item', 't9suite'));
            case 'edit':
                return sprintf(esc_html__('Edit %s', 't9suite'), $post_type_obj->labels->singular_name ?? esc_html__('Item', 't9suite'));
            case 'manage':
            default:
                return sprintf(esc_html__('Manage %s', 't9suite'), $post_type_obj->label ?? esc_html__('Items', 't9suite'));
        }
    }
}
