<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// WooCommerce Post Types
$woocommerce_post_types = [
    'shop_order' => __('Orders', 't9suite'),
    'shop_coupon' => __('Coupons', 't9suite'),
    'product' => __('Products', 't9suite'),
    'shop_customer' => __('Customers', 't9suite')
];

// Fetch all post types and filter custom post types
$post_types = get_post_types(['public' => true], 'objects');
$custom_post_types = array_filter($post_types, function ($post_type) use ($woocommerce_post_types) {
    return !array_key_exists($post_type->name, $woocommerce_post_types);
});

$menu_items = get_option('t9suite_menu_items', []); // Load saved menu order

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_items = isset($_POST['t9suite_menu_items']) ? $this->sanitize_menu_items($_POST['t9suite_menu_items']) : [];
    update_option('t9suite_menu_items', $menu_items);

    wp_cache_flush();
    if (class_exists('LiteSpeed_Cache_API')) {
        LiteSpeed_Cache_API::purge_all();
        LiteSpeed_Cache_API::purge_private();
    } else {
        do_action('litespeed_purge_all');
    }

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Menu saved successfully.', 't9suite') . '</p></div>';
    });
}
?>

<div class="wrap">
    <h2><?php esc_html_e('Menu Settings', 't9suite'); ?></h2>
    <form method="post" action="options.php">
        <div class="menu-settings-container">
            <!-- Left: Available Post Types -->
            <div style="flex: 1;">
                <h3><?php esc_html_e('Available Post Types', 't9suite'); ?></h3>
                <ul id="t9suite-available-items">
                    <li><strong><?php esc_html_e('WooCommerce', 't9suite'); ?></strong></li>
                    <?php foreach ($woocommerce_post_types as $post_type => $label): ?>
                        <?php if (!array_search($post_type, array_column($menu_items, 'post_type'))): ?>
                            <li class="available-item" data-post-type="<?php echo esc_attr($post_type); ?>" data-label="<?php echo esc_attr($label); ?>">
                                <span class="dashicons dashicons-plus"></span>
                                <?php echo esc_html($label); ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <li><strong><?php esc_html_e('Custom Post Types', 't9suite'); ?></strong></li>
                    <?php foreach ($custom_post_types as $post_type): ?>
                        <?php if (!array_search($post_type->name, array_column($menu_items, 'post_type'))): ?>
                            <li class="available-item" data-post-type="<?php echo esc_attr($post_type->name); ?>" data-label="<?php echo esc_attr($post_type->label); ?>">
                                <span class="dashicons dashicons-plus"></span>
                                <?php echo esc_html($post_type->label); ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Right: Current Menu -->
            <div style="flex: 2;">
                <h3><?php esc_html_e('Current Menu', 't9suite'); ?></h3>
                <?php
                settings_fields('t9suite_menu_settings');
                do_settings_sections('t9suite_menu_settings');
                ?>
                <div class="menu-right-container">
                    <ul id="t9suite-menu-list" class="sortable-menu-list">
                        <?php 
                        $current_parent_index = null;
                        foreach ($menu_items as $key => $menu_item): 
                            $is_child = $current_parent_index !== null && $menu_item['post_type'] !== 'mini-nav';
                            $indent_class = $is_child ? 'child-menu' : '';
                            if ($menu_item['post_type'] === 'mini-nav') {
                                $current_parent_index = $key;
                            }
                        ?>
                            <li class="menu-item <?php echo esc_attr($indent_class); ?>" data-post-type="<?php echo esc_attr($menu_item['post_type']); ?>" data-parent-index="<?php echo $is_child ? esc_attr($current_parent_index) : ''; ?>">
                                <span class="dashicons dashicons-menu"></span>
                                <?php if ($menu_item['post_type'] === 'hr'): ?>
                                    <span class="menu-label" style="width:100%;">---------------</span>
                                <?php elseif ($menu_item['post_type'] === 'label'): ?>
                                    <input type="text" name="t9suite_menu_items[<?php echo esc_attr($key); ?>][label]" 
                                        value="<?php echo esc_attr($menu_item['label']); ?>" 
                                        placeholder="<?php esc_attr_e('Enter Label', 't9suite'); ?>" 
                                        class="menu-label-input">
                                <?php elseif ($menu_item['post_type'] === 'mini-nav'): ?>
                                    <select name="t9suite_menu_items[<?php echo esc_attr($key); ?>][icon]" class="menu-icon-select">
                                        <option value=""><?php esc_html_e('Select Icon', 't9suite'); ?></option>
                                        <?php foreach ($this->get_bootstrap_icons() as $icon): ?>
                                            <option value="<?php echo esc_attr($icon); ?>" <?php selected($menu_item['icon'], $icon); ?>>
                                                <?php echo esc_html($icon); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="t9suite_menu_items[<?php echo esc_attr($key); ?>][label]" 
                                        value="<?php echo esc_attr($menu_item['label']); ?>" 
                                        placeholder="<?php esc_attr_e('Enter Mini Nav Label', 't9suite'); ?>" 
                                        class="menu-label-input">
                                <?php else: ?>
                                    <select name="t9suite_menu_items[<?php echo esc_attr($key); ?>][icon]" class="menu-icon-select">
                                        <option value=""><?php esc_html_e('Select Icon', 't9suite'); ?></option>
                                        <?php foreach ($this->get_bootstrap_icons() as $icon): ?>
                                            <option value="<?php echo esc_attr($icon); ?>" <?php selected($menu_item['icon'], $icon); ?>>
                                                <?php echo esc_html($icon); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="menu-label"><?php echo esc_html($menu_item['label']); ?></span>
                                <?php endif; ?>
                                <button type="button" class="button remove-menu-item" data-post-type="<?php echo esc_attr($menu_item['post_type']); ?>" data-label="<?php echo esc_attr($menu_item['label']); ?>">
                                    <span class="dashicons dashicons-minus"></span>
                                </button>
                                <input type="hidden" name="t9suite_menu_items[<?php echo esc_attr($key); ?>][post_type]" value="<?php echo esc_attr($menu_item['post_type']); ?>">
                                <?php if ($menu_item['post_type'] !== 'label' && $menu_item['post_type'] !== 'mini-nav'): ?>
                                    <input type="hidden" name="t9suite_menu_items[<?php echo esc_attr($key); ?>][label]" value="<?php echo esc_attr($menu_item['label']); ?>">
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button type="button" id="add-label" class="button"><?php esc_html_e('Add Label', 't9suite'); ?></button>
                <button type="button" id="add-hr" class="button"><?php esc_html_e('Add Horizontal Line', 't9suite'); ?></button>
                <button type="button" id="add-mini-nav" class="button"><?php esc_html_e('Add Mini Nav', 't9suite'); ?></button>
            </div>
        </div>
        <?php submit_button(); ?>
    </form>
