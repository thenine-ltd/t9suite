<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-taxonomy-handler.php';
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-tags-handler.php';
require_once T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/class-t9admin-pro-upload-handler.php';
require_once T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/Ticket/TicketForm.php'; 
require_once T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/Hrm/StaffsForm.php'; 
require_once T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/Crm/CustomersForm.php'; 
require_once T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/Epatrol/SitesForm.php'; 
require_once T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/Epatrol/CheckpointsForm.php'; 
require_once T9ADMIN_PRO_PLUGIN_DIR . 'includes/Modules/Epatrol/ToursForm.php'; 

class T9AdminProPostsFormHandler {

    private $post_type;
    private $taxonomy_handler;
    private $tags_handler;
    private $upload_handler;

    public function __construct($post_type = 'post') {
        $this->post_type = post_type_exists($post_type) ? sanitize_text_field($post_type) : 'post';
        $this->taxonomy_handler = new T9AdminProTaxonomyHandler($this->post_type);
        $this->tags_handler = new T9AdminProTagsHandler($this->post_type);
        $this->upload_handler = new T9AdminProUploadHandler();
    }

    /**
     * Render the form
     */
    public function t9admin_pro_render_form() {
    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
    $post = $post_id ? get_post($post_id) : null;

    $post_type = get_post_type($post_id);
    $hide_fields = ($post_type !== 'page'); 
    $is_staffs = ($post_type === 'staffs'); 

    $post_title = $post ? esc_attr($post->post_title) : '';
    $post_excerpt = $post ? esc_textarea($post->post_excerpt) : '';
    if ($post_type === 'customers') {
        $customers_form = new T9AdminProCustomersForm($post_id);
        $customers_form->render_form();
        return;
    }
    
    if ($post_type === 'sites') {
        $sites_form = new T9AdminProSitesForm($post_id);
        $sites_form->render_form();
        return;
    }
    
   if ($post_type === 'staffs') {
        $staffs_form = new T9AdminProStaffsForm($post_id);
        $staffs_form->render_form();
        return;
    }
   
   if ($post_type === 'checkpoints') {
        $staffs_form = new T9AdminProCheckpointsForm($post_id);
        $staffs_form->render_form();
        return;
    }    
    
    if ($post_type === 'tours') {
        $tours_form = new T9AdminProToursForm($post_id);
        $tours_form->render_form();
        return;
    }
    
    if ($post_type === 't9_ticket') {
        $ticket_form = new T9AdminProTicketForm($post_id);
        $ticket_form->render_form();
        return;
    }    

    ?>
    <form id="create-post-form" method="POST" enctype="multipart/form-data">
        <?php wp_nonce_field('t9_create_post_action', 't9_create_post_nonce'); ?>
        <input type="hidden" name="post_type" value="<?php echo esc_attr($this->post_type); ?>">
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

        <div class="row">
            <div class="col-md-8">
                <?php if (!$hide_fields) : // Chỉ hiển thị card này nếu là "page" ?>
                <div class="card">
                    <div class="card-body">
                        <!-- Post Title -->
                        <div class="form-floating mb-3">
                            <input type="text" name="post_title" class="form-control" id="post-title" 
                                placeholder="<?php esc_attr_e('Please input title', 't9admin-pro'); ?>" 
                                value="<?php echo $post_title; ?>">
                            <label for="post-title"><?php esc_html_e('Title', 't9admin-pro'); ?></label>
                        </div>

                        <!-- Post Excerpt -->
                        <div class="form-floating mb-3">
                            <textarea name="post_excerpt" class="form-control" id="post-excerpt"
                                placeholder="<?php esc_attr_e('Please input excerpt', 't9admin-pro'); ?>">
                                <?php echo wp_kses_post($post_excerpt); ?>
                            </textarea>
                            <label for="post-excerpt"><?php esc_html_e('Excerpt', 't9admin-pro'); ?></label>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php $this->render_acf_fields($post_id, $this->post_type); ?>
            </div>

            <div class="col-md-4">
                <?php if ($is_staffs) : // Nếu là staffs, hiển thị Login Information ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php esc_html_e('Login Information', 't9admin-pro'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="form-floating mb-3">
                            <input type="text" name="staff_username" class="form-control" id="staff-username" 
                                placeholder="<?php esc_attr_e('Username', 't9admin-pro'); ?>">
                            <label for="staff-username"><?php esc_html_e('Username', 't9admin-pro'); ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="staff_password" class="form-control" id="staff-password" 
                                placeholder="<?php esc_attr_e('Password', 't9admin-pro'); ?>">
                            <label for="staff-password"><?php esc_html_e('Password', 't9admin-pro'); ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="staff_confirm_password" class="form-control" id="staff-confirm-password" 
                                placeholder="<?php esc_attr_e('Confirm Password', 't9admin-pro'); ?>">
                            <label for="staff-confirm-password"><?php esc_html_e('Confirm Password', 't9admin-pro'); ?></label>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary w-100"><?php esc_html_e('Save aaa', 't9admin-pro'); ?></button>
            </div>
        </div>
    </form>
    <?php
}




private function render_acf_fields($post_id, $post_type) {
        $field_groups = function_exists('acf_get_field_groups') ? acf_get_field_groups(['post_type' => $post_type]) : [];

        // if (empty($field_groups)) {
        //     echo '<div class="alert alert-warning">No ACF fields available for this post type.</div>';
        //     return;
        // }

        foreach ($field_groups as $group) {
            echo '<div class="card mb-3">';
            echo '<div class="card-header">';
            echo '<h5 class="card-title">' . esc_html($group['title']) . '</h5>';
            if (!empty($group['description'])) {
                echo '<h6 class="card-subtitle text-muted">' . esc_html($group['description']) . '</h6>';
            }
            echo '</div>';
            echo '<div class="card-body">';

            $fields = acf_get_fields($group['key']);
            foreach ($fields as $field) {
                $this->render_acf_field($field, $post_id);
            }

            echo '</div></div>'; // Close card-body and card
        }
    }

