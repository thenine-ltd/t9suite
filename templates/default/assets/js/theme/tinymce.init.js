export function initTinyMCE() {
    if (typeof tinymce === 'undefined') {
        console.error("TinyMCE is not loaded.");
        return;
    }

    tinymce.init({
        selector: '#post-content',
        height: 300,
        plugins: 'link image code lists',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
        menubar: false,
        branding: false
    });

    console.log("TinyMCE initialized successfully.");
}
