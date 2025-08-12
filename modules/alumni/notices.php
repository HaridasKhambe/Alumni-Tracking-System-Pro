<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('alumni');

// Page configuration
$page_title = "Alumni Dashboard - ATS";
$current_page = "notices";

// Include templates
include_once '../../templates/header.php'; //navbar global style
include_once '../../templates/navbar_alumni.php'; //navbar for alumini
?>

<!-- Your page content here -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-custom text-white shadow-sm border-0 rounded-3">
                <!-- Dashboard content -->
            </div>
        </div>
    </div>
</div>

<?php include_once '../../templates/footer.php'; ?>