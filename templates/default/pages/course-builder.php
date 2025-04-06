<?php
// Kiểm tra quyền truy cập
?>
    <style>
        body.course-builder {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .course-builder-topbar {
            height: 64px;
            background-color: #fff;
            color: #2C3659;
            padding: 0 20px;
                margin-bottom: 48px;
                        justify-content: space-between;

        }

        .course-builder-topbar a {
            text-decoration: none;
            color: #2C3659;
            font-size: 16px;
            display: flex;
            align-items: center;
        }

        .course-builder-topbar a svg {
            margin-right: 10px;
        }

        .course-builder-form-container {
            display: flex;
            justify-content: center;
            align-items: center;
                margin-bottom: 48px;
        }

        .course-builder-form {
            width: 800px;
            background: white;
            padding: 30px;
            border-radius: 8px;
        }

    .drag-drop-area {
    border: 2px dashed #007bff;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
        margin-bottom: 16px;
}

.course-builder button.btn.btn-primary {
    font-size: 14px;
    padding: 16px 10px;
    font-weight: bold;
}

.drag-drop-area:hover {
    background-color: #e9f5ff;
}

.preview-container img {
    max-width: 100%;
    height: auto;
    margin-top: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.course-builder-topbar h3 {
    font-size: 16px;
    font-weight: bold;
}


        .form-floating > textarea {
            height: 100px !important;
        }
    </style>

<div class="course-builder">
    <!-- Topbar -->
    <div class="course-builder-topbar d-flex justify-content-space-between align-items-center">
        <a href="javascript:history.back()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M5.854 4.646a.5.5 0 0 1 0 .708L2.707 8H13.5a.5.5 0 0 1 0 1H2.707l3.147 2.646a.5.5 0 0 1-.708.708l-4-3.5a.5.5 0 0 1 0-.708l4-3.5a.5.5 0 0 1 .708 0z"/>
            </svg>
        </a>
        <h3 class="text-center mb-0"><?php _e('Tạo Khóa Học', 't9admin'); ?></h3>
        <div></div>
    </div>

    <!-- Form Container -->
    <div class="course-builder-form-container">
        <form id="course-builder-form" class="course-builder-form">

            <div class="form-floating mb-3">
                <input type="text" id="course_name" name="course_name" class="form-control" placeholder="Course Name" required>
                <label for="course_name"><?php _e('Tên Khóa Học', 't9admin'); ?></label>
            </div>

            <div class="form-floating mb-3">
                <select id="course_categories" name="course_categories[]" class="form-select" multiple>
                    <?php
                    $categories = get_terms(['taxonomy' => 'course_category', 'hide_empty' => false]);
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                    }
                    ?>
                </select>
                <label for="course_categories"><?php _e('Danh Mục', 't9admin'); ?></label>
            </div>

            <div class="drag-drop-area" id="drag-drop-area">
                <p><?php _e('Thả hình vào hoặc chọn tải lên từ máy tính', 't9admin'); ?></p>
                <input type="file" id="course_image" name="course_image" class="form-control d-none" accept="image/*">
                <div class="preview-container mt-3"></div>
<div id="progress-container" class="progress" role="progressbar" aria-label="Upload Progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="display: none; height: 25px;">
  <div id="upload-progress" class="progress-bar text-bg-warning" style="width: 0%;">0%</div>
</div>
                <p id="upload-status" class="text-muted mt-2"></p>
            </div>
            
            <div class="form-floating mb-3">
                <textarea id="short_description" name="short_description" class="form-control" placeholder="Short Description" required></textarea>
                <label for="short_description"><?php _e('Tóm Tắt', 't9admin'); ?></label>
            </div>

            <div class="mb-3">
                <textarea id="long_description" name="long_description" class="form-control" placeholder="Mô Tả Chi Tiết"></textarea>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary"><?php _e('Lưu và Bước Tiếp Theo: Tạo Curriculum', 't9admin'); ?></button>
            </div>
        </form>
    </div>
</div>
<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dragDropArea = document.getElementById('drag-drop-area');
    const fileInput = document.getElementById('course_image');
    const previewContainer = document.querySelector('.preview-container');
    const progressBar = document.getElementById('upload-progress');
    const progressContainer = document.getElementById('progress-container');
    const uploadStatus = document.getElementById('upload-status');

    // Handle Drag & Drop Events
    dragDropArea.addEventListener('click', () => fileInput.click());

    dragDropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dragDropArea.style.backgroundColor = '#e9f5ff';
    });

    dragDropArea.addEventListener('dragleave', () => {
        dragDropArea.style.backgroundColor = 'transparent';
    });

    dragDropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDropArea.style.backgroundColor = 'transparent';
        const files = e.dataTransfer.files;
        if (files.length) handleFileUpload(files[0]);
    });

    fileInput.addEventListener('change', function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            handleFileUpload(files[0]); // Handle the first selected file
        }
    });

    function handleFileUpload(file) {
    if (!file.type.startsWith('image/')) {
        showToast('Please upload a valid image file.', 'error');
        return;
    }

    // Hiển thị trước hình ảnh ngay khi chọn
    updatePreview(file);

    // Hiển thị progress bar
    progressContainer.style.display = 'block';
    progressContainer.setAttribute('aria-valuenow', 0);
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';

    const formData = new FormData();
    formData.append('action', 'upload_course_image');
    formData.append('course_image', file);
    formData.append('_wpnonce', '<?php echo wp_create_nonce("t9admin_nonce"); ?>');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo admin_url("admin-ajax.php"); ?>', true);

    // Cập nhật trạng thái progress bar
    xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = `${percentComplete}%`;
            progressBar.textContent = `${percentComplete}%`;
            progressContainer.setAttribute('aria-valuenow', percentComplete);
        }
    };

    // Xử lý kết quả tải lên
    xhr.onload = function () {
        progressContainer.style.display = 'none';
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                showToast('Upload successful!', 'success');
                updatePreviewFromURL(response.data.url, response.data.id);
            } else {
                showToast(response.data.message || 'Upload failed!', 'error');
            }
        } else {
            showToast('Upload failed!', 'error');
        }
    };

    xhr.onerror = function () {
        progressContainer.style.display = 'none';
        showToast('An error occurred during upload.', 'error');
    };

    xhr.onloadend = function () {
        fileInput.value = ''; // Reset input để có thể chọn lại file khác
    };

    xhr.send(formData);
}


    function updatePreview(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            previewContainer.innerHTML = '';
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    }

    function updatePreviewFromURL(imageUrl, imageId) {
        const img = document.createElement('img');
        img.src = imageUrl;
        previewContainer.innerHTML = '';
        previewContainer.appendChild(img);

        // Store attachment ID for later use
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'featured_image_id';
        hiddenInput.value = imageId;
        const existingInput = document.querySelector('[name="featured_image_id"]');
        if (existingInput) {
            existingInput.value = imageId;
        } else {
            document.getElementById('course-builder-form').appendChild(hiddenInput);
        }
    }

    // Handle Form Submission
    document.getElementById('course-builder-form').addEventListener('submit', function (e) {
    e.preventDefault();
    showToast('Saving...', 'info', true); // Show loading toast

    // Đồng bộ nội dung TinyMCE với textarea
    if (tinymce && tinymce.activeEditor) {
        tinymce.triggerSave();
    }

    const formData = new FormData(this);
    formData.append('action', 'save_course');
    formData.append('_ajax_nonce', '<?php echo wp_create_nonce("t9lms_nonce"); ?>');

    // Gỡ lỗi - kiểm tra dữ liệu trước khi gửi
    console.log('Form data before submission:', [...formData.entries()]);

    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        body: formData,
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log('Response from server:', data); // Gỡ lỗi - kiểm tra phản hồi từ server

            if (data.success) {
                showToast('Course saved successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '<?php echo home_url("/t9admin/create-curriculum"); ?>?course_id=' + data.data.course_id;
                }, 2000); // Delay before redirect
            } else {
                const errorMessage = data.data?.message || 'An error occurred.';
                showToast(errorMessage, 'error');
                console.error('Error details:', data.data);
            }
        })
        .catch((error) => {
            showToast('Unable to save the course.', 'error');
            console.error('Fetch error:', error);
        });
});


    // Toast Notifications
    function showToast(message, type = 'info', isLoading = false) {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} border-0`;
        toast.role = 'alert';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${isLoading ? `<div class="spinner-border spinner-border-sm me-2" role="status"><span class="visually-hidden">Loading...</span></div>` : ''}
                    ${message}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toast);
        const bootstrapToast = new bootstrap.Toast(toast);
        bootstrapToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
});


</script>