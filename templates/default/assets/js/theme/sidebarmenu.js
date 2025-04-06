document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    // Toggle sidebarmenu visibility when clicking on the sidebartoggler
    const sidebarToggler = document.getElementById("headerCollapse");
    const sidebarmenu = document.querySelector(".sidebarmenu");

    // Default state (visible)
    sidebarmenu.style.display = "block";

    sidebarToggler.addEventListener("click", function () {
        if (sidebarmenu.style.display === "block") {
            sidebarmenu.style.display = "none";  // Hide menu
        } else {
            sidebarmenu.style.display = "block";  // Show menu
        }
    });

    // Handle navigation when clicking on mini-nav items
    document.querySelectorAll(".mini-nav-ul li a").forEach(function (navItem) {
        navItem.addEventListener("click", function () {
            // Remove active class from all items
            document.querySelectorAll(".mini-nav-ul li a").forEach(function (item) {
                item.classList.remove("active");
            });
            // Set active class on the clicked item
            navItem.classList.add("active");

            // Hide all menus first
            document.querySelectorAll(".sidebar-nav").forEach(function (menu) {
                menu.style.display = "none";
            });

            // Get the corresponding menu ID dynamically
            const menuId = navItem.parentElement.id.replace('mini-', 'menu-right-mini-');

            // Show the corresponding menu if it exists
            const correspondingMenu = document.getElementById(menuId);
            if (correspondingMenu) {
                correspondingMenu.style.display = "block";
            }
        });
    });

    // Show the first menu by default
    const firstMiniNavItem = document.querySelector(".mini-nav-ul li a");
    if (firstMiniNavItem) {
        firstMiniNavItem.classList.add("active");
        const defaultMenuId = firstMiniNavItem.parentElement.id.replace('mini-', 'menu-right-mini-');
        const defaultMenu = document.getElementById(defaultMenuId);
        if (defaultMenu) {
            defaultMenu.style.display = "block";  // Display the default menu
        }
    }
    
    // Kiểm tra xem thư viện tinymce đã được load chưa
    if (typeof tinymce === 'undefined') {
        console.error("TinyMCE library is not loaded.");
        return;
    }

    // Khởi tạo TinyMCE
    tinymce.init({
        selector: '#post-content',
        height: 300,
        plugins: 'link image code lists',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
        menubar: false,
        branding: false
    });

    // Profile Form Submission
    document.getElementById('profile-form').addEventListener('submit', async function (e) {
        e.preventDefault(); // Ngăn chặn submit form thông thường

const formData = new FormData(document.getElementById('profile-form'));
        formData.append('action', 't9admin_pro_update_profile'); // Định nghĩa action
        formData.append('security', t9adminProData.nonce); // Sử dụng nonce từ localize_script

        try {
                const response = await fetch(t9adminProData.ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
            
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
            
                const result = await response.json();
                console.log('AJAX Response:', result); // Debug thông tin trả về

                let alertType = result.success ? 'alert-success' : 'alert-danger';
                document.getElementById('alert-container').innerHTML = `
                    <div class="alert ${alertType} alert-dismissible fade show" role="alert">
                        ${result.data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            } catch (error) {
                console.error('Error updating profile:', error);
                
            }

    });

    // Avatar Upload Submission
    const uploadInput = document.getElementById('upload-avatar');
    const avatar = document.getElementById('user-avatar');
    const uploadBtn = document.getElementById('upload-avatar-btn');
    const progressCircle = document.getElementById('progress-circle');

    uploadBtn.addEventListener('click', () => {
        uploadInput.click();
    });

    uploadInput.addEventListener('change', async function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('action', 't9admin_pro_upload_avatar');
        formData.append('security', t9adminProData.nonce);

        progressCircle.style.visibility = 'visible';
        avatar.style.opacity = '0.6';

        try {
            const response = await fetch(t9adminProData.ajaxurl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                avatar.src = result.data.avatar_url;
            } else {
                alert(result.data.message);
            }
        } catch (error) {
            console.error('Error uploading avatar:', error);
        } finally {
            progressCircle.style.visibility = 'hidden';
            avatar.style.opacity = '1';
        }
    });
    
});
