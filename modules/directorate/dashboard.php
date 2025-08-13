<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('directorate');

// Page configuration
$page_title = "Directorate Dashboard - ATS";
$current_page = "dashboard";

// Include templates
include_once '../../templates/header.php'; //navbar global style
include_once '../../templates/navbar_directorate.php'; //navbar for alumini
?>

<!-- Your page content here -->



<?php include_once '../../templates/footer.php'; ?>