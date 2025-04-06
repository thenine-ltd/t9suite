<?php
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-branding-handler.php';
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-menu-handler.php';

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Lấy menu items từ settings
$menu_items = get_option('t9admin_pro_menu_items', []);
$mini_nav_items = array_filter($menu_items, function($item) {
    return $item['post_type'] === 'mini-nav';
});

// Gán children cho mỗi mini-nav dựa trên data-parent-index
$structured_menu = [];
foreach ($menu_items as $key => $item) {
    if ($item['post_type'] === 'mini-nav') {
        $item['children'] = [];
        $item['index'] = $key; // Lưu index gốc từ settings
        $structured_menu[$key] = $item;
    }
}

// Gán child menu vào parent tương ứng
foreach ($menu_items as $key => $item) {
    if ($item['post_type'] !== 'mini-nav') {
        $parent_index = null;
        // Tìm parent gần nhất trước child
        for ($i = $key - 1; $i >= 0; $i--) {
            if (isset($menu_items[$i]) && $menu_items[$i]['post_type'] === 'mini-nav') {
                $parent_index = $i;
                break;
            }
        }
        if ($parent_index !== null && isset($structured_menu[$parent_index])) {
            $structured_menu[$parent_index]['children'][] = $item;
        }
    }
}
?>

<aside class="side-mini-panel with-vertical">
    <!-- Start Vertical Layout Sidebar -->
    <div class="iconbar">
        <div>
            <div class="mini-nav">
                <div class="brand-logo d-flex align-items-center justify-content-center">
                    <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <iconify-icon icon="solar:hamburger-menu-line-duotone" class="fs-7"></iconify-icon>
                    </a>
                </div>
                <ul class="mini-nav-ul" data-simplebar>
                    <!-- Dashboard cố định -->
                    <li class="mini-nav-item" id="mini-1" data-parent-index="">
                        <a href="javascript:void(0)" 
                           data-bs-toggle="tooltip" 
                           data-bs-custom-class="custom-tooltip" 
                           data-bs-placement="right" 
                           data-bs-title="<?php esc_attr_e('Dashboards', 't9admin-pro'); ?>">
                            <iconify-icon icon="solar:layers-line-duotone" class="fs-5"></iconify-icon>
                        </a>
                    </li>
                    <!-- Các mini-nav từ settings -->
                    <?php foreach ($structured_menu as $item): ?>
                        <li class="mini-nav-item" id="mini-<?php echo esc_attr($item['index'] + 2); ?>" data-parent-index="<?php echo esc_attr($item['index']); ?>">
                            <a href="javascript:void(0)" 
                               data-bs-toggle="tooltip" 
                               data-bs-custom-class="custom-tooltip" 
                               data-bs-placement="right" 
                               data-bs-title="<?php echo esc_attr($item['label']); ?>">
                                <iconify-icon icon="<?php echo esc_attr($item['icon'] ?: 'solar:layers-line-duotone'); ?>" class="fs-5"></iconify-icon>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Sidebarmenu Wrapper -->
            <div class="sidebarmenu">
                <?php if (class_exists('T9AdminProBrandingHandler')): ?>
                    <?php T9AdminProBrandingHandler::t9admin_pro_render_branding(); ?>
                <?php endif; ?>
                <?php if (class_exists('T9AdminProMenuHandler')): ?>
                    <?php
                    $menu_handler = new T9AdminProMenuHandler();
                    $menu_handler->t9admin_pro_render_nav_menu($structured_menu);
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</aside>

<script>
document.addEventListener("DOMContentLoaded", function () {
    "use strict";
    var isSidebar = document.getElementsByClassName("side-mini-panel");
    if (isSidebar.length > 0) {
        var url = window.location + "";
        var path = url.replace(window.location.protocol + "//" + window.location.host + "/", "");

        // Tìm và active menu item dựa trên URL
        function findMatchingElement() {
            var currentUrl = window.location.href;
            var anchors = document.querySelectorAll(".sidebar-nav a");
            for (var i = 0; i < anchors.length; i++) {
                if (anchors[i].href === currentUrl) {
                    return anchors[i];
                }
            }
            return null;
        }

        var activeElement = findMatchingElement();
        if (activeElement) {
            activeElement.classList.add("active");
            activeElement.closest('.sidebar-item')?.classList.add("selected");
        }

        // Xử lý click vào mini-nav items
        document.querySelectorAll(".mini-nav-item").forEach(function (item) {
            item.addEventListener("click", function () {
                // Remove selected class từ tất cả mini-nav items
                document.querySelectorAll(".mini-nav-item").forEach(function (nav) {
                    nav.classList.remove("selected");
                });
                // Thêm selected class cho item được click
                this.classList.add("selected");

                // Ẩn tất cả sidebar-nav
                document.querySelectorAll(".sidebar-nav").forEach(function (nav) {
                    nav.style.display = 'none';
                });

                // Hiển thị sidebar-nav tương ứng dựa trên data-parent-index
                var parentIndex = this.getAttribute("data-parent-index");
                var navId = parentIndex === "" ? "nav-1" : "nav-" + (parseInt(parentIndex) + 2);
                var targetNav = document.getElementById(navId);
                if (targetNav) {
                    targetNav.style.display = 'block';
                } 

                // Đảm bảo sidebarmenu hiển thị
                document.querySelector(".sidebarmenu").classList.add("active");
            });
        });

        // Hiển thị Dashboard mặc định khi load trang
        var initialMini = document.getElementById('mini-1'); // Dashboard luôn là mini-1
        var initialNav = document.getElementById('nav-1');
        if (initialMini && initialNav) {
            initialMini.classList.add('selected');
            initialNav.style.display = 'block';
            document.querySelector(".sidebarmenu").classList.add("active");

            // Active child menu dựa trên URL nếu không phải mini-nav click
            var urlParams = new URLSearchParams(window.location.search);
            var page = urlParams.get('page') || 'dashboard';
            var activeLink = document.querySelector(`.sidebar-nav a[href*="${page}"]`);
            if (activeLink) {
                document.querySelectorAll(".sidebar-nav").forEach(function (nav) {
                    nav.style.display = 'none';
                });
                activeLink.closest('.sidebar-nav').style.display = 'block';
                activeLink.classList.add('active');
                activeLink.closest('.sidebar-item')?.classList.add('selected');
                
                // Active mini-nav tương ứng
                var navId = activeLink.closest('.sidebar-nav').getAttribute('id');
                var miniNav = document.querySelector(`.mini-nav-item[data-parent-index="${navId === 'nav-1' ? '' : parseInt(navId.replace('nav-', '')) - 2}"]`);
                if (miniNav) {
                    document.querySelectorAll(".mini-nav-item").forEach(function (nav) {
                        nav.classList.remove("selected");
                    });
                    miniNav.classList.add('selected');
                }
            }
        }
    }
});
</script>

<style>
.sidebarmenu {
    display: none;
}
.sidebarmenu.active {
    display: block;
}
.sidebar-nav {
    display: none;
}
.mini-nav-item.selected {
    background-color: #f0f0f0;
}

</style>