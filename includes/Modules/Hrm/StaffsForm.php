<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

/**
 * Staffs Form class for T9Admin Pro HRM Module.
 * Manages user data (password, email) and staff records (personal details, featured image).
 */
class T9AdminProStaffsForm {

    private $post_id;
    private $post;
    private $user_id;

    public function __construct($post_id = 0) {
        $this->post_id = absint($post_id);
        $this->post = $this->post_id ? get_post($this->post_id) : null;
        $this->user_id = get_current_user_id();
    }

    private function calculate_age($birth_date) {
        if (empty($birth_date)) {
            return '';
        }
        $birth = new DateTime($birth_date);
        $today = new DateTime();
        $age = $today->diff($birth)->y;
        return $age;
    }

    public function render_form() {
        $user = get_userdata($this->user_id);
        $avatar_url = $this->post_id && has_post_thumbnail($this->post_id) ? get_the_post_thumbnail_url($this->post_id, 'thumbnail') : get_avatar_url($this->user_id);
        $username = $user ? $user->user_login : '';
        $roles = wp_roles()->get_names();
        unset($roles['administrator']);
        $email = $this->post_id ? get_post_meta($this->post_id, 'email', true) : '';

        // Dữ liệu cho form với các field mới
        $data = [
            'post_id'          => $this->post_id,
            'avatar_url'       => $avatar_url,
            'username'         => $username,
            'email'            => $email,
            'full_name'        => $this->post ? esc_attr($this->post->post_title) : '',
            'status'           => get_post_meta($this->post_id, 'status', true),
            'identifier_code'  => get_post_meta($this->post_id, 'identifier_code', true),
            'birth_date'       => get_post_meta($this->post_id, 'birth_date', true),
            'sex'              => get_post_meta($this->post_id, 'sex', true),
            'nationality'      => get_post_meta($this->post_id, 'nationality', true),
            'job_position'     => get_post_meta($this->post_id, 'job_position', true),
            'workplace'        => get_post_meta($this->post_id, 'workplace', true),
            'city'             => get_post_meta($this->post_id, 'city', true),
            'district'         => get_post_meta($this->post_id, 'district', true),
            'address'          => get_post_meta($this->post_id, 'address', true),
            'phone'            => get_post_meta($this->post_id, 'phone', true),
            'bank_account'     => get_post_meta($this->post_id, 'bank_account', true),
            'name_of_account'  => get_post_meta($this->post_id, 'name_of_account', true),
            'bank_of_issue'    => get_post_meta($this->post_id, 'bank_of_issue', true),
            'personal_tax_code' => get_post_meta($this->post_id, 'personal_tax_code', true),
            'marital_status'   => get_post_meta($this->post_id, 'marital_status', true),
            'notes'            => get_post_meta($this->post_id, 'notes', true),
            'departments'      => get_terms(['taxonomy' => 'department', 'hide_empty' => false]),
            'selected_department' => wp_get_object_terms($this->post_id, 'department', ['fields' => 'ids']),
            'roles'            => $roles,
            'user_role'        => $user ? $user->roles[0] : '',
            'errors'           => []
        ];

        $template_path = T9ADMIN_PRO_PLUGIN_DIR . 'templates/default/includes/modules/hrm/form.php';
        if (file_exists($template_path)) {
            extract($data);
            include $template_path;
        } else {
            echo '<div class="alert alert-danger">' . esc_html__('HRM staff form template not found.', 't9admin-pro') . '</div>';
        }
    }

