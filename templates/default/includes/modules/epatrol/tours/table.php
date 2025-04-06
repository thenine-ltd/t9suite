<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}
?>

<style>
    .t9admin_pro_add_checkpoint {
        display: none; /* Ẩn mặc định */
        cursor: pointer;
        position: absolute;
        top: 58px;
        border-radius: 50%;
        height: 24px;
        width: 24px;
        text-align: center;
        z-index: 99;   
        left: 19px;
    }
    tr:hover .t9admin_pro_add_checkpoint {
        display: block;
    }
</style>

<div class="table-responsive mb-4 border rounded-1">
    <table class="table table-hover w-100 m-0 display text-nowrap align-middle">
        <thead>
            <tr>
                <th><input type="checkbox" id="t9admin_pro_select_all"></th>
                <th><?php esc_html_e('Tour Name', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Start Date', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('End Date', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Customer', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Site', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Assignee', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Interval', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Frequency', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Progress', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Status', 't9admin-pro'); ?></th>
                <th><?php esc_html_e('Created Date', 't9admin-pro'); ?></th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                $post_id = get_the_ID();
                $tour_start_date = get_post_meta($post_id, 'tour_start_date', true);
                $tour_end_date = get_post_meta($post_id, 'tour_end_date', true);
                $customer_id = get_post_meta($post_id, 'customer', true);
                $customer = $customer_id ? get_post($customer_id) : null;
                $selecting_site = get_post_meta($post_id, 'selecting_site', true);
                $site = $selecting_site ? get_post($selecting_site) : null;
                $selecting_guard = get_post_meta($post_id, 'selecting_guard', true);
                $guard = $selecting_guard ? get_userdata($selecting_guard) : null;
                $interval = get_post_meta($post_id, 'interval', true);
                $frequency = get_post_meta($post_id, 'repeat', true);
                $status = get_post_status($post_id);
                $tour_rounds = get_post_meta($post_id, 'tour_rounds', true) ?: [];

                // Lấy avatar cho assignee
                $avatar = 'https://via.placeholder.com/40'; // Default avatar
                if ($selecting_guard) {
                    $staff_post = get_posts([
                        'post_type' => 'staffs',
                        'meta_key' => 'user_id',
                        'meta_value' => $selecting_guard,
                        'numberposts' => 1,
                    ]);
                    if (!empty($staff_post) && has_post_thumbnail($staff_post[0]->ID)) {
                        $avatar = get_the_post_thumbnail_url($staff_post[0]->ID, [40, 40]);
                    } else {
                        $avatar = get_avatar_url($selecting_guard, ['size' => 40]);
                    }
                }

                // Tính Progress
                $total_checkpoints = count($tour_rounds);
                $completed_checkpoints = 0;
                foreach ($tour_rounds as $round) {
                    if (isset($round['status']) && $round['status'] === 'completed') {
                        $completed_checkpoints++;
                    }
                }
                $progress = $total_checkpoints > 0 ? round(($completed_checkpoints / $total_checkpoints) * 100) : 0;
                ?>
                <tr data-toggle="collapse" data-target="#sub-row-<?php echo $post_id; ?>" class="accordion-toggle" style="position:relative;">
                    <td>
                        <input type="checkbox" class="t9admin_pro_bulk_checkbox" name="post_ids[]" value="<?php echo esc_attr($post_id); ?>">
                        <span class="t9admin_pro_add_checkpoint bg-primary-subtle text-primary" title="Add Checkpoint">+</span>
                    </td>
                    <td>
                        <a href="<?php echo esc_url(home_url("t9admin/post-type-create/?post_type=tours&post_id=" . $post_id)); ?>">
                            <strong><?php echo esc_html(get_the_title()); ?></strong>
                        </a>
                        <div class="t9admin_pro_post_tools" style="display: none;">
                            <a href="#" class="t9admin_pro_edit"><?php esc_html_e('Edit', 't9admin-pro'); ?></a> |
                            <a href="#" class="t9admin_pro_delete"><?php esc_html_e('Delete', 't9admin-pro'); ?></a>
                        </div>
                    </td>
                    <td><?php echo esc_html($tour_start_date ?: '—'); ?></td>
                    <td><?php echo esc_html($tour_end_date ?: '—'); ?></td>
                    <td><?php echo $customer ? esc_html($customer->post_title) : '—'; ?></td>
                    <td><?php echo $site ? esc_html($site->post_title) : '—'; ?></td>
                    <td>
                        <?php if ($guard): ?>
                            <img src="<?php echo esc_url($avatar); ?>" alt="Assignee" width="40" height="40" class="rounded-circle card-hover border border-2 border-white">
                        <?php else: ?>
                            <?php esc_html_e('—', 't9admin-pro'); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($interval ? $interval . ' mins' : '—'); ?></td>
                    <td><?php echo esc_html($frequency ? ucfirst($frequency) : '—'); ?></td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%;" 
                                aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $progress; ?>%
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($status === 'publish'): ?>
                            <span class="badge bg-success-subtle text-success"><?php esc_html_e('Active', 't9admin-pro'); ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger"><?php esc_html_e('Inactive', 't9admin-pro'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html(get_the_date('d-m-Y H:i:s')); ?></td>
                </tr>
                <!-- Sub-row listing checkpoints -->
                <tr>
                    <td colspan="12" class="p-0">
                        <div class="collapse" id="sub-row-<?php echo $post_id; ?>">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Checkpoint Name', 't9admin-pro'); ?></th>
                                        <th><?php esc_html_e('Status', 't9admin-pro'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tour_rounds)): ?>
                                        <?php foreach ($tour_rounds as $round): ?>
                                            <?php 
                                            $checkpoint_id = $round['checkpoint'] ?? '';
                                            $checkpoint = $checkpoint_id ? get_post($checkpoint_id) : null;
                                            $round_status = $round['status'] ?? 'processing';
                                            ?>
                                            <tr>
                                                <td><?php echo $checkpoint ? esc_html($checkpoint->post_title) : '—'; ?></td>
                                                <td>
                                                    <?php
                                                    switch ($round_status) {
                                                        case 'completed':
                                                            echo '<span class="badge bg-success-subtle text-success">' . esc_html__('Completed', 't9admin-pro') . '</span>';
                                                            break;
                                                        case 'processing':
                                                            echo '<span class="badge bg-info-subtle text-info">' . esc_html__('Processing', 't9admin-pro') . '</span>';
                                                            break;
                                                        case 'lated':
                                                            echo '<span class="badge bg-warning-subtle text-warning">' . esc_html__('Lated', 't9admin-pro') . '</span>';
                                                            break;
                                                        case 'inactive':
                                                            echo '<span class="badge bg-danger-subtle text-danger">' . esc_html__('Inactive', 't9admin-pro') . '</span>';
                                                            break;
                                                        default:
                                                            echo '—';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2"><?php esc_html_e('No checkpoints assigned.', 't9admin-pro'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thêm collapse toggle với Bootstrap
    jQuery('.accordion-toggle').on('click', function() {
        const target = jQuery(this).data('target');
        jQuery(target).collapse('toggle');
    });

    // Có thể thêm logic cho nút "+" nếu cần (ví dụ: mở form thêm checkpoint)
    jQuery('.t9admin_pro_add_checkpoint').on('click', function() {
        const tourId = jQuery(this).closest('tr').find('.t9admin_pro_bulk_checkbox').val();
        console.log('Add checkpoint for tour ID:', tourId);
        // Thêm logic xử lý tại đây nếu cần
    });
});
</script>