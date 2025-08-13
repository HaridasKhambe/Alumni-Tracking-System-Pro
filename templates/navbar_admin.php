<!-- Mobile Sidebar Overlay -->
<div class="mobile-sidebar-overlay" id="sidebarOverlay"></div>

<!-- Mobile Sidebar -->
<div class="mobile-sidebar" id="mobileSidebar">
    <div class="sidebar-header p-3 bg-gradient-custom">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center text-white">
                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                    <span class="fw-bold"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'Admin', 0, 2)); ?></span>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></h6>
                    <small class="opacity-75">Admin</small>
                </div>
            </div>
            <button class="btn btn-link text-white p-0" id="closeSidebar">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <a class="sidebar-item <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a class="sidebar-item <?php echo ($current_page == 'verify_alumni') ? 'active' : ''; ?>" href="verify_alumni.php">
            <i class="fas fa-user-check"></i>
            <span>Verify Alumni</span>
        </a>
        <a class="sidebar-item <?php echo ($current_page == 'manage_events') ? 'active' : ''; ?>" href="manage_events.php">
            <i class="fas fa-calendar-alt"></i>
            <span>Manage Events</span>
        </a>
        <a class="sidebar-item <?php echo ($current_page == 'manage_notices') ? 'active' : ''; ?>" href="manage_notices.php">
            <i class="fas fa-bullhorn"></i>
            <span>Manage Notices</span>
        </a>
        <a class="sidebar-item <?php echo ($current_page == 'manage_users') ? 'active' : ''; ?>" href="manage_users.php">
            <i class="fas fa-users-cog"></i>
            <span>Manage </span>
        </a>

        <hr class="sidebar-divider">

        <!-- Settings and Logout placed in dropdown, so only logout here -->
        <a class="sidebar-item <?php echo ($current_page == 'profile') ? 'active' : ''; ?>" href="#">
            <i class="fas fa-cog"></i>
            <span>My Profile</span>
        </a>

        <a class="sidebar-item <?php echo ($current_page == 'settings') ? 'active' : ''; ?>" href="settings.php">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a class="sidebar-item text-danger" href="#" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>

    </div>
</div>

<!-- Desktop Navbar -->
<nav class="navbar navbar-expand-lg navbar-gradient shadow-sm">
    <div class="container-fluid">
        <!-- Mobile Menu Toggle -->
        <button class="navbar-toggler border-light d-lg-none me-2" type="button" id="mobileMenuToggle">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Brand -->
        <a class="navbar-brand text-white fw-bold fs-4" href="dashboard.php">
            <i class="fas fa-user-shield me-2"></i>
            <span class="d-none d-sm-inline">ATS - Admin</span>
            <span class="d-sm-none">ATS</span>
        </a>

        <!-- Desktop Navigation -->
        <div class="collapse navbar-collapse d-none d-lg-flex justify-content-end">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'dashboard') ? 'active text-white' : 'text-white-50'; ?> fw-medium px-3 rounded-pill" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'verify_alumni') ? 'active text-white' : 'text-white-50'; ?> fw-medium px-3 rounded-pill" href="verify_alumni.php">
                        <i class="fas fa-user-check me-2"></i>Verify Alumni
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_events') ? 'active text-white' : 'text-white-50'; ?> fw-medium px-3 rounded-pill" href="manage_events.php">
                        <i class="fas fa-calendar-alt me-2"></i>Manage Events
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_notices') ? 'active text-white' : 'text-white-50'; ?> fw-medium px-3 rounded-pill" href="manage_notices.php">
                        <i class="fas fa-bullhorn me-2"></i>Manage Notices
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'manage_users') ? 'active text-white' : 'text-white-50'; ?> fw-medium px-3 rounded-pill" href="manage_users.php">
                        <i class="fas fa-users-cog me-2"></i>Manage Alumni
                    </a>
                </li>
            </ul>

            <!-- Desktop User Dropdown -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                            <span class="fw-bold"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'Admin', 0, 2)); ?></span>
                        </div>
                        <span class="d-none d-xl-inline"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li><a class="dropdown-item py-2" href="profile.php"><i class="fas fa-cog me-2 text-secondary"></i>My Profile</a></li>
                        <li><a class="dropdown-item py-2" href="settings.php"><i class="fas fa-cog me-2 text-secondary"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
