<?php
if (!defined('ABSPATH')) exit;

require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-table-data-handler.php';
require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-title-heading-handler.php';

$post_type = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'post';
$action    = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : 'manage';

if (class_exists('T9SuiteTitleHeadingHandler')) {
    $title_heading_handler = new T9SuiteTitleHeadingHandler($post_type, $action);
    $title_heading_handler->render_heading();
}
?>

<div class="content-body">
    <div class="card">
        <div class="card-body">
            <?php
            if (class_exists('T9SuiteTableDataHandler')) {
                $table_handler = new T9SuiteTableDataHandler($post_type);
                $table_handler->render_table();
            }
            ?>
        </div>
    </div>
</div>
