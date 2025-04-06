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
                <th><?php esc_html_e('Site Name', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Representative', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Phone', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Email', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Customer', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Team Leader', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Patrol Staff', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Status', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Created Date', 't9admin-pro'); ?></th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                $post_id = get_the_ID();
                $status = get_post_meta($post_id, 'status', true);
                $representative = get_post_meta($post_id, 'representative', true);
                $phone = get_post_meta($post_id, 'phone', true);
                $email = get_post_meta($post_id, 'email', true);
                $related_customer = get_post_meta($post_id, 'related_customer', true);
                $customer = $related_customer ? get_post($related_customer) : null;
                $related_leader = get_post_meta($post_id, 'related_leader', true);
                $related_patrol_staff = get_post_meta($post_id, 'related_patrol_staff', true) ?: [];

                // Lấy avatar cho Team Leader
                $leader_avatar = $related_leader ? get_avatar_url($related_leader, ['size' => 40]) : 'https://via.placeholder.com/50';
                if ($related_leader) {
                    $leader_staff_post = get_posts([
                        'post_type' => 'staffs',
                        'meta_key' => 'user_id',
                        'meta_value' => $related_leader,
                        'numberposts' => 1,
                    ]);
                    if (!empty($leader_staff_post) && has_post_thumbnail($leader_staff_post[0]->ID)) {
                        $leader_avatar = get_the_post_thumbnail_url($leader_staff_post[0]->ID, 'thumbnail');
                    }
                }

                // Lấy avatar cho Patrol Staff
                $patrol_staff_avatars = [];
                foreach ((array) $related_patrol_staff as $staff_id) {
                    $staff_post = get_posts([
                        'post_type' => 'staffs',
                        'meta_key' => 'user_id',
                        'meta_value' => $staff_id,
                        'numberposts' => 1,
                    ]);
                    $avatar = !empty($staff_post) && has_post_thumbnail($staff_post[0]->ID) 
                        ? get_the_post_thumbnail_url($staff_post[0]->ID, 'thumbnail') 
                        : get_avatar_url($staff_id, ['size' => 40]);
                    $patrol_staff_avatars[] = $avatar;
                }
                ?>
                <tr>
                    <td><input type="checkbox" class="t9admin_pro_bulk_checkbox" name="post_ids[]" value="<?php echo esc_attr($post_id); ?>"></td>
                    <td>
                        <a href="<?php echo esc_url(home_url("t9admin/post-type-create/?post_type=sites&post_id=" . $post_id)); ?>">
                            <strong><?php echo esc_html(get_the_title()); ?></strong>
                        </a>
                        <div class="t9admin_pro_post_tools" style="display: none;">
                            <a href="#" class="t9admin_pro_edit"><?php esc_html_e('Edit', 't9admin-pro'); ?></a> |
                            <a href="#" class="t9admin_pro_delete"><?php esc_html_e('Delete', 't9admin-pro'); ?></a>
                        </div>
                    </td>
                    <td><?php echo esc_html($representative ?: '—'); ?></td>
                    <td><?php echo esc_html($phone ?: '—'); ?></td>
                    <td><?php echo esc_html($email ?: '—'); ?></td>
                    <td><?php echo $customer ? esc_html($customer->post_title) : '—'; ?></td>
                    <td>
                        <?php if ($related_leader): ?>
                            <img src="<?php echo esc_url($leader_avatar); ?>" alt="Team Leader" width="40" height="40" class="rounded-circle card-hover border border-2 border-white">
                        <?php else: ?>
                            <?php esc_html_e('—', 't9admin-pro'); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if (!empty($patrol_staff_avatars)):
                            $display_count = min(2, count($patrol_staff_avatars));
                            for ($i = 0; $i < $display_count; $i++): ?>
                                <img src="<?php echo esc_url($patrol_staff_avatars[$i]); ?>" alt="Patrol Staff" width="40" height="40" class="rounded-circle me-n2 card-hover border border-2 border-white">
                            <?php endfor; ?>
                            <?php if (count($patrol_staff_avatars) > 2): ?>
                                <span class="bg-info-subtle text-info" style="height: 40px; width: 40px; border-radius: 50%; display: inline-block; text-align: center; line-height: 40px;">+<?php echo count($patrol_staff_avatars) - 2; ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php esc_html_e('—', 't9admin-pro'); ?>
                        <?php endif; ?>
                    </td>
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