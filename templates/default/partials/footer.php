<?php
if (!defined('ABSPATH')) exit;
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-enqueue-handler.php';
?>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

<script>
    window.t9adminPro = {
        ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('t9admin_pro_add_term_action'); ?>'
    };
</script>
<?php
if (class_exists('T9AdminProAssetsManager')) {
    $assets_manager = new T9AdminProAssetsManager();
    if (method_exists($assets_manager, 't9admin_pro_render_js')) {
        $assets_manager->t9admin_pro_render_js();
    } else {
        echo '<!-- Method t9admin_pro_render_assets not found in T9AdminProAssetsManager -->';
    }
} else {
    echo '<!-- T9AdminProAssetsManager class not found -->';
}
?>
</body>
</html>