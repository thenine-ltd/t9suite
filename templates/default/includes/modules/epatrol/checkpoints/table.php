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
                <th><?php esc_html_e('Checkpoint Name', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Label', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Longitude', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Latitude', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Status', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Created Date', 't9admin-pro'); ?></th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                $post_id = get_the_ID();
                $status = get_post_meta($post_id, 'status', true);
                $label = get_post_meta($post_id, 'label', true);
                $longitude = get_post_meta($post_id, 'longitude', true);
                $latitude = get_post_meta($post_id, 'latitude', true);
                ?>
                <tr>
                    <td><input type="checkbox" class="t9admin_pro_bulk_checkbox" name="post_ids[]" value="<?php echo esc_attr($post_id); ?>"></td>
                    <td>
                        <a href="<?php echo esc_url(home_url("t9admin/post-type-create/?post_type=checkpoints&post_id=" . $post_id)); ?>">
                            <?php echo esc_html(get_the_title()); ?>
                        </a>
                        <div class="t9admin_pro_post_tools" style="display: none;">
                            <a href="#" class="t9admin_pro_edit"><?php esc_html_e('Edit', 't9admin-pro'); ?></a> |
                            <a href="#" class="t9admin_pro_delete"><?php esc_html_e('Delete', 't9admin-pro'); ?></a>
                        </div>
                    </td>
                    <td><?php echo esc_html($label ?: '—'); ?></td>
                    <td><?php echo esc_html($longitude ?: '—'); ?></td>
                    <td><?php echo esc_html($latitude ?: '—'); ?></td>
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