</div>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
jQuery(document).ready(function ($) {
    let menuIndex = $('#t9suite-menu-list .menu-item').length;

    // Thêm item từ Available Post Types
    $('#t9suite-available-items').on('click', '.available-item', function () {
        const postType = $(this).data('post-type');
        const label = $(this).data('label');

        if (!postType || !label) {
            console.error('Invalid post type or label.');
            return;
        }

        const newItem = `
            <li class="menu-item child-menu" data-post-type="${postType}" data-parent-index="${getLastMiniNavIndex()}">
                <span class="dashicons dashicons-menu"></span>
                <select name="t9suite_menu_items[${menuIndex}][icon]" class="menu-icon-select">
                    <option value=""><?php esc_html_e('Select Icon', 't9suite'); ?></option>
                    <?php foreach ($this->get_bootstrap_icons() as $icon): ?>
                        <option value="<?php echo esc_attr($icon); ?>">
                            <?php echo esc_html($icon); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="menu-label">${label}</span>
                <button type="button" class="button remove-menu-item" data-post-type="${postType}" data-label="${label}">
                    <span class="dashicons dashicons-minus"></span>
                </button>
                <input type="hidden" name="t9suite_menu_items[${menuIndex}][post_type]" value="${postType}">
                <input type="hidden" name="t9suite_menu_items[${menuIndex}][label]" value="${label}">
            </li>
        `;

        const lastMiniNav = $('#t9suite-menu-list .menu-item[data-post-type="mini-nav"]').last();
        if (lastMiniNav.length) {
            lastMiniNav.after(newItem);
        } else {
            $('#t9suite-menu-list').append(newItem);
        }
        menuIndex++;
        $(this).remove();
    });

    // Make the menu list sortable
    $('#t9suite-menu-list').sortable({
        placeholder: "ui-state-highlight",
        handle: ".dashicons-menu",
        stop: function () {
            let menuIndex = 0;
            let currentParentIndex = null;
            $('#t9suite-menu-list .menu-item').each(function () {
                $(this).find('input, select').each(function () {
                    let name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, `[${menuIndex}]`);
                        $(this).attr('name', name);
                    }
                });
                if ($(this).data('post-type') === 'mini-nav') {
                    currentParentIndex = menuIndex;
                    $(this).removeClass('child-menu');
                } else {
                    $(this).addClass('child-menu');
                    $(this).attr('data-parent-index', currentParentIndex !== null ? currentParentIndex : '');
                }
                menuIndex++;
            });
        }
    });

    // Thêm Label
    $('#add-label').on('click', function () {
        const newItem = `
            <li class="menu-item child-menu" data-post-type="label" data-parent-index="${getLastMiniNavIndex()}">
                <span class="dashicons dashicons-menu"></span>
                <input type="text" name="t9suite_menu_items[${menuIndex}][label]" 
                    placeholder="Enter label text" class="menu-label-input" 
                    value="">
                <button type="button" class="button remove-menu-item">
                    <span class="dashicons dashicons-minus"></span>
                </button>
                <input type="hidden" name="t9suite_menu_items[${menuIndex}][post_type]" value="label">
            </li>
        `;
        const lastMiniNav = $('#t9suite-menu-list .menu-item[data-post-type="mini-nav"]').last();
        if (lastMiniNav.length) {
            lastMiniNav.after(newItem);
        } else {
            $('#t9suite-menu-list').append(newItem);
        }
        menuIndex++;
    });

    // Thêm Horizontal Line
    $('#add-hr').on('click', function () {
        const newItem = `
            <li class="menu-item child-menu" data-post-type="hr" data-parent-index="${getLastMiniNavIndex()}">
                <span class="dashicons dashicons-menu"></span>
                <span class="menu-label">---------------</span>
                <button type="button" class="button remove-menu-item">
                    <span class="dashicons dashicons-minus"></span>
                </button>
                <input type="hidden" name="t9suite_menu_items[${menuIndex}][post_type]" value="hr">
                <input type="hidden" name="t9suite_menu_items[${menuIndex}][label]" value="---------------">
            </li>
        `;
        const lastMiniNav = $('#t9suite-menu-list .menu-item[data-post-type="mini-nav"]').last();
        if (lastMiniNav.length) {
            lastMiniNav.after(newItem);
        } else {
            $('#t9suite-menu-list').append(newItem);
        }
        menuIndex++;
    });

    // Thêm Mini Nav
    $('#add-mini-nav').on('click', function () {
        const newItem = `
            <li class="menu-item" data-post-type="mini-nav">
                <span class="dashicons dashicons-menu"></span>
                <select name="t9suite_menu_items[${menuIndex}][icon]" class="menu-icon-select">
                    <option value=""><?php esc_html_e('Select Icon', 't9suite'); ?></option>
                    <?php foreach ($this->get_bootstrap_icons() as $icon): ?>
                        <option value="<?php echo esc_attr($icon); ?>">
                            <?php echo esc_html($icon); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="t9suite_menu_items[${menuIndex}][label]" 
                    placeholder="Enter Mini Nav Label" class="menu-label-input" 
                    value="">
                <button type="button" class="button remove-menu-item">
                    <span class="dashicons dashicons-minus"></span>
                </button>
                <input type="hidden" name="t9suite_menu_items[${menuIndex}][post_type]" value="mini-nav">
            </li>
        `;
        $('#t9suite-menu-list').append(newItem);
        menuIndex++;
    });

    // Xóa menu item
    $('#t9suite-menu-list').on('click', '.remove-menu-item', function () {
        const menuItem = $(this).closest('.menu-item');
        const postType = menuItem.data('post-type');
        const label = menuItem.find('.menu-label').text() || menuItem.find('.menu-label-input').val();

        if (postType && label && postType !== 'hr' && postType !== 'label' && postType !== 'mini-nav') {
            const newAvailableItem = `
                <li class="available-item" data-post-type="${postType}" data-label="${label}">
                    <span class="dashicons dashicons-plus"></span>
                    ${label}
                </li>`;
            $('#t9suite-available-items').append(newAvailableItem);
        }

        menuItem.remove();
    });

    // Hàm lấy index của mini-nav cuối cùng
    function getLastMiniNavIndex() {
        const lastMiniNav = $('#t9suite-menu-list .menu-item[data-post-type="mini-nav"]').last();
        return lastMiniNav.length ? parseInt(lastMiniNav.find('input[name*="[post_type]"]').attr('name').match(/\d+/)[0]) : '';
    }
});
</script>