    /**
     * Render individual ACF field based on type
     */
    /**
 * Render individual ACF field based on type
 */
private function render_acf_field($field, $post_id) {
    $field_value = get_field($field['name'], $post_id) ?? '';
    $field_label = $field['label'] ?? ''; // Nếu null thì để chuỗi rỗng
    $field_type = $field['type'] ?? 'text';
    $field_name = esc_attr($field['name'] ?? '');
    $field_placeholder = esc_attr($field_label);

    echo '<div class="form-floating mb-3">';

    switch ($field_type) {
        case 'textarea':
            echo '<textarea class="form-control tinymce-enabled" name="acf[' . $field_name . ']" id="' . $field_name . '">'
                . esc_textarea($field_value ?? '') . '</textarea>';
            break;

        // New case to handle conditional dropdown
        case 'conditional_dropdown':
            echo '<select class="form-select" name="acf[' . $field_name . ']" id="' . $field_name . '" onchange="handleDropdownChange(this)">';
            // Populate options based on the first dropdown selection
            // You can customize this part to suit your needs
            echo '<option value="option1">Option 1</option>';
            echo '<option value="option2">Option 2</option>';
            echo '</select>';
            echo '<select class="form-select" name="acf[conditional_dropdown]" id="conditional_dropdown" style="display: none;">';
            echo '<option value="suboption1">Sub Option 1</option>';
            echo '<option value="suboption2">Sub Option 2</option>';
            echo '</select>';
            break;    

        case 'email':
            echo '<input type="email" class="form-control" name="acf[' . $field_name . ']" id="' . $field_name . '" value="' . esc_attr($field_value ?? '') . '" placeholder="' . $field_placeholder . '">';
            break;
        case 'password':
            echo '<input type="password" class="form-control" name="acf[' . $field_name . ']" id="' . $field_name . '" value="' . esc_attr($field_value ?? '') . '" placeholder="' . $field_placeholder . '">';
            break;
        case 'url':
            echo '<input type="url" class="form-control" name="acf[' . $field_name . ']" id="' . $field_name . '" value="' . esc_attr($field_value ?? '') . '" placeholder="' . $field_placeholder . '">';
            break;
        case 'number':
            echo '<input type="number" class="form-control" name="acf[' . $field_name . ']" id="' . $field_name . '" value="' . esc_attr($field_value ?? '') . '" placeholder="' . $field_placeholder . '">';
            break;
        case 'date_picker':
            echo '<input type="date" class="form-control" name="acf[' . $field_name . ']" id="' . $field_name . '" value="' . esc_attr($field_value ?? '') . '" placeholder="' . $field_placeholder . '">';
            break;

        case 'image': // Nếu là image thì dùng Dropzone
            echo '<div class="dropzone" id="dropzone-' . $field_name . '" data-field="' . esc_attr($field_name) . '"></div>';
            if (!empty($field_value)) {
                echo '<img src="' . esc_url($field_value) . '" class="mt-2 img-thumbnail" style="max-width: 150px;">';
            }
            break;
        default:
            echo '<input type="' . esc_attr($field_type) . '" class="form-control" name="acf[' . $field_name . ']" id="' . $field_name . '" value="' . esc_attr($field_value ?? '') . '" placeholder="' . $field_placeholder . '">';
            break;
    }

    echo '<label for="' . $field_name . '">' . esc_html($field_label) . '</label>';
    echo '</div>'; // End form-floating
}




    /**
     * Handle form submission
     */
    public function t9admin_pro_handle_form_submission() {
    
        $post_type = sanitize_text_field($_POST['post_type']);
    
        if ($post_type === 'customers') {
            $customers_form = new T9AdminProCustomersForm();
            $customers_form->handle_form_submission();
            return;
        }
        
        if ($post_type === 'sites') {
            $sites_form = new T9AdminProSitesForm();
            $sites_form->handle_form_submission();
            return;
        }
        
        if ($post_type === 'staffs') {
            $staffs_form = new T9AdminProStaffsForm();
            $staffs_form->handle_form_submission();
            return;
        }     
        
        if ($post_type === 'checkpoints') {
            $staffs_form = new T9AdminProCheckpointsForm();
            $staffs_form->handle_form_submission();
            return;
        }     
        
        if ($post_type === 'tours') {
            $tours_form = new T9AdminProToursForm();
            $tours_form->handle_form_submission();
            return;
        }
        
        if ($post_type === 't9_ticket') {
            $ticket_form = new T9AdminProTicketForm();
            $ticket_form->handle_form_submission();
            return;
        }
    
    }

    
}
