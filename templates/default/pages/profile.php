    <?php
    // Lấy thông tin người dùng đăng nhập
    $current_user = wp_get_current_user();
    $avatar_url = get_avatar_url($current_user->ID, ['size' => 150]);
    $username = $current_user->display_name;
    $email = $current_user->user_email;
    $role = implode(', ', $current_user->roles); // Lấy vai trò
    $first_name = $current_user->user_firstname;
    $last_name = $current_user->user_lastname;
    $phone = get_user_meta($current_user->ID, 'phone', true); // Meta phone
   
// Thông báo (nếu có)
if (!empty($_GET['status'])) {
    $status = sanitize_text_field($_GET['status']);
    if ($status === 'success') {
        echo '<div class="alert alert-success">Profile updated successfully!</div>';
    } elseif ($status === 'error') {
        echo '<div class="alert alert-danger">Failed to update profile. Please try again.</div>';
    }
}    
 ?>
<div class="card">
    <ul class="nav nav-pills user-profile-tab" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link position-relative rounded-0 active d-flex align-items-center justify-content-center bg-transparent fs-3 py-3" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-controls="pills-account" aria-selected="true">
                <i class="bi bi-person-circle me-2 fs-6"></i>
                <span class="d-none d-md-block">Account</span>
            </button>
        </li>
    </ul>
    
    <div class="card-body">
        <div class="tab-content" id="pills-tabContent">
            <!-- Tab Profile Information -->
            <form method="POST" enctype="multipart/form-data">
            <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab" tabindex="0">
                <div class="row">
                    <!-- Avatar Upload Section -->
                    <div class="col-lg-6">
                        <div class="card w-100 border position-relative overflow-hidden">
                            <div class="card-body p-4">
                                <h4 class="card-title"><?php _e('Change Profile Picture', 't9admin'); ?></h4>
                                <div class="text-center position-relative">
                                    <img 
                                        src="<?php echo esc_url($avatar_url); ?>" 
                                        alt="<?php echo esc_attr($username); ?>" 
                                        class="img-fluid rounded-circle" 
                                        id="user-avatar" 
                                        width="120" height="120"
                                    >
                                    
                                    <!-- Progress Circle -->
                                    <div id="progress-circle" class="position-absolute w-100 h-100 top-0 start-0 d-flex align-items-center justify-content-center" style="visibility: hidden;">
                                        <div class="spinner-border text-light" role="status" style="width: 60px; height: 60px;">
                                            <span class="visually-hidden">Uploading...</span>
                                        </div>
                                    </div>
                                
                                    <div class="d-flex align-items-center justify-content-center my-4 gap-6">
                                        <input type="file" id="upload-avatar" accept="image/*" style="display: none;">
                                        <button id="upload-avatar-btn" class="btn btn-primary"><?php _e('Change Avatar', 't9admin'); ?></button>
                                    </div>
                                    <p class="mb-0"><?php _e('Allowed JPG, GIF, PNG. Max size 800KB.', 't9admin'); ?></p>
                                </div>

                            </div>
                        </div>
                    </div>
<style>
    #progress-circle {
        background: rgba(0, 0, 0, 0.6);
        border-radius: 50%;
        z-index: 10;
    }

    #user-avatar {
        transition: opacity 0.3s ease;
    }
</style>

                    <!-- Change Password Section -->
                    <div class="col-lg-6">
                        <div class="card w-100 border position-relative overflow-hidden">
                            <div class="card-body p-4">
                                <h4 class="card-title"><?php _e('Change Password', 't9admin'); ?></h4>
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" id="current-password" placeholder="<?php _e('Current Password', 't9admin'); ?>">
                                        <label for="current-password"><?php _e('Current Password', 't9admin'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" id="new-password" placeholder="<?php _e('New Password', 't9admin'); ?>">
                                        <label for="new-password"><?php _e('New Password', 't9admin'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" id="retype-password" placeholder="<?php _e('Confirm New Password', 't9admin'); ?>">
                                        <label for="retype-password"><?php _e('Confirm New Password', 't9admin'); ?></label>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information Form -->
                <div class="card w-100 border position-relative overflow-hidden">
                    <div class="card-body p-4">
                        <h4 class="card-title"><?php _e('Personal Details', 't9admin'); ?></h4>
                            <?php wp_nonce_field('t9admin_pro_update_profile', 't9admin_pro_profile_nonce'); ?>


                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="first-name" name="first_name" value="<?php echo esc_attr($first_name); ?>" placeholder="<?php _e('First Name', 't9admin'); ?>">
                                        <label for="first-name"><?php _e('First Name', 't9admin'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo esc_attr($email); ?>" readonly placeholder="<?php _e('Email', 't9admin'); ?>">
                                        <label for="email"><?php _e('Email', 't9admin'); ?></label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="last-name" name="last_name" value="<?php echo esc_attr($last_name); ?>" placeholder="<?php _e('Last Name', 't9admin'); ?>">
                                        <label for="last-name"><?php _e('Last Name', 't9admin'); ?></label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo esc_attr($phone); ?>" placeholder="<?php _e('Phone', 't9admin'); ?>">
                                        <label for="phone"><?php _e('Phone', 't9admin'); ?></label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success"><?php _e('Save Changes', 't9admin'); ?></button>
                                </div>
                            </div>
                        <div id="alert-container" class="mt-4"></div> <!-- Nơi hiển thị thông báo thành công/thất bại -->
                    </div>
                </div>
            </div> <!-- End Account Tab -->
            </form>
        </div>
    </div>
</div>

<script>


    document.getElementById('upload-avatar-btn').addEventListener('click', function() {
        document.getElementById('upload-avatar').click();
    });
</script>
