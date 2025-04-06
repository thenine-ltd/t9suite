<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

// Lấy URL thumbnail nếu có
$thumbnail_url = $post_id && has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'thumbnail') : 'https://via.placeholder.com/120';
?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="customers-form" novalidate>
            <?php wp_nonce_field('t9_customers_save_action', 't9_customers_nonce'); ?>
            <input type="hidden" name="post_type" value="customers">
            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

            <div class="row">
                <!-- Cột trái: Upload Logo -->
                <div class="col-lg-6">
                    <div class="card w-100 border position-relative overflow-hidden mb-3">
                        <div class="card-header text-center">
                            <h4 class="card-title"><?php _e('Logo', 't9admin-pro'); ?></h4>
                        </div>   
                        <div class="card-body p-4">
                            <div class="text-center position-relative">
                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="Customer Logo" class="object-fit-contain" id="customer-logo" width="120" height="120">
                                <div id="progress-circle" class="position-absolute w-100 h-100 top-0 start-0 d-flex align-items-center justify-content-center" style="visibility: hidden;">
                                    <div class="spinner-border text-light" role="status" style="width: 60px; height: 60px;">
                                        <span class="visually-hidden">Uploading...</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-center my-4 gap-6">
                                    <input type="file" name="logo" id="upload-logo" accept="image/*" style="display: none;">
                                    <button type="button" id="upload-logo-btn" class="btn bg-primary-subtle text-primary"><?php _e('Change Logo', 't9admin-pro'); ?></button>
                                </div>
                                <p class="mb-0"><?php _e('Allowed JPG, GIF, PNG. Max size 800KB.', 't9admin-pro'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Customer Details -->
                <div class="col-lg-6">
                    <div class="card w-100 border position-relative overflow-hidden mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0"><?php _e('Customer Details', 't9admin-pro'); ?></h4>
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
                                <input type="text" name="customer_name" class="form-control" id="customer_name" 
                                    value="<?php echo $customer_name; ?>" placeholder="Customer Name" required>
                                <label for="customer_name"><?php esc_html_e('Customer Name', 't9admin-pro'); ?></label>
                                <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                <div class="invalid-feedback"><?php _e('Please enter customer name.', 't9admin-pro'); ?></div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="representative" class="form-control" id="representative" 
                                    value="<?php echo esc_attr($representative); ?>" placeholder="Representative" required>
                                <label for="representative"><?php esc_html_e('Representative', 't9admin-pro'); ?></label>
                                <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                <div class="invalid-feedback"><?php _e('Please enter representative.', 't9admin-pro'); ?></div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" name="phone" class="form-control" id="phone" 
                                    value="<?php echo esc_attr($phone); ?>" placeholder="Phone" required>
                                <label for="phone"><?php esc_html_e('Phone', 't9admin-pro'); ?></label>
                                <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                <div class="invalid-feedback"><?php _e('Please enter a valid phone number.', 't9admin-pro'); ?></div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control" id="email" 
                                    value="<?php echo esc_attr($email); ?>" placeholder="Email">
                                <label for="email"><?php esc_html_e('Email', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <textarea name="notes" class="form-control" id="notes" placeholder="Notes" style="height: 100px;"><?php echo esc_textarea($notes); ?></textarea>
                                <label for="notes"><?php esc_html_e('Notes', 't9admin-pro'); ?></label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?php _e('Save Changes', 't9admin-pro'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('customers-form');
    const uploadBtn = document.getElementById('upload-logo-btn');
    const uploadInput = document.getElementById('upload-logo');

    if (uploadBtn && uploadInput) {
        uploadBtn.addEventListener('click', function() {
            uploadInput.click();
        });

        uploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('customer-logo').src = e.target.result;
                    document.getElementById('progress-circle').style.visibility = 'visible';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    form.addEventListener('submit', function(event) {
        let isValid = true;

        ['customer_name', 'representative', 'phone'].forEach(id => {
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

        if (!isValid) {
            event.preventDefault();
            event.stopPropagation();
        }
    });

    form.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('input', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
});
</script>