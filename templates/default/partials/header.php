<?php
if (!defined('ABSPATH')) exit;

require_once T9SUITE_PLUGIN_DIR . 'templates/default/includes/class-t9suite-enqueue-handler.php';

$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'default-page';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- Favicon -->
    <?php
    if (class_exists('\T9Suite\Settings\T9Suite_Settings')) {
        $favicon_url = get_option('t9suite_favicon'); // Khuyên nên đổi tên option này
        if ($favicon_url) {
            echo '<link rel="shortcut icon" href="' . esc_url($favicon_url) . '" type="image/png">';
        } else {
            echo '<link rel="shortcut icon" href="' . esc_url(get_template_directory_uri() . '/assets/images/default-favicon.ico') . '" type="image/png">';
        }
    }
    ?>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Core CSS -->
    <?php
    if (class_exists('T9SuiteAssetsManager')) {
        $assets_manager = new T9SuiteAssetsManager();
        if (method_exists($assets_manager, 't9suite_render_css')) {
            $assets_manager->t9suite_render_css();
        } else {
            echo '<!-- Method t9suite_render_css not found in T9SuiteAssetsManager -->';
        }
    } else {
        echo '<!-- T9SuiteAssetsManager class not found -->';
    }

    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
    $title = '';

    if ($page || $post_type) {
        $post_types = get_post_types(['public' => true], 'objects');
        $post_type_key = $post_type ?: $page;

        if (isset($post_types[$post_type_key])) {
            $title = $post_types[$post_type_key]->label;
        } else {
            $title = ucfirst($post_type_key);
        }
    } else {
        $company_name = get_option('t9suite_company_name', '');
        $title = !empty($company_name) ? $company_name : get_bloginfo('name');
    }
    ?>

    <title><?php echo esc_html($title); ?></title>
</head>

<body>
