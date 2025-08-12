<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'ATS'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --gradient-bg: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        .navbar-gradient { background: var(--gradient-bg) !important; }
        .bg-gradient-custom { background: var(--gradient-bg) !important; }
        /* @media (max-width: 991.98px) {
            .navbar-collapse {
                background: white;
                margin-top: 1rem;
                border-radius: 0.5rem;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }
            .mobile-nav .nav-link { color: #495057 !important; border-bottom: 1px solid #e9ecef; }
        } */

        /* Mobile Sidebar Styles */
.mobile-sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.mobile-sidebar-overlay.show {
    opacity: 1;
    visibility: visible;
}

.mobile-sidebar {
    position: fixed;
    top: 0;
    left: -280px;
    width: 280px;
    height: 100%;
    background-color: white;
    z-index: 1050;
    transition: left 0.3s ease;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.mobile-sidebar.show {
    left: 0;
}

.sidebar-header {
    background: var(--gradient-bg) !important;
}

.sidebar-menu {
    padding: 1rem 0;
}

.sidebar-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: #495057;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    background: none;
}

.sidebar-item:hover {
    background-color: #f8f9fa;
    color: var(--primary-color);
    text-decoration: none;
}

.sidebar-item.active {
    background-color: var(--primary-color);
    color: white;
    border-right: 3px solid var(--secondary-color);
}

.sidebar-item i {
    width: 20px;
    margin-right: 12px;
    text-align: center;
}

.sidebar-divider {
    margin: 1rem 1.5rem;
    border-color: #e9ecef;
}

/* Hide sidebar on desktop */
@media (min-width: 992px) {
    .mobile-sidebar,
    .mobile-sidebar-overlay {
        display: none !important;
    }
}

    </style>

    <script>
        // Mobile Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const closeSidebar = document.getElementById('closeSidebar');

    // Open sidebar
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileSidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent body scroll
        });
    }

    // Close sidebar
    function closeSidebarMenu() {
        mobileSidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.style.overflow = ''; // Restore body scroll
    }

    if (closeSidebar) {
        closeSidebar.addEventListener('click', closeSidebarMenu);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebarMenu);
    }

    // Close sidebar when clicking on menu items
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.addEventListener('click', function() {
            if (this.getAttribute('href') !== '#') {
                closeSidebarMenu();
            }
        });
    });

    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeSidebarMenu();
        }
    });
});

    async function logout() {
        if (confirm('Are you sure you want to logout?')) {
            try {
                const response = await fetch('../../api/auth.php?action=logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                });
                if (response.ok) {
                    window.location.href = '../../index.php'; // Redirect to login page
                } else {
                    alert('Logout failed, please try again.');
                }
            } catch (error) {
                console.error('Logout error:', error);
                alert('Network error during logout.');
            }
        }
    }


    </script>
</head>
<body class="bg-light">