<?php
if (!defined('ABSPATH')) exit; 
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-table-data-handler.php';
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-title-heading-handler.php';

$post_type = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'post';
$action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : 'manage';

if (class_exists('T9AdminProTitleHeadingHandler')) {
    $title_heading_handler = new T9AdminProTitleHeadingHandler($post_type, $action);
    $title_heading_handler->t9admin_pro_render_heading();
}
?>
<div class="content-body">
    <div class="card">
        <div class="card-body">
            <?php
            if (class_exists('T9AdminProTableDataHandler')) {
                $table_handler = new T9AdminProTableDataHandler($post_type);
                $table_handler->t9admin_pro_render_table();
            }
            ?>
        </div>
    </div>
</div>
