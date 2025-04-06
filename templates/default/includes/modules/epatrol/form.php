<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

$customers = get_posts(['post_type' => 'customers', 'numberposts' => -1]);
$api_key = 'AIzaSyA3XuxLNGwP1kKZovEoQkX0vm2OAgCXYP8'; // Thay bằng API Key của bạn
?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="sites-form" novalidate>
            <?php wp_nonce_field('t9_sites_save_action', 't9_sites_nonce'); ?>
            <input type="hidden" name="post_type" value="sites">
            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

            <div class="row">
                <!-- Cột trái: Sites Information -->
                <div class="col-lg-6">
                    <div class="card w-100 border position-relative overflow-hidden mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0"><?php _e('Sites Information', 't9admin-pro'); ?></h4>
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
                                <input type="text" name="site_name" class="form-control" id="site_name" 
                                    value="<?php echo $site_name; ?>" placeholder="Site Name" required>
                                <label for="site_name"><?php esc_html_e('Site Name', 't9admin-pro'); ?></label>
                                <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                <div class="invalid-feedback"><?php _e('Please enter site name.', 't9admin-pro'); ?></div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="representative" class="form-control" id="representative" 
                                    value="<?php echo esc_attr($representative); ?>" placeholder="Representative">
                                <label for="representative"><?php esc_html_e('Representative', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" name="phone" class="form-control" id="phone" 
                                    value="<?php echo esc_attr($phone); ?>" placeholder="Phone">
                                <label for="phone"><?php esc_html_e('Phone', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control" id="email" 
                                    value="<?php echo esc_attr($email); ?>" placeholder="Email">
                                <label for="email"><?php esc_html_e('Email', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="city" class="form-control" id="city" 
                                    value="<?php echo esc_attr($city); ?>" placeholder="City">
                                <label for="city"><?php esc_html_e('City', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="district" class="form-control" id="district" 
                                    value="<?php echo esc_attr($district); ?>" placeholder="District">
                                <label for="district"><?php esc_html_e('District', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="address" class="form-control" id="site_address" 
                                    value="<?php echo esc_attr($address); ?>" placeholder="Address">
                                <label for="site_address"><?php esc_html_e('Address', 't9admin-pro'); ?></label>
                            </div>
                            <div id="map" style="height: 300px; width: 100%; margin-bottom: 20px;"></div>
                            <div class="form-floating mb-3">
                                <select name="related_customer" id="related_customer" class="form-select select2">
                                    <?php 
                                    $related_customer = get_post_meta($post_id, 'related_customer', true);
                                    foreach ($customers as $customer): ?>
                                        <option value="<?php echo esc_attr($customer->ID); ?>" <?php selected($related_customer, $customer->ID); ?>>
                                            <?php echo esc_html($customer->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="related_customer"><?php esc_html_e('Customer', 't9admin-pro'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Security Information -->
                <div class="col-lg-6">
                    <div class="card w-100 border position-relative overflow-hidden mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0"><?php _e('Security Information', 't9admin-pro'); ?></h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-floating mb-3">
                                <select name="related_leader" id="related_leader" class="form-select select2">
                                    <option value=""><?php _e('-- Select Team Leader --', 't9admin-pro'); ?></option>
                                    <?php foreach ($captains as $captain): ?>
                                        <option value="<?php echo esc_attr($captain->ID); ?>" <?php selected($related_leader, $captain->ID); ?>>
                                            <?php echo esc_html($captain->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="related_leader"><?php esc_html_e('Team Leader', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3 position-relative">
                                <select name="related_patrol_staff[]" id="related_patrol_staff" class="form-select select2" multiple>
                                    <?php 
                                    $related_patrol_staff = is_array($related_patrol_staff) ? $related_patrol_staff : (array) $related_patrol_staff;
                                    foreach ($staffs as $staff):
                                        $staff_post = get_posts([
                                            'post_type' => 'staffs',
                                            'meta_key' => 'user_id',
                                            'meta_value' => $staff->ID,
                                            'numberposts' => 1,
                                        ]);
                                        $avatar = !empty($staff_post) && has_post_thumbnail($staff_post[0]->ID) 
                                            ? get_the_post_thumbnail_url($staff_post[0]->ID, 'thumbnail') 
                                            : 'https://via.placeholder.com/50';
                                    ?>
                                        <option value="<?php echo esc_attr($staff->ID); ?>" 
                                            data-avatar="<?php echo esc_url($avatar); ?>" 
                                            <?php echo in_array($staff->ID, $related_patrol_staff) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($staff->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="related_patrol_staff"><?php esc_html_e('Patrol Staff', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <select name="related_checkpoints[]" id="related_checkpoints" class="form-select select2" multiple>
                                    <?php foreach ($checkpoints as $checkpoint): ?>
                                        <option value="<?php echo esc_attr($checkpoint->ID); ?>" <?php echo is_array($related_checkpoints) && in_array($checkpoint->ID, $related_checkpoints) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($checkpoint->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="related_checkpoints"><?php esc_html_e('Checkpoints', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <textarea name="notes" class="form-control" id="notes" placeholder="Security Note" style="height: 100px;"><?php echo esc_textarea($notes); ?></textarea>
                                <label for="notes"><?php esc_html_e('Security Note', 't9admin-pro'); ?></label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?php _e('Save Changes', 't9admin-pro'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($api_key); ?>&libraries=places"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sites-form');
    const addressInput = document.getElementById('site_address');
    let map, marker;

    // Khởi tạo bản đồ với vị trí mặc định (Hà Nội)
    function initMap(lat = 21.0285, lng = 105.8542) {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: lat, lng: lng },
            zoom: 15,
        });
        marker = new google.maps.Marker({
            map: map,
            draggable: false, // Không kéo được vì không cần tọa độ
            position: { lat: lat, lng: lng }
        });
    }

    // Khởi tạo autocomplete cho địa chỉ
    const autocomplete = new google.maps.places.Autocomplete(addressInput);
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            map.setCenter({ lat: lat, lng: lng });
            marker.setPosition({ lat: lat, lng: lng });
        }
    });

    // Khởi tạo bản đồ khi tải trang
    initMap();

    // Validation form
    form.addEventListener('submit', function(event) {
        let isValid = true;

        ['site_name'].forEach(id => {
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

    // Khởi tạo Select2 nếu có
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('#related_leader').select2();
        jQuery('#related_patrol_staff').select2({
            templateResult: function(data) {
                if (!data.element) return data.text;
                var $option = jQuery(data.element);
                var avatar = $option.data('avatar');
                return jQuery('<span><img src="' + avatar + '" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;" />' + data.text + '</span>');
            },
            templateSelection: function(data) {
                if (!data.element) return data.text;
                var $option = jQuery(data.element);
                var avatar = $option.data('avatar');
                return jQuery('<span><img src="' + avatar + '" style="width: 20px; height: 20px; border-radius: 50%;" /></span>');
            }
        });
        jQuery('#related_checkpoints').select2();
        jQuery('#related_customer').select2();
    }
});
</script>