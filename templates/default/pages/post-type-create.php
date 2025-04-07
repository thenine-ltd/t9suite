<?php
if (!defined('ABSPATH')) exit;

require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-title-heading-handler.php';
require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-posts-form-handler.php';

$post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
$action    = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : 'create';

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_handler = new T9SuitePostsFormHandler($post_type);
    $form_handler->handle_form_submission();
}

// Render heading
if (class_exists('T9SuiteTitleHeadingHandler')) {
    $title_heading_handler = new T9SuiteTitleHeadingHandler($post_type, $action);
    $title_heading_handler->render_heading();
}

// Thông báo khi tạo thành công
if (isset($_GET['message']) && $_GET['message'] === 'success') {
    echo '<div class="alert alert-success" role="alert">';
    _e('Post created successfully!', 't9suite');
    echo '</div>';
}
?>

<div class="content-body">
    <?php
    $form_handler = new T9SuitePostsFormHandler($post_type);
    $form_handler->render_form();
    ?>
</div>
