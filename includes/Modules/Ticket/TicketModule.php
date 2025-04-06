<?php
namespace T9AdminPro\Modules\Ticket;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ticket Module for T9Admin Pro.
 * Manages ticket creation and details with custom fields.
 */
class TicketModule {

    /**
     * Constructor to initialize hooks for the Ticket module.
     */
    public function __construct() {
        add_action('init', [$this, 'register_ticket_post_type']);
        add_action('add_meta_boxes', [$this, 'add_ticket_meta_boxes']);
        add_action('save_post_t9_ticket', [$this, 'save_ticket_meta_data']);
        add_filter('manage_t9_ticket_posts_columns', [$this, 'customize_ticket_columns']);
        add_action('manage_t9_ticket_posts_custom_column', [$this, 'render_ticket_column_data'], 10, 2);
    }

    /**
     * Register the Ticket custom post type.
     */
    public function register_ticket_post_type() {
        $labels = [
            'name'               => __('Tickets', 't9admin-pro'),
            'singular_name'      => __('Ticket', 't9admin-pro'),
            'add_new'            => __('Add New Ticket', 't9admin-pro'),
            'add_new_item'       => __('Add New Ticket', 't9admin-pro'),
            'edit_item'          => __('Edit Ticket', 't9admin-pro'),
            'new_item'           => __('New Ticket', 't9admin-pro'),
            'view_item'          => __('View Ticket', 't9admin-pro'),
            'search_items'       => __('Search Tickets', 't9admin-pro'),
            'not_found'          => __('No tickets found', 't9admin-pro'),
            'not_found_in_trash' => __('No tickets found in Trash', 't9admin-pro'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'supports'           => ['title', 'editor', 'author', 'comments'],
            'menu_icon'          => 'dashicons-tickets-alt',
            'rewrite'            => ['slug' => 'ticket'],
            'menu_position'      => 23,
            'has_archive'        => true,
        ];

        register_post_type('t9_ticket', $args);
    }

    /**
     * Add meta boxes for ticket details.
     */
    public function add_ticket_meta_boxes() {
        add_meta_box(
            't9_ticket_details',
            __('Ticket Details', 't9admin-pro'),
            [$this, 'render_ticket_meta_box'],
            't9_ticket',
            'side',
            'high'
        );
    }

    /**
     * Render the ticket meta box content.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_ticket_meta_box($post) {
        wp_nonce_field('t9_ticket_meta_nonce', 't9_ticket_nonce');

        $status    = get_post_meta($post->ID, '_t9_ticket_status', true);
        $assignee  = get_post_meta($post->ID, '_t9_ticket_assignee', true);
        $priority  = get_post_meta($post->ID, '_t9_ticket_priority', true);
        $customer  = get_post_meta($post->ID, '_t9_ticket_customer', true);
        $site      = get_post_meta($post->ID, '_t9_ticket_site', true);

        // Lấy danh sách Staffs, Customers, Sites từ CPT
        $staffs    = get_posts(['post_type' => 't9_staff', 'numberposts' => -1, 'post_status' => 'publish']);
        $customers = get_posts(['post_type' => 't9_customer', 'numberposts' => -1, 'post_status' => 'publish']);
        $sites     = get_posts(['post_type' => 't9_site', 'numberposts' => -1, 'post_status' => 'publish']);
        ?>
        <p>
            <label for="t9_ticket_status"><?php esc_html_e('Status', 't9admin-pro'); ?></label><br>
            <select name="t9_ticket_status" id="t9_ticket_status">
                <option value="open" <?php selected($status, 'open'); ?>><?php esc_html_e('Open', 't9admin-pro'); ?></option>
                <option value="pending" <?php selected($status, 'pending'); ?>><?php esc_html_e('Pending', 't9admin-pro'); ?></option>
                <option value="done" <?php selected($status, 'done'); ?>><?php esc_html_e('Done', 't9admin-pro'); ?></option>
            </select>
        </p>
        <p>
            <label for="t9_ticket_assignee"><?php esc_html_e('Assignee', 't9admin-pro'); ?></label><br>
            <select name="t9_ticket_assignee" id="t9_ticket_assignee">
                <option value=""><?php esc_html_e('None', 't9admin-pro'); ?></option>
                <?php foreach ($staffs as $staff) : ?>
                    <option value="<?php echo esc_attr($staff->ID); ?>" <?php selected($assignee, $staff->ID); ?>>
                        <?php echo esc_html($staff->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="t9_ticket_priority"><?php esc_html_e('Priority', 't9admin-pro'); ?></label><br>
            <select name="t9_ticket_priority" id="t9_ticket_priority">
                <option value="high" <?php selected($priority, 'high'); ?>><?php esc_html_e('High', 't9admin-pro'); ?></option>
                <option value="normal" <?php selected($priority, 'normal'); ?>><?php esc_html_e('Normal', 't9admin-pro'); ?></option>
                <option value="low" <?php selected($priority, 'low'); ?>><?php esc_html_e('Low', 't9admin-pro'); ?></option>
            </select>
        </p>
        <p>
            <label for="t9_ticket_customer"><?php esc_html_e('Customer', 't9admin-pro'); ?></label><br>
            <select name="t9_ticket_customer" id="t9_ticket_customer">
                <option value=""><?php esc_html_e('None', 't9admin-pro'); ?></option>
                <?php foreach ($customers as $customer) : ?>
                    <option value="<?php echo esc_attr($customer->ID); ?>" <?php selected($customer, $customer->ID); ?>>
                        <?php echo esc_html($customer->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="t9_ticket_site"><?php esc_html_e('Site', 't9admin-pro'); ?></label><br>
            <select name="t9_ticket_site" id="t9_ticket_site">
                <option value=""><?php esc_html_e('None', 't9admin-pro'); ?></option>
                <?php foreach ($sites as $site) : ?>
                    <option value="<?php echo esc_attr($site->ID); ?>" <?php selected($site, $site->ID); ?>>
                        <?php echo esc_html($site->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    /**
     * Save ticket meta data when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_ticket_meta_data($post_id) {
        if (!isset($_POST['t9_ticket_nonce']) || !wp_verify_nonce($_POST['t9_ticket_nonce'], 't9_ticket_meta_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $status    = isset($_POST['t9_ticket_status']) ? sanitize_text_field($_POST['t9_ticket_status']) : 'open';
        $assignee  = isset($_POST['t9_ticket_assignee']) ? absint($_POST['t9_ticket_assignee']) : '';
        $priority  = isset($_POST['t9_ticket_priority']) ? sanitize_text_field($_POST['t9_ticket_priority']) : 'normal';
        $customer  = isset($_POST['t9_ticket_customer']) ? absint($_POST['t9_ticket_customer']) : '';
        $site      = isset($_POST['t9_ticket_site']) ? absint($_POST['t9_ticket_site']) : '';

        update_post_meta($post_id, '_t9_ticket_status', $status);
        update_post_meta($post_id, '_t9_ticket_assignee', $assignee);
        update_post_meta($post_id, '_t9_ticket_priority', $priority);
        update_post_meta($post_id, '_t9_ticket_customer', $customer);
        update_post_meta($post_id, '_t9_ticket_site', $site);
    }

    /**
     * Customize the columns in the ticket admin list table.
     *
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function customize_ticket_columns($columns) {
        $columns['ticket_status']   = __('Status', 't9admin-pro');
        $columns['ticket_assignee'] = __('Assignee', 't9admin-pro');
        $columns['ticket_priority'] = __('Priority', 't9admin-pro');
        $columns['ticket_customer'] = __('Customer', 't9admin-pro');
        $columns['ticket_site']     = __('Site', 't9admin-pro');
        return $columns;
    }

    /**
     * Render data for custom columns in the ticket admin list table.
     *
     * @param string $column_name The name of the column.
     * @param int    $post_id     The ID of the post.
     */
    public function render_ticket_column_data($column_name, $post_id) {
        switch ($column_name) {
            case 'ticket_status':
                $status = get_post_meta($post_id, '_t9_ticket_status', true);
                echo esc_html(ucfirst($status ?: 'Open'));
                break;
            case 'ticket_assignee':
                $assignee_id = get_post_meta($post_id, '_t9_ticket_assignee', true);
                $assignee = $assignee_id ? get_the_title($assignee_id) : __('None', 't9admin-pro');
                echo esc_html($assignee);
                break;
            case 'ticket_priority':
                $priority = get_post_meta($post_id, '_t9_ticket_priority', true);
                echo esc_html(ucfirst($priority ?: 'Normal'));
                break;
            case 'ticket_customer':
                $customer_id = get_post_meta($post_id, '_t9_ticket_customer', true);
                $customer = $customer_id ? get_the_title($customer_id) : __('None', 't9admin-pro');
                echo esc_html($customer);
                break;
            case 'ticket_site':
                $site_id = get_post_meta($post_id, '_t9_ticket_site', true);
                $site = $site_id ? get_the_title($site_id) : __('None', 't9admin-pro');
                echo esc_html($site);
                break;
        }
    }
}