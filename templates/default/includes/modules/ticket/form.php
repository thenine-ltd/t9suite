<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}
?>
<form id="ticket-form" method="POST">
    <?php wp_nonce_field('t9_ticket_save_action', 't9_ticket_nonce'); ?>
    <input type="hidden" name="post_type" value="t9_ticket">
    <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

    <div class="form-floating mb-3">
        <input type="text" name="ticket_title" id="ticket_title" class="form-control" value="<?php echo $title; ?>" placeholder="Ticket Title" required>
        <label for="ticket_title"><?php esc_html_e('Ticket Title', 't9admin-pro'); ?></label>
    </div>

    <div class="form-floating mb-3">
        <textarea name="ticket_content" id="ticket_content" class="form-control" placeholder="Description" style="height: 100px;"><?php echo esc_textarea($content); ?></textarea>
        <label for="ticket_content"><?php esc_html_e('Description', 't9admin-pro'); ?></label>
    </div>

    <div class="form-floating mb-3">
        <select name="ticket_status" id="ticket_status" class="form-select">
            <option value="open" <?php selected($status, 'open'); ?>><?php esc_html_e('Open', 't9admin-pro'); ?></option>
            <option value="pending" <?php selected($status, 'pending'); ?>><?php esc_html_e('Pending', 't9admin-pro'); ?></option>
            <option value="done" <?php selected($status, 'done'); ?>><?php esc_html_e('Done', 't9admin-pro'); ?></option>
        </select>
        <label for="ticket_status"><?php esc_html_e('Status', 't9admin-pro'); ?></label>
    </div>

    <div class="form-floating mb-3">
        <select name="ticket_assignee" id="ticket_assignee" class="form-select">
            <option value=""><?php esc_html_e('None', 't9admin-pro'); ?></option>
            <?php foreach ($staffs as $staff) : ?>
                <option value="<?php echo esc_attr($staff->ID); ?>" <?php selected($assignee, $staff->ID); ?>>
                    <?php echo esc_html($staff->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="ticket_assignee"><?php esc_html_e('Assignee', 't9admin-pro'); ?></label>
    </div>

    <div class="form-floating mb-3">
        <select name="ticket_priority" id="ticket_priority" class="form-select">
            <option value="high" <?php selected($priority, 'high'); ?>><?php esc_html_e('High', 't9admin-pro'); ?></option>
            <option value="normal" <?php selected($priority, 'normal'); ?>><?php esc_html_e('Normal', 't9admin-pro'); ?></option>
            <option value="low" <?php selected($priority, 'low'); ?>><?php esc_html_e('Low', 't9admin-pro'); ?></option>
        </select>
        <label for="ticket_priority"><?php esc_html_e('Priority', 't9admin-pro'); ?></label>
    </div>

    <div class="form-floating mb-3">
        <select name="ticket_customer" id="ticket_customer" class="form-select">
            <option value=""><?php esc_html_e('None', 't9admin-pro'); ?></option>
            <?php foreach ($customers as $customer) : ?>
                <option value="<?php echo esc_attr($customer->ID); ?>" <?php selected($customer, $customer->ID); ?>>
                    <?php echo esc_html($customer->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="ticket_customer"><?php esc_html_e('Customer', 't9admin-pro'); ?></label>
    </div>

    <div class="form-floating mb-3">
        <select name="ticket_site" id="ticket_site" class="form-select">
            <option value=""><?php esc_html_e('None', 't9admin-pro'); ?></option>
            <?php foreach ($sites as $site) : ?>
                <option value="<?php echo esc_attr($site->ID); ?>" <?php selected($site, $site->ID); ?>>
                    <?php echo esc_html($site->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="ticket_site"><?php esc_html_e('Site', 't9admin-pro'); ?></label>
    </div>

    <button type="submit" class="btn btn-primary w-100"><?php esc_html_e('Save', 't9admin-pro'); ?></button>
</form>