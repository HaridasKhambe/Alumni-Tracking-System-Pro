<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('admin');

// Page configuration
$page_title = "Admin Dashboard - ATS";
$current_page = "manage_notices";

// Include templates
include_once '../../templates/header.php'; //navbar global style
include_once '../../templates/navbar_admin.php'; //navbar for alumini
?>


<!-- -------------------------------------------------------------------------------------- -->
<?php include '../../templates/notice_manager.php'; ?>
<script src="../../assets/js/notice_manager.js"></script>
<!-- -------------------------------------------------------------------------------------- -->



<?php include_once '../../templates/footer.php'; ?>