<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('admin');

// Page configuration
$page_title = "Admin Dashboard - ATS";
$current_page = "manage_users";

// Include templates
include_once '../../templates/header.php'; //navbar global style
include_once '../../templates/navbar_admin.php'; //navbar for alumini
?>

<!-- Your page content here -->

<!-- -------------------------------------------------------------------------------------- -->
<?php include '../../templates/alumni_filter.php'; ?>
<script src="../../assets/js/alumni_filter.js"></script>
<!-- -------------------------------------------------------------------------------------- -->


<?php include_once '../../templates/footer.php'; ?>