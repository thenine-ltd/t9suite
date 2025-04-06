<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

$customers = get_posts(['post_type' => 'customers', 'numberposts' => -1]);
?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="tours-form" novalidate>
            <?php wp_nonce_field('t9_tours_save_action', 't9_tours_nonce'); ?>
            <input type="hidden" name="post_type" value="tours">
            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

            <!-- Phần 1: Tour Information & Security Information -->
            <div class="row mb-4">
                <!-- Bên trái: Tour Information -->
                <div class="col-lg-6">
                    <div class="card w-100 border position-relative overflow-hidden mb-3">
                        <div class="card-header p-4">
                            <h4 class="card-title mb-0"><?php _e('Tour Information', 't9admin-pro'); ?></h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-floating mb-3">
                                <input type="text" name="tour_name" class="form-control" id="tour_name" 
                                    value="<?php echo $tour_name; ?>" placeholder="Tour Name" required>
                                <label for="tour_name"><?php esc_html_e('Tour Name', 't9admin-pro'); ?></label>
                                <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                <div class="invalid-feedback"><?php _e('Please enter tour name.', 't9admin-pro'); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="date" name="tour_start_date" class="form-control" id="tour_start_date" 
                                            value="<?php echo esc_attr($tour_start_date); ?>" placeholder="Start Date">
                                        <label for="tour_start_date"><?php esc_html_e('Start Date', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="date" name="tour_end_date" class="form-control" id="tour_end_date" 
                                            value="<?php echo esc_attr($tour_end_date); ?>" placeholder="End Date">
                                        <label for="tour_end_date"><?php esc_html_e('End Date', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="time" name="start_time" class="form-control" id="start_time" 
                                            value="<?php echo esc_attr(get_post_meta($post_id, 'start_time', true)); ?>" placeholder="Start Time">
                                        <label for="start_time"><?php esc_html_e('Start Time', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="time" name="end_time" class="form-control" id="end_time" 
                                            value="<?php echo esc_attr(get_post_meta($post_id, 'end_time', true)); ?>" placeholder="End Time">
                                        <label for="end_time"><?php esc_html_e('End Time', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="number" name="interval" class="form-control" id="interval" 
                                            value="<?php echo esc_attr(get_post_meta($post_id, 'interval', true)); ?>" placeholder="Interval" min="1">
                                        <label for="interval"><?php esc_html_e('Interval (minutes)', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select name="repeat" id="repeat" class="form-select">
                                            <option value="daily" <?php selected(get_post_meta($post_id, 'repeat', true), 'daily'); ?>><?php _e('Daily', 't9admin-pro'); ?></option>
                                            <option value="weekly" <?php selected(get_post_meta($post_id, 'repeat', true), 'weekly'); ?>><?php _e('Weekly', 't9admin-pro'); ?></option>
                                        </select>
                                        <label for="repeat"><?php esc_html_e('Frequency', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bên phải: Security Information -->
                <div class="col-lg-6">
                    <div class="card w-100 border position-relative overflow-hidden mb-3">
                        <div class="card-header p-4">
                            <h4 class="card-title mb-0"><?php _e('Security Information', 't9admin-pro'); ?></h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-floating mb-3">
                                <select name="customer" id="customer" class="form-select">
                                    <option value=""><?php _e('-- Select Customer --', 't9admin-pro'); ?></option>
                                    <?php foreach ($customers as $cust): ?>
                                        <option value="<?php echo esc_attr($cust->ID); ?>" <?php selected($customer, $cust->ID); ?>>
                                            <?php echo esc_html($cust->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="customer"><?php esc_html_e('Customer', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <select name="selecting_site" id="selecting_site" class="form-select">
                                    <option value=""><?php _e('-- Select Site --', 't9admin-pro'); ?></option>
                                    <?php if ($customer): 
                                        $customer_sites = get_posts([
                                            'post_type' => 'sites',
                                            'meta_key' => 'related_customer',
                                            'meta_value' => $customer,
                                            'numberposts' => -1,
                                        ]);
                                        foreach ($customer_sites as $site): ?>
                                            <option value="<?php echo esc_attr($site->ID); ?>" <?php selected($selecting_site, $site->ID); ?>>
                                                <?php echo esc_html($site->post_title); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <label for="selecting_site"><?php esc_html_e('Site', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <select name="selecting_guard" id="selecting_guard" class="form-select">
                                    <option value=""><?php _e('-- Select Assignee --', 't9admin-pro'); ?></option>
                                    <?php if ($selecting_site):
                                        $site_staffs = get_post_meta($selecting_site, 'related_patrol_staff', true) ?: [];
                                        foreach ($staffs as $staff):
                                            if (in_array($staff->ID, $site_staffs)): ?>
                                                <option value="<?php echo esc_attr($staff->ID); ?>" <?php selected($selecting_guard, $staff->ID); ?>>
                                                    <?php echo esc_html($staff->display_name); ?>
                                                </option>
                                            <?php endif;
                                        endforeach;
                                    endif; ?>
                                </select>
                                <label for="selecting_guard"><?php esc_html_e('Assignee', 't9admin-pro'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phần 2: Repeater -->
            <div class="card w-100 border position-relative overflow-hidden mb-3">
                <div class="card-header p-4">
                    <h4 class="card-title mb-0"><?php _e('Checkpoints', 't9admin-pro'); ?></h4>
                </div>
                <div class="card-body p-4">
                    <div id="rounds-repeater">
                        <?php 
                        $rounds = get_post_meta($post_id, 'tour_rounds', true) ?: [[]];
                        foreach ($rounds as $index => $round):
                            $checkpoint = $round['checkpoint'] ?? '';
                            $round_status = $round['status'] ?? 'inactive';
                        ?>
                            <div class="round-entry mb-3" data-index="<?php echo $index; ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select name="tour_rounds[<?php echo $index; ?>][checkpoint]" class="form-select checkpoints-select" data-index="<?php echo $index; ?>">
                                                <option value=""><?php _e('-- Select Checkpoint --', 't9admin-pro'); ?></option>
                                                <?php 
                                                if ($selecting_site):
                                                    $site_checkpoints = get_post_meta($selecting_site, 'related_checkpoints', true) ?: [];
                                                    foreach ($all_checkpoints as $cp):
                                                        if (in_array($cp->ID, $site_checkpoints)): ?>
                                                            <option value="<?php echo esc_attr($cp->ID); ?>" <?php selected($checkpoint, $cp->ID); ?>>
                                                                <?php echo esc_html($cp->post_title); ?>
                                                            </option>
                                                        <?php endif;
                                                    endforeach;
                                                endif; ?>
                                            </select>
                                            <label><?php _e('Checkpoint', 't9admin-pro'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-floating">
                                            <select name="tour_rounds[<?php echo $index; ?>][status]" class="form-select">
                                                <option value="completed" <?php selected($round_status, 'completed'); ?>><?php _e('Completed', 't9admin-pro'); ?></option>
                                                <option value="processing" <?php selected($round_status, 'processing'); ?>><?php _e('Processing', 't9admin-pro'); ?></option>
                                                <option value="lated" <?php selected($round_status, 'lated'); ?>><?php _e('Lated', 't9admin-pro'); ?></option>
                                                <option value="inactive" <?php selected($round_status, 'inactive'); ?>><?php _e('Inactive', 't9admin-pro'); ?></option>
                                            </select>
                                            <label><?php _e('Status', 't9admin-pro'); ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn bg-danger-subtle text-danger remove-round" style="margin-top: 10px;">-</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn bg-primary-subtle text-primary add-round"><?php _e('+ Add Checkpoint', 't9admin-pro'); ?></button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100"><?php _e('Save Changes', 't9admin-pro'); ?></button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tours-form');
    let roundIndex = <?php echo count($rounds); ?>;

    // Validation form (giữ nguyên)
    form.addEventListener('submit', function(event) {
        let isValid = true;

        ['tour_name'].forEach(id => {
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

    form.querySelectorAll('input, select').forEach(input => {
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

    // Xử lý AJAX khi chọn Customer
    document.getElementById('customer').addEventListener('change', function() {
        const customerId = this.value;
        console.log('Customer selected:', customerId);

        if (!customerId) {
            document.getElementById('selecting_site').innerHTML = '<option value=""><?php _e('-- Select Site --', 't9admin-pro'); ?></option>';
            document.getElementById('selecting_guard').innerHTML = '<option value=""><?php _e('-- Select Assignee --', 't9admin-pro'); ?></option>';
            document.querySelectorAll('.checkpoints-select').forEach(select => {
                select.innerHTML = '<option value=""><?php _e('-- Select Checkpoint --', 't9admin-pro'); ?></option>';
            });
            return;
        }

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'load_sites_by_customer',
                customer_id: customerId,
                nonce: '<?php echo wp_create_nonce('t9admin_pro_action'); ?>'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Sites response:', data);
            if (data.success) {
                const siteSelect = document.getElementById('selecting_site');
                siteSelect.innerHTML = '<option value=""><?php _e('-- Select Site --', 't9admin-pro'); ?></option>';
                data.data.forEach(site => {
                    siteSelect.innerHTML += `<option value="${site.id}">${site.title}</option>`;
                });
                siteSelect.value = ''; // Reset site selection
                siteSelect.dispatchEvent(new Event('change')); // Trigger change để load site data
            } else {
                console.error('Error loading sites:', data.data || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('AJAX error (Customer):', error);
        });
    });

    // Xử lý AJAX khi chọn Site
    document.getElementById('selecting_site').addEventListener('change', function() {
        const siteId = this.value;
        console.log('Site selected:', siteId);

        if (!siteId) {
            document.getElementById('selecting_guard').innerHTML = '<option value=""><?php _e('-- Select Assignee --', 't9admin-pro'); ?></option>';
            document.querySelectorAll('.checkpoints-select').forEach(select => {
                select.innerHTML = '<option value=""><?php _e('-- Select Checkpoint --', 't9admin-pro'); ?></option>';
            });
            return;
        }

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'load_site_data',
                site_id: siteId,
                nonce: '<?php echo wp_create_nonce('t9admin_pro_action'); ?>'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Site data response:', data);
            if (data.success) {
                // Load Assignee (Patrol Staff)
                const guardSelect = document.getElementById('selecting_guard');
                guardSelect.innerHTML = '<option value=""><?php _e('-- Select Assignee --', 't9admin-pro'); ?></option>';
                data.data.staffs.forEach(staff => {
                    guardSelect.innerHTML += `<option value="${staff.id}">${staff.name}</option>`;
                });

                // Load Checkpoints vào repeater
                document.querySelectorAll('.checkpoints-select').forEach(select => {
                    const currentValue = select.value; // Giữ giá trị hiện tại nếu có
                    select.innerHTML = '<option value=""><?php _e('-- Select Checkpoint --', 't9admin-pro'); ?></option>';
                    data.data.checkpoints.forEach(checkpoint => {
                        select.innerHTML += `<option value="${checkpoint.id}">${checkpoint.title}</option>`;
                    });
                    if (currentValue && select.querySelector(`option[value="${currentValue}"]`)) {
                        select.value = currentValue; // Khôi phục giá trị đã chọn
                    }
                });
            } else {
                console.error('Error loading site data:', data.data || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('AJAX error (Site):', error);
        });
    });

    // Xử lý Repeater
    document.querySelector('.add-round').addEventListener('click', function() {
        const siteId = document.getElementById('selecting_site').value;
        const newRound = `
            <div class="round-entry mb-3" data-index="${roundIndex}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select name="tour_rounds[${roundIndex}][checkpoint]" class="form-select checkpoints-select" data-index="${roundIndex}">
                                <option value=""><?php _e('-- Select Checkpoint --', 't9admin-pro'); ?></option>
                            </select>
                            <label><?php _e('Checkpoint', 't9admin-pro'); ?></label>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-floating">
                            <select name="tour_rounds[${roundIndex}][status]" class="form-select">
                                <option value="completed"><?php _e('Completed', 't9admin-pro'); ?></option>
                                <option value="processing"><?php _e('Processing', 't9admin-pro'); ?></option>
                                <option value="lated"><?php _e('Lated', 't9admin-pro'); ?></option>
                                <option value="inactive" selected><?php _e('Inactive', 't9admin-pro'); ?></option>
                            </select>
                            <label><?php _e('Status', 't9admin-pro'); ?></label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn bg-danger-subtle text-danger remove-round" style="margin-top: 10px;">-</button>
                    </div>
                </div>
            </div>`;
        document.getElementById('rounds-repeater').insertAdjacentHTML('beforeend', newRound);
        if (siteId) {
            document.getElementById('selecting_site').dispatchEvent(new Event('change')); // Trigger để load checkpoints
        }
        roundIndex++;
    });

    document.getElementById('rounds-repeater').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-round')) {
            if (document.querySelectorAll('.round-entry').length > 1) {
                e.target.closest('.round-entry').remove();
            }
        }
    });

    // Load dữ liệu ban đầu nếu có customer/site đã chọn
    const initialCustomerId = document.getElementById('customer').value;
    if (initialCustomerId) {
        document.getElementById('customer').dispatchEvent(new Event('change'));
    }
});
</script>