    public function handle_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['t9_staffs_nonce']) || !wp_verify_nonce($_POST['t9_staffs_nonce'], 't9_staffs_save_action')) {
            wp_die(__('Nonce verification failed.', 't9admin-pro'));
        }

        if (!current_user_can('edit_users')) {
            wp_die(__('You do not have permission to edit staff records.', 't9admin-pro'));
        }

        // Lấy post_id từ form thay vì từ constructor để chắc chắn
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $errors = [];

        // Lấy dữ liệu từ form, bao gồm các field mới
        $username = sanitize_user($_POST['staff_username'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $retype_password = $_POST['retype_password'] ?? '';
        $full_name = sanitize_text_field($_POST['full_name'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'active');
        $identifier_code = sanitize_text_field($_POST['identifier_code'] ?? '');
        $birth_date = sanitize_text_field($_POST['birth_date'] ?? '');
        $sex = sanitize_text_field($_POST['sex'] ?? '');
        $nationality = sanitize_text_field($_POST['nationality'] ?? '');
        $job_position = sanitize_text_field($_POST['job_position'] ?? '');
        $workplace = sanitize_text_field($_POST['workplace'] ?? '');
        $city = sanitize_text_field($_POST['city'] ?? '');
        $district = sanitize_text_field($_POST['district'] ?? '');
        $address = sanitize_text_field($_POST['address'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $bank_account = sanitize_text_field($_POST['bank_account'] ?? '');
        $name_of_account = sanitize_text_field($_POST['name_of_account'] ?? '');
        $bank_of_issue = sanitize_text_field($_POST['bank_of_issue'] ?? '');
        $personal_tax_code = sanitize_text_field($_POST['personal_tax_code'] ?? '');
        $marital_status = sanitize_text_field($_POST['marital_status'] ?? '');
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        $department = absint($_POST['department'] ?? 0);
        $role = sanitize_text_field($_POST['role'] ?? 'subscriber');

        if ($post_id === 0) {
            // Add mới: bắt buộc username, email, password
            if (empty($username)) {
                $errors['staff_username'] = __('Username is required for new staff.', 't9admin-pro');
            }
            if (empty($email)) {
                $errors['email'] = __('Email is required for new staff.', 't9admin-pro');
            }
            if (empty($new_password)) {
                $errors['new_password'] = __('New Password is required for new staff.', 't9admin-pro');
            }
            if (empty($retype_password)) {
                $errors['retype_password'] = __('Confirm New Password is required for new staff.', 't9admin-pro');
            } elseif ($new_password !== $retype_password) {
                $errors['retype_password'] = __('Passwords do not match.', 't9admin-pro');
            }

            if (!empty($errors)) {
                wp_die(__('Please fix the following errors: ', 't9admin-pro') . implode(', ', $errors));
            }

            // Tạo user mới
            $user_data = [
                'user_login' => $username,
                'user_email' => $email,
                'user_pass'  => $new_password,
                'display_name' => $full_name,
                'role'       => $role !== 'administrator' ? $role : 'subscriber',
            ];

            $user_id = wp_insert_user($user_data);
            if (is_wp_error($user_id)) {
                wp_die(__('Failed to create user: ', 't9admin-pro') . $user_id->get_error_message());
            }
        } else {
            // Edit: lấy user_id từ post_author
            $user_id = get_post_field('post_author', $post_id);
            if (!$user_id) {
                wp_die(__('Invalid user associated with this staff.', 't9admin-pro'));
            }

            // Chỉ kiểm tra password nếu có dữ liệu nhập vào
            if (!empty($new_password) || !empty($retype_password)) {
                if (empty($new_password)) {
                    $errors['new_password'] = __('New Password is required if Confirm New Password is entered.', 't9admin-pro');
                } elseif (empty($retype_password)) {
                    $errors['retype_password'] = __('Confirm New Password is required if New Password is entered.', 't9admin-pro');
                } elseif ($new_password !== $retype_password) {
                    $errors['retype_password'] = __('Passwords do not match.', 't9admin-pro');
                } else {
                    wp_set_password($new_password, $user_id);
                }

                if (!empty($errors)) {
                    wp_die(__('Please fix the following errors: ', 't9admin-pro') . implode(', ', $errors));
                }
            }

            // Cập nhật thông tin user
            wp_update_user([
                'ID' => $user_id,
                'user_email' => $email,
                'display_name' => $full_name,
            ]);

            if ($role !== 'administrator') {
                $user = new WP_User($user_id);
                $user->set_role($role);
            }
        }

        // Xử lý dữ liệu CPT Staffs
        $post_data = [
            'ID'          => $post_id,
            'post_type'   => 'staffs',
            'post_title'  => $full_name,
            'post_status' => 'publish',
            'post_author' => $user_id,
        ];

        $result = $post_id ? wp_update_post($post_data, true) : wp_insert_post($post_data, true);
        if (!is_wp_error($result)) {
            $post_id = $post_id ?: $result;

            if (!empty($_FILES['avatar']['name'])) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';

                $attachment_id = media_handle_upload('avatar', $post_id);
                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }

            if ($department) {
                wp_set_object_terms($post_id, [$department], 'department');
            } else {
                wp_set_object_terms($post_id, [], 'department');
            }

            // Lưu các field vào post_meta, bao gồm các field mới
            update_post_meta($post_id, 'status', $status);
            update_post_meta($post_id, 'identifier_code', $identifier_code);
            update_post_meta($post_id, 'birth_date', $birth_date);
            update_post_meta($post_id, 'sex', $sex);
            update_post_meta($post_id, 'nationality', $nationality);
            update_post_meta($post_id, 'job_position', $job_position);
            update_post_meta($post_id, 'workplace', $workplace);
            update_post_meta($post_id, 'city', $city);
            update_post_meta($post_id, 'district', $district);
            update_post_meta($post_id, 'address', $address);
            update_post_meta($post_id, 'phone', $phone);
            update_post_meta($post_id, 'bank_account', $bank_account);
            update_post_meta($post_id, 'name_of_account', $name_of_account);
            update_post_meta($post_id, 'bank_of_issue', $bank_of_issue);
            update_post_meta($post_id, 'personal_tax_code', $personal_tax_code);
            update_post_meta($post_id, 'marital_status', $marital_status);
            update_post_meta($post_id, 'notes', $notes);
            update_post_meta($post_id, 'user_id', $user_id);
            update_post_meta($post_id, 'email', $email);

            wp_safe_redirect(add_query_arg([
                'post_id'   => $post_id,
                'message'   => 'success'
            ], home_url('/t9admin/post-type-create/')));
            exit;
        } else {
            wp_die(__('Failed to create/update staff: ', 't9admin-pro') . ($result->get_error_message() ?? 'Unknown error'));
        }
    }
}

add_action('admin_post_t9_staffs_save', [new T9AdminProStaffsForm(), 'handle_form_submission']);