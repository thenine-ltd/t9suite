<?php
if (!defined('ABSPATH')) {
    exit; // Ngăn truy cập trực tiếp
}

$post_url = get_permalink($post_id);
$qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($post_url) . '&size=225x225';
$api_key = 'AIzaSyA3XuxLNGwP1kKZovEoQkX0vm2OAgCXYP8'; // Thay bằng API Key của bạn
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <!-- Cột trái: Checkpoint Information -->
            <div class="col-md-8">
                <form method="POST" enctype="multipart/form-data" id="checkpoints-form" novalidate>
                    <?php wp_nonce_field('t9_checkpoints_save_action', 't9_checkpoints_nonce'); ?>
                    <input type="hidden" name="post_type" value="checkpoints">
                    <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

                    <div class="card w-100 border position-relative overflow-hidden mb-3">
                        <div class="card-header p-4">
                            <h4 class="card-title mb-0"><?php _e('Checkpoint Information', 't9admin-pro'); ?></h4>
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
                                    value="<?php echo $site_name; ?>" placeholder="Checkpoint" required>
                                <label for="site_name"><?php esc_html_e('Checkpoint', 't9admin-pro'); ?></label>
                                <div class="valid-feedback"><?php _e('Looks good!', 't9admin-pro'); ?></div>
                                <div class="invalid-feedback"><?php _e('Please enter checkpoint name.', 't9admin-pro'); ?></div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="label" class="form-control" id="label" 
                                    value="<?php echo esc_attr($label); ?>" placeholder="Label">
                                <label for="label"><?php esc_html_e('Label', 't9admin-pro'); ?></label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" name="address" class="form-control" id="checkpoint_address" 
                                    value="<?php echo esc_attr(get_post_meta($post_id, 'address', true)); ?>" placeholder="Type address to get Longitude and Latitude">
                                <label for="checkpoint_address"><?php esc_html_e('Address', 't9admin-pro'); ?></label>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" name="longitude" class="form-control" id="longitude" 
                                            value="<?php echo esc_attr($longitude); ?>" placeholder="Longitude" step="any">
                                        <label for="longitude"><?php esc_html_e('Longitude', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" name="latitude" class="form-control" id="latitude" 
                                            value="<?php echo esc_attr($latitude); ?>" placeholder="Latitude" step="any">
                                        <label for="latitude"><?php esc_html_e('Latitude', 't9admin-pro'); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div id="map" style="height: 300px; width: 100%; margin-bottom: 20px;"></div>
                            <div class="form-floating mb-3">
                                <textarea name="security_notes" class="form-control" id="security_notes" placeholder="Security Notes" style="height: 100px;"><?php echo esc_textarea($security_notes); ?></textarea>
                                <label for="security_notes"><?php esc_html_e('Security Notes', 't9admin-pro'); ?></label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?php _e('Save Changes', 't9admin-pro'); ?></button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cột phải: QR Code -->
            <div class="col-md-4">
                <div class="card w-100 border position-relative overflow-hidden mb-3">
                    <div class="card-header text-center">
                        <h4 class="card-title mb-0"><?php _e('QR Code', 't9admin-pro'); ?></h4>
                    </div>
                    <div class="card-body">
                        <img src="<?php echo esc_url($qr_code_url); ?>" alt="QR Code" class="img-fluid">
                    </div>
                    <div class="card-footer text-center">
                        <button type="button" class="btn bg-primary-subtle text-primary" onclick="printQRCode('<?php echo esc_url($qr_code_url); ?>')">
                            <?php _e('Print QR', 't9admin-pro'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($api_key); ?>&libraries=places"></script>
<script>
function printQRCode(qrCodeUrl) {
    if (!qrCodeUrl) {
        alert("QR Code URL is missing!");
        return;
    }

    var printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert("Popup blocked! Please allow popups for this site.");
        return;
    }

    printWindow.document.write(`
        <html>
            <head>
                <title>Print QR Code</title>
                <style>
                    body { text-align: center; font-family: Arial, sans-serif; padding: 20px; }
                    img { max-width: 100%; height: auto; margin-bottom: 20px; }
                    button { padding: 10px 20px; font-size: 16px; }
                </style>
            </head>
            <body>
                <h2>Scan this QR Code</h2>
                <img src="${qrCodeUrl}" alt="QR Code">
                <br>
                <button onclick="window.print()">Print</button>
            </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.print();
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkpoints-form');
    const addressInput = document.getElementById('checkpoint_address');
    const longitudeInput = document.getElementById('longitude');
    const latitudeInput = document.getElementById('latitude');
    let map, marker;

    // Khởi tạo bản đồ với tọa độ mặc định hoặc từ input
    function initMap(lat = 21.0285, lng = 105.8542) { // Mặc định: Hà Nội
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: lat, lng: lng },
            zoom: 15,
        });
        marker = new google.maps.Marker({
            map: map,
            draggable: true,
            position: { lat: lat, lng: lng }
        });

        // Cập nhật tọa độ khi kéo marker
        marker.addListener('dragend', function() {
            const position = marker.getPosition();
            longitudeInput.value = position.lng();
            latitudeInput.value = position.lat();
        });

        // Nếu có tọa độ từ input, cập nhật map
        if (longitudeInput.value && latitudeInput.value) {
            const lat = parseFloat(latitudeInput.value);
            const lng = parseFloat(longitudeInput.value);
            map.setCenter({ lat: lat, lng: lng });
            marker.setPosition({ lat: lat, lng: lng });
        }
    }

    // Khởi tạo autocomplete cho địa chỉ
    const autocomplete = new google.maps.places.Autocomplete(addressInput);
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            longitudeInput.value = lng;
            latitudeInput.value = lat;
            map.setCenter({ lat: lat, lng: lng });
            marker.setPosition({ lat: lat, lng: lng });
        }
    });

    // Cập nhật tọa độ thủ công
    longitudeInput.addEventListener('input', updateMapFromCoords);
    latitudeInput.addEventListener('input', updateMapFromCoords);

    function updateMapFromCoords() {
        const lat = parseFloat(latitudeInput.value);
        const lng = parseFloat(longitudeInput.value);
        if (lat && lng) {
            map.setCenter({ lat: lat, lng: lng });
            marker.setPosition({ lat: lat, lng: lng });
        }
    }

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
});
</script>