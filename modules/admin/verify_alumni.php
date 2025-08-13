<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('admin');

// Page configuration
$page_title = "Admin Dashboard - ATS";
$current_page = "verify_alumni";

// Include templates
include_once '../../templates/header.php'; //navbar global style
include_once '../../templates/navbar_admin.php'; //navbar for alumini
?>

<!-- Your page content here -->
<!-- -------------------------------------------------------------------------------------- -->
<?php include '../../templates/verify_alumni.php'; ?>
<script src="../../assets/js/verify_alumni.js"></script>
<!-- -------------------------------------------------------------------------------------- -->


<?php include_once '../../templates/footer.php'; ?>