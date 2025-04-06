
<?php
if (!defined('ABSPATH')) exit; 
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-title-heading-handler.php';
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-posts-form-handler.php';


$post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_handler = new T9AdminProPostsFormHandler($post_type);
    $form_handler->t9admin_pro_handle_form_submission();
}

if (class_exists('T9AdminProTitleHeadingHandler')) {
    $title_heading_handler = new T9AdminProTitleHeadingHandler($post_type, $action);
    $title_heading_handler->t9admin_pro_render_heading();
}

if (isset($_GET['message']) && $_GET['message'] === 'success') {
    echo '<div class="alert alert-success" role="alert">';
    _e('Post created successfully!', 't9admin');
    echo '</div>';
}
?>

<div class="content-body">
    <?php
    $form_handler = new T9AdminProPostsFormHandler($post_type);
    $form_handler->t9admin_pro_render_form();
    ?>
</div>
