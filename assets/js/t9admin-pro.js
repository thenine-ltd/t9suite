jQuery(document).ready(function ($) {
    $('.t9admin-pro-upload-button').click(function (e) {
        e.preventDefault();

        var button = $(this);
        var target = button.data('target');
        var mediaUploader;

        // Kiểm tra và khởi tạo media uploader nếu chưa tồn tại
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Tạo mới media uploader
        mediaUploader = wp.media({
            title: 'Select Logo',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        // Khi chọn ảnh, lấy URL và hiển thị xem trước
        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#' + target).val(attachment.url); // Lưu URL thay vì ID
            $('#' + target + '_preview').html('<img src="' + attachment.url + '" style="max-width: 100px;" alt="Logo Preview">');
        });

        mediaUploader.open();
    });

    $('.t9admin-pro-font-select').select2({
        width: '100%',
        placeholder: 'Search for a font...',
        allowClear: true
    });

    $('.t9admin-pro-darkmode-toggle').on('change', function () {
        if ($(this).is(':checked')) {
            $('body').addClass('dark-mode');
        } else {
            $('body').removeClass('dark-mode');
        }
    });
});