<style>
.menu-settings-container {
    display: flex;
    flex-direction: row;
    gap: 20px;
}

.menu-right-container {
    display: flex;
    flex-direction: row;
    gap: 20px;
}

#t9suite-menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
    flex: 2;
}

#t9suite-menu-list .menu-item {
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 5px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    cursor: grab;
}

#t9suite-menu-list .menu-item.child-menu {
    margin-left: 20px;
    background: #f9f9f9;
}

#t9suite-menu-list .menu-item .dashicons-menu {
    cursor: grab;
}

#t9suite-menu-list .menu-item .menu-icon-select {
    margin: 0 8px 0 16px;
}

#t9suite-menu-list .menu-item .remove-menu-item {
    border: none;
    background: none;
    color: #ff0000;
    border-radius: 0;
    padding: 10px 8px 0 0;
    margin-left: auto;
}

#t9suite-available-items {
    list-style: none;
    padding: 0;
}

#t9suite-available-items .available-item {
    padding: 10px;
    margin-bottom: 5px;
    background: #f7f7f7;
    border: 1px solid #ddd;
    border-radius: 5px;
    cursor: pointer;
}

#t9suite-available-items .available-item .dashicons-plus {
    margin-right: 10px;
}

.menu-item .menu-label-input {
    flex: 1;
    margin-right: 10px;
}

.ui-state-highlight {
    background-color: #f0f0f0;
    border: 1px dashed #ccc;
    height: 40px;
}
</style>