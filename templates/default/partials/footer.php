<?php
if (!defined('ABSPATH')) exit;

require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-enqueue-handler.php';
?>

<!-- Iconify -->
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

<!-- Global JS config -->
<script>
    window.t9suite = {
        ajaxurl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
        nonce: '<?php echo esc_js(wp_create_nonce('t9suite_add_term_action')); ?>'
    };
</script>

<!-- Enqueue JS -->
<?php
if (class_exists('T9SuiteAssetsManager')) {
    $assets_manager = new T9SuiteAssetsManager();
    if (method_exists($assets_manager, 't9suite_render_js')) {
        $assets_manager->t9suite_render_js();
    } else {
        echo '<!-- Method t9suite_render_js not found in T9SuiteAssetsManager -->';
    }
} else {
    echo '<!-- T9SuiteAssetsManager class not found -->';
}
?>
</body>
</html>
