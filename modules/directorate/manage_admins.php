<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('directorate');

// Page configuration
$page_title = "Directorate Dashboard - ATS";
$current_page = "manage_admins";

// Include templates
include_once '../../templates/header.php'; //navbar global scss tyle
include_once '../../templates/navbar_directorate.php'; //navbar for alumini
?>

<!--  this.......... page content here -->
<!-- ------------------------------------------------------------------------------------------------------------------------------------- -->
<?php include '../../templates/manage_admins.php'; ?>
<script src="../../assets/js/manage_admins.js"></script>
<!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->
<?php include_once '../../templates/footer.php'; ?>