<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}
?>

<div class="table-responsive mb-4 border rounded-1">
    <table class="table table-hover w-100 m-0 display text-nowrap align-middle">
        <thead>
            <tr>
                <th><input type="checkbox" id="t9admin_pro_select_all"></th>
                <th><?php esc_html_e('Customer', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Representative', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Phone', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Email', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Status', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Created Date', 't9admin-pro'); ?></th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                $post_id = get_the_ID();
                $thumbnail = has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'thumbnail') : 'https://via.placeholder.com/50';
                $status = get_post_meta($post_id, 'status', true);
                $representative = get_post_meta($post_id, 'representative', true);
                $phone = get_post_meta($post_id, 'phone', true);
                $email = get_post_meta($post_id, 'email', true);
                ?>
                <tr>
                    <td><input type="checkbox" class="t9admin_pro_bulk_checkbox" name="post_ids[]" value="<?php echo esc_attr($post_id); ?>"></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="Logo" width="40" height="40" class="object-fit-contain me-2">
                            <div>
                                <a href="<?php echo esc_url(home_url("t9admin/post-type-create/?post_type=customers&post_id=" . $post_id)); ?>">
                                    <strong><?php echo esc_html(get_the_title()); ?></strong>
                                </a>
                                <div class="t9admin_pro_post_tools" style="display: none;">
                                    <a href="#" class="t9admin_pro_edit"><?php esc_html_e('Edit', 't9admin-pro'); ?></a> |
                                    <a href="#" class="t9admin_pro_delete"><?php esc_html_e('Delete', 't9admin-pro'); ?></a>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo esc_html($representative ?: '—'); ?></td>
                    <td><?php echo esc_html($phone ?: '—'); ?></td>
                    <td><?php echo esc_html($email ?: '—'); ?></td>
                    <td>
                        <?php if ($status === 'active'): ?>
                            <span class="badge bg-success-subtle text-success"><?php esc_html_e('Active', 't9admin-pro'); ?></span>
                        <?php elseif ($status === 'inactive'): ?>
                            <span class="badge bg-danger-subtle text-danger"><?php esc_html_e('Inactive', 't9admin-pro'); ?></span>
                        <?php else: ?>
                            <?php esc_html_e('—', 't9admin-pro'); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html(get_the_date('Y-m-d H:i:s')); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>