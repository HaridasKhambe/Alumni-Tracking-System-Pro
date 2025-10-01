<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('directorate');

// Page configuration
$page_title = "Directorate Dashboard - ATS";
$current_page = "analytics";

// Include templates
include_once '../../templates/header.php'; //navbar global cs style
include_once '../../templates/navbar_directorate.php'; //navbar for alumini
?>


<!-- -------------------------------------------------------------------------------------- -->
<?php include '../../templates/alumni_filter.php'; ?>
<script src="../../assets/js/alumni_filter.js"></script>
<!-- -------------------------------------------------------------------------------------- -->


<?php include_once '../../templates/footer.php'; ?>