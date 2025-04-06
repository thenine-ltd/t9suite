<?php
if (!defined('ABSPATH')) exit;
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-enqueue-handler.php';
$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'default-page';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- Favicon icon-->
    <?php
    if (class_exists('T9AdminSettings')) {
        $favicon_url = get_option('the9_favicon');
        if ($favicon_url) {
            echo '<link rel="shortcut icon" href="' . esc_url($favicon_url) . '" type="image/png">';
        } else {
            echo '<link rel="shortcut icon" href="' . esc_url(get_template_directory_uri() . '/assets/images/default-favicon.ico') . '" type="image/png">';
        }
    }
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Core Css -->
    <?php
    if (class_exists('T9AdminProAssetsManager')) {
        $assets_manager = new T9AdminProAssetsManager();
        if (method_exists($assets_manager, 't9admin_pro_render_css')) {
            $assets_manager->t9admin_pro_render_css();
        } else {
            echo '<!-- Method t9admin_pro_render_assets not found in T9AdminProAssetsManager -->';
        }
    } else {
        echo '<!-- T9AdminProAssetsManager class not found -->';
    }
    
    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
    $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
    $title = '';
    
    if ($page || $post_type) {
        // Nếu có page hoặc post_type, lấy label của post type
        $post_types = get_post_types(['public' => true], 'objects');
        $post_type_key = $post_type ?: $page; // Ưu tiên post_type, nếu không có thì dùng page
        if (isset($post_types[$post_type_key])) {
            $title = $post_types[$post_type_key]->label; // Lấy label của post type
        } else {
            $title = ucfirst($post_type_key); // Nếu không tìm thấy post type, dùng giá trị thô và viết hoa chữ cái đầu
        }
    } else {
        // Nếu không có page hoặc post_type, lấy company name hoặc site title
        $company_name = get_option('t9admin_pro_company_name', '');
        $title = !empty($company_name) ? $company_name : get_bloginfo('name');
    }
    ?>

    <title><?php echo esc_html($title); ?></title>
</head>

<body>