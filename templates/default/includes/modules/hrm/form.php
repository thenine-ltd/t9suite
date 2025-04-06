<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}
?>

<div class="card bg-sm-transparent">
    <div class="card-body no-p">
        <div class="tab-content" id="pills-tabContent">
            <form method="POST" enctype="multipart/form-data" id="staffs-form" novalidate>
                <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab" tabindex="0">
                    <div class="row">
                        <!-- Avatar Upload Section -->
                        <div class="col-lg-6">
                            <div class="card w-100 border position-relative overflow-hidden">
                                <div class="card-header text-center">
                                    <h4 class="card-title mb-0"><?php _e('Profile Picture', 't9admin-pro'); ?></h4>
                                </div>
                                <div class="card-body p-4">
                                    <div class="text-center position-relative">
                                        <img 
                                            src="<?php echo esc_url($avatar_url); ?>" 
                                            alt="<?php echo esc_attr($username); ?>" 
                                            class="img-fluid rounded-circle" 
                                            id="user-avatar" 
                                            width="120" height="120"
                                        >
                                        <div id="progress-circle" class="position-absolute w-100 h-100 top-0 start-0 d-flex align-items-center justify-content-center" style="visibility: hidden;">
                                            <div class="spinner-border text-light" role="status" style="width: 60px; height: 60px;">
                                                <span class="visually-hidden">Uploading...</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-center my-4 gap-6">
                                            <input type="file" name="avatar" id="upload-avatar" accept="image/*" style="display: none;">
                                            <button type="button" id="upload-avatar-btn" class="btn bg-primary-subtle text-primary"><?php _e('Change Avatar', 't9admin-pro'); ?></button>
                                        </div>
                                        <p class="mb-0"><?php _e('Allowed JPG, GIF, PNG. Max size 800KB.', 't9admin-pro'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Login Detail Section -->
                        <div class="col-lg-6">
                            <div class="card w-100 border position-relative overflow-hidden">
                                <div class="card-header">
                                    <h4 class="card-title mb-0"><?php _e('Login Detail', 't9admin-pro'); ?></h4>
                                </div>
                                <div class="card-body p-4">
                                    <div class="form-floating mb-3">
                                        <select name="status" id="status" class="form-select">
                                            <option value="active" <?php selected($status, 'active'); ?>><?php esc_html_e('Active', 't9admin-pro'); ?></option>
                                            <option value="inactive" <?php selected($status, 'inactive'); ?>><?php esc_html_e('Inactive', 't9admin-pro'); ?></option>
                                        </select>
                                        <label for="status"><?php esc_html_e('Status', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" name="staff_username" class="form-control" id="staff-username" 
                                            value="<?php echo esc_attr($post_id > 0 ? get_the_author_meta('user_login', get_post_field('post_author', $post_id)) : ''); ?>" 
                                            placeholder="<?php esc_attr_e('Username', 't9admin-pro'); ?>" 
                                            <?php if ($post_id > 0) : ?>required readonly<?php else : ?>required<?php endif; ?>>
                                        <label for="staff-username"><?php esc_html_e('Username', 't9admin-pro'); ?></label>
                                        <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                        <div class="invalid-feedback"><?php _e('Please enter a username.', 't9admin-pro'); ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="password" class="form-control" id="new-password" name="new_password" 
                                                    placeholder="<?php _e('New Password', 't9admin-pro'); ?>" 
                                                    <?php echo $post_id == 0 ? 'required' : ''; ?>>
                                                <label for="new-password"><?php _e('New Password', 't9admin-pro'); ?></label>
                                                <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                                <div class="invalid-feedback"><?php echo $post_id == 0 ? _e('Please enter a new password.', 't9admin-pro') : _e('Passwords do not match.', 't9admin-pro'); ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="password" class="form-control" id="retype-password" name="retype_password" 
                                                    placeholder="<?php _e('Confirm New Password', 't9admin-pro'); ?>" 
                                                    <?php echo $post_id == 0 ? 'required' : ''; ?>>
                                                <label for="retype-password"><?php _e('Confirm New Password', 't9admin-pro'); ?></label>
                                                <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                                <div class="invalid-feedback"><?php echo $post_id == 0 ? _e('Please confirm your new password.', 't9admin-pro') : _e('Passwords do not match.', 't9admin-pro'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information Section -->
                    <div class="card w-100 border position-relative overflow-hidden">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0"><?php _e('Personal Information', 't9admin-pro'); ?></h4>
                            <button type="button" class="btn bg-primary-subtle text-primary"><i class="bi bi-lightbulb me-2"></i> <?php _e('AI', 't9admin-pro'); ?></button>
                        </div>
                        <div class="card-body p-4">
                            <?php wp_nonce_field('t9_staffs_save_action', 't9_staffs_nonce'); ?>
                            <input type="hidden" name="post_type" value="staffs">
                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="full_name" id="full_name" class="form-control" 
                                            value="<?php echo esc_attr($full_name); ?>" placeholder="Full Name" required>
                                        <label for="full_name"><?php esc_html_e('Full Name', 't9admin-pro'); ?></label>
                                        <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                        <div class="invalid-feedback"><?php _e('Please enter full name.', 't9admin-pro'); ?></div>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="number" name="identifier_code" id="identifier_code" class="form-control" 
                                            value="<?php echo esc_attr($identifier_code); ?>" placeholder="Personal ID">
                                        <label for="identifier_code"><?php esc_html_e('Personal ID', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="date" name="birth_date" id="birth_date" class="form-control" 
                                                    value="<?php echo esc_attr($birth_date); ?>" placeholder="Birth Date">
                                                <label for="birth_date"><?php esc_html_e('Birth Date', 't9admin-pro'); ?></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" id="age" class="form-control" 
                                                    value="<?php echo $birth_date ? esc_attr($this->calculate_age($birth_date)) : ''; ?>" 
                                                    placeholder="Age" readonly>
                                                <label for="age"><?php esc_html_e('Age', 't9admin-pro'); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <select name="sex" id="sex" class="form-select">
                                            <option value="male" <?php selected($sex, 'male'); ?>><?php esc_html_e('Male', 't9admin-pro'); ?></option>
                                            <option value="female" <?php selected($sex, 'female'); ?>><?php esc_html_e('Female', 't9admin-pro'); ?></option>
                                        </select>
                                        <label for="sex"><?php esc_html_e('Sex', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <select name="nationality" id="nationality" class="form-select">
                                            <option value="vietnam" <?php selected($nationality, 'vietnam'); ?>><?php esc_html_e('Vietnam', 't9admin-pro'); ?></option>
                                            <option value="usa" <?php selected($nationality, 'usa'); ?>><?php esc_html_e('USA', 't9admin-pro'); ?></option>
                                            <option value="japan" <?php selected($nationality, 'japan'); ?>><?php esc_html_e('Japan', 't9admin-pro'); ?></option>
                                            <option value="other" <?php selected($nationality, 'other'); ?>><?php esc_html_e('Other', 't9admin-pro'); ?></option>
                                        </select>
                                        <label for="nationality"><?php esc_html_e('Nationality', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="city" id="city" class="form-control" 
                                            value="<?php echo esc_attr($city); ?>" placeholder="City">
                                        <label for="city"><?php esc_html_e('City', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" name="district" id="district" class="form-control" 
                                            value="<?php echo esc_attr($district); ?>" placeholder="District">
                                        <label for="district"><?php esc_html_e('District', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" name="address" id="address" class="form-control" 
                                            value="<?php echo esc_attr($address); ?>" placeholder="Address">
                                        <label for="address"><?php esc_html_e('Address', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" name="personal_tax_code" id="personal_tax_code" class="form-control" 
                                            value="<?php echo esc_attr($personal_tax_code); ?>" placeholder="Personal Tax Code">
                                        <label for="personal_tax_code"><?php esc_html_e('Personal Tax Code', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <select name="marital_status" id="marital_status" class="form-select">
                                            <option value="single" <?php selected($marital_status, 'single'); ?>><?php esc_html_e('Single', 't9admin-pro'); ?></option>
                                            <option value="married" <?php selected($marital_status, 'married'); ?>><?php esc_html_e('Married', 't9admin-pro'); ?></option>
                                        </select>
                                        <label for="marital_status"><?php esc_html_e('Marital Status', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Working Information Section -->
                    <div class="card w-100 border position-relative overflow-hidden mt-4">
                        <div class="card-header">
                            <h4 class="card-title mb-0"><?php _e('Working Information', 't9admin-pro'); ?></h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <select name="role" id="role" class="form-select">
                                            <option value=""><?php esc_html_e('-- Select Role --', 't9admin-pro'); ?></option>
                                            <?php
                                            $staff_user_id = $post_id ? get_post_field('post_author', $post_id) : 0;
                                            $staff_user = $staff_user_id ? get_userdata($staff_user_id) : null;
                                            $staff_user_role = $staff_user ? $staff_user->roles[0] : '';
                                            foreach ($roles as $role_key => $role_name) {
                                                if ($role_key !== 'administrator') {
                                                    echo '<option value="' . esc_attr($role_key) . '" ' . selected($staff_user_role, $role_key, false) . '>' . esc_html($role_name) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        <label for="role"><?php esc_html_e('Role', 't9admin-pro'); ?></label>
                                    </div>                                  
                                    <div class="input-group mb-3">
                                        <div class="form-floating flex-grow-1">
                                            <select name="department" id="department" class="form-select">
                                                <option value=""><?php esc_html_e('-- Select Department --', 't9admin-pro'); ?></option>
                                                <?php
                                                if (!empty($departments) && !is_wp_error($departments)) {
                                                    foreach ($departments as $term) {
                                                        echo '<option value="' . esc_attr($term->term_id) . '" ' . selected(in_array($term->term_id, $selected_department), true, false) . '>' . esc_html($term->name) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <label for="department"><?php esc_html_e('Department', 't9admin-pro'); ?></label>
                                        </div>
                                        <button type="button" class="btn bg-primary-subtle text-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">+</button>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" name="job_position" id="job_position" class="form-control" 
                                            value="<?php echo esc_attr($job_position); ?>" placeholder="Job Position">
                                        <label for="job_position"><?php esc_html_e('Job Position', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" name="workplace" id="workplace" class="form-control" 
                                            value="<?php echo esc_attr($workplace); ?>" placeholder="Workplace">
                                        <label for="workplace"><?php esc_html_e('Workplace', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="tel" name="phone" id="phone" class="form-control" 
                                            value="<?php echo esc_attr($phone); ?>" placeholder="Phone" required>
                                        <label for="phone"><?php esc_html_e('Phone', 't9admin-pro'); ?></label>
                                        <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                        <div class="invalid-feedback"><?php _e('Please enter a valid phone number.', 't9admin-pro'); ?></div>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="email" name="email" id="email" class="form-control" 
                                            value="<?php echo esc_attr($email); ?>" placeholder="Email" <?php if ($post_id > 0) : ?>required readonly<?php endif; ?>>
                                        <label for="email"><?php esc_html_e('Email', 't9admin-pro'); ?></label>
                                        <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                        <div class="invalid-feedback"><?php _e('Please enter a valid email.', 't9admin-pro'); ?></div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="bank_account" id="bank_account" class="form-control" 
                                            value="<?php echo esc_attr($bank_account); ?>" placeholder="Bank Account">
                                        <label for="bank_account"><?php esc_html_e('Bank Account', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" name="name_of_account" id="name_of_account" class="form-control" 
                                            value="<?php echo esc_attr($name_of_account); ?>" placeholder="Name of Account">
                                        <label for="name_of_account"><?php esc_html_e('Name of Account', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" name="bank_of_issue" id="bank_of_issue" class="form-control" 
                                            value="<?php echo esc_attr($bank_of_issue); ?>" placeholder="Bank of Issue">
                                        <label for="bank_of_issue"><?php esc_html_e('Bank of Issue', 't9admin-pro'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <textarea name="notes" id="notes" class="form-control" placeholder="Notes" style="height: 132px;"><?php echo esc_textarea($notes); ?></textarea>
                                        <label for="notes"><?php esc_html_e('Notes', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary"><?php _e('Save Changes', 't9admin-pro'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Box để thêm Department -->
            <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addDepartmentModalLabel"><?php esc_html_e('Add New Department', 't9admin-pro'); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="new-department-name" placeholder="Department/Group Name">
                                <label for="new-department-name"><?php esc_html_e('Department/Group Name', 't9admin-pro'); ?></label>
                                <div class="invalid-feedback"><?php _e('Please enter a department name.', 't9admin-pro'); ?></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e('Cancel', 't9admin-pro'); ?></button>
                            <button type="button" class="btn btn-primary" id="add-department-btn"><?php esc_html_e('Add', 't9admin-pro'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('staffs-form');
                const birthDateInput = document.getElementById('birth_date');
                const ageInput = document.getElementById('age');
                const uploadBtn = document.getElementById('upload-avatar-btn');
                const uploadInput = document.getElementById('upload-avatar');
                const newPasswordInput = document.getElementById('new-password');
                const retypePasswordInput = document.getElementById('retype-password');
                const departmentSelect = document.getElementById('department');
                const addDepartmentBtn = document.getElementById('add-department-btn');
                const newDepartmentInput = document.getElementById('new-department-name');

                function calculateAge(birthDate) {
                    const today = new Date();
                    const birth = new Date(birthDate);
                    let age = today.getFullYear() - birth.getFullYear();
                    const monthDiff = today.getMonth() - birth.getMonth();
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                        age--;
                    }
                    return age;
                }

                function updateAge() {
                    const birthDate = birthDateInput.value;
                    if (birthDate) {
                        const age = calculateAge(birthDate);
                        ageInput.value = age >= 0 ? age : '';
                    } else {
                        ageInput.value = '';
                    }
                }

                if (birthDateInput) {
                    birthDateInput.addEventListener('change', updateAge);
                    updateAge();
                }

                if (uploadBtn && uploadInput) {
                    uploadBtn.addEventListener('click', function() {
                        uploadInput.click();
                    });

                    uploadInput.addEventListener('change', function(event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                document.getElementById('user-avatar').src = e.target.result;
                                document.getElementById('progress-circle').style.visibility = 'visible';
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                // Xử lý thêm department
                if (addDepartmentBtn) {
                    addDepartmentBtn.addEventListener('click', function() {
                        const departmentName = newDepartmentInput.value.trim();
                        if (!departmentName) {
                            newDepartmentInput.classList.add('is-invalid');
                            return;
                        }

                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'action': 't9admin_pro_add_department',
                                'nonce': '<?php echo wp_create_nonce('t9admin_pro_add_department_nonce'); ?>',
                                'department_name': departmentName
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const newOption = new Option(data.data.name, data.data.term_id, true, true);
                                departmentSelect.add(newOption);
                                departmentSelect.value = data.data.term_id;
                                bootstrap.Modal.getInstance(document.getElementById('addDepartmentModal')).hide();
                                newDepartmentInput.value = '';
                                newDepartmentInput.classList.remove('is-invalid');
                            } else {
                                alert(data.data.message || '<?php esc_html_e('Failed to add department.', 't9admin-pro'); ?>');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('<?php esc_html_e('An error occurred while adding department.', 't9admin-pro'); ?>');
                        });
                    });
                }

                form.addEventListener('submit', function(event) {
                    let isValid = true;

                    ['staff-username', 'email', 'new-password', 'retype-password', 'full_name', 'phone'].forEach(id => {
                        const input = document.getElementById(id);
                        if (!input) return;

                        if (input.hasAttribute('required') && !input.value.trim()) {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        }
                    });

                    if (newPasswordInput && retypePasswordInput) {
                        const newPassword = newPasswordInput.value;
                        const retypePassword = retypePasswordInput.value;

                        if ((newPassword || retypePassword) && newPassword !== retypePassword) {
                            retypePasswordInput.classList.remove('is-valid');
                            retypePasswordInput.classList.add('is-invalid');
                            isValid = false;
                        } else if (newPassword && retypePassword) {
                            retypePasswordInput.classList.remove('is-invalid');
                            retypePasswordInput.classList.add('is-valid');
                        }
                    }

                    if (!isValid) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                });

                form.querySelectorAll('input, select').forEach(input => {
                    input.addEventListener('input', function() {
                        if (this.hasAttribute('required') && !this.value.trim()) {
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        }

                        if (this.id === 'new-password' || this.id === 'retype-password') {
                            const newPassword = newPasswordInput.value;
                            const retypePassword = retypePasswordInput.value;
                            if (newPassword && retypePassword && newPassword !== retypePassword) {
                                retypePasswordInput.classList.remove('is-valid');
                                retypePasswordInput.classList.add('is-invalid');
                            } else if (newPassword && retypePassword) {
                                retypePasswordInput.classList.remove('is-invalid');
                                retypePasswordInput.classList.add('is-valid');
                            }
                        }
                    });
                });
            });
            </script>