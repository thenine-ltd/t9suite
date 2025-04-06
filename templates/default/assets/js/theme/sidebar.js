export function initSidebar() {
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
    
}
