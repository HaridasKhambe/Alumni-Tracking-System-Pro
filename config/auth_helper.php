<!-- Sessio authentication [user and role] -->
<?php
// config/auth_helper.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /ATSPro/index.php");
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header("Location: /ATSPro/index.php");
        exit();
    }
}
?>
