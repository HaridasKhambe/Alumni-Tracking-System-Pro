<?php
// modules/auth/register_process.php
session_start();
require_once '../../config/database.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

// Initialize error array
$errors = [];

// Get and sanitize form data
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$prn_no = trim($_POST['prn_no'] ?? '');
$branch = trim($_POST['branch'] ?? '');
$passout_year = trim($_POST['passout_year'] ?? '');
$employment_status = trim($_POST['employment_status'] ?? '');
$company_name = trim($_POST['company_name'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($first_name)) {
    $errors[] = "First name is required";
} elseif (strlen($first_name) < 2) {
    $errors[] = "First name must be at least 2 characters";
}

if (empty($last_name)) {
    $errors[] = "Last name is required";
} elseif (strlen($last_name) < 2) {
    $errors[] = "Last name must be at least 2 characters";
}

if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address";
}

if (empty($phone)) {
    $errors[] = "Phone number is required";
} elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = "Phone number must be exactly 10 digits";
}

if (empty($prn_no)) {
    $errors[] = "PRN number is required";
} elseif (strlen($prn_no) < 8) {
    $errors[] = "PRN number must be at least 8 characters";
}

if (empty($branch)) {
    $errors[] = "Branch is required";
}

if (empty($passout_year)) {
    $errors[] = "Passout year is required";
} else {
    $current_year = date('Y');
    if ($passout_year < 2000 || $passout_year > $current_year + 2) {
        $errors[] = "Please enter a valid passout year";
    }
}

if (!in_array($employment_status, ['employed', 'unemployed', 'self-employed'])) {
    $errors[] = "Please select a valid employment status";
}

// If employed or self-employed, company name is required
if (in_array($employment_status, ['employed', 'self-employed']) && empty($company_name)) {
    $errors[] = "Company name is required for employed/self-employed status";
}

if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// If there are validation errors, redirect back with errors
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $_POST; // Keep form data for user convenience
    header('Location: register.php');
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if email already exists
    $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->execute([$email]);
    if ($check_email->rowCount() > 0) {
        throw new Exception("Email address already registered");
    }
    
    // Check if PRN number already exists
    $check_prn = $pdo->prepare("SELECT id FROM alumni WHERE prn_no = ?");
    $check_prn->execute([$prn_no]);
    if ($check_prn->rowCount() > 0) {
        throw new Exception("PRN number already registered");
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert into users table
    $insert_user = $pdo->prepare("
        INSERT INTO users (email, password, role, status) 
        VALUES (?, ?, 'alumni', 'pending')
    ");
    $insert_user->execute([$email, $hashed_password]);
    
    // Get the inserted user ID
    $user_id = $pdo->lastInsertId();
    
    // Insert into alumni table
    $insert_alumni = $pdo->prepare("
        INSERT INTO alumni (user_id, prn_no, first_name, last_name, branch, passout_year, phone, employment_status, company_name) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $insert_alumni->execute([
        $user_id,
        $prn_no,
        $first_name,
        $last_name,
        $branch,
        $passout_year,
        $phone,
        $employment_status,
        $employment_status === 'unemployed' ? null : $company_name
    ]);
    
    // Commit the transaction
    $pdo->commit();
    
    // Set success message
    $_SESSION['success_message'] = "Registration successful! Your account is pending approval from the admin.";
    
    // Clear any stored form data
    unset($_SESSION['form_data']);
    
    // Redirect to login page
    header("Location: register.php?success=1");
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction
    $pdo->rollBack();
    
    // Log the error (in production, use proper logging)
    error_log("Registration error: " . $e->getMessage());

    // // Show error in console (for debugging only)
    // echo "<script>console.error('Registration Error: " . addslashes($e->getMessage()) . "');</script>";
    
    // Set generic error message for security
    $_SESSION['errors'] = ["Registration failed. Please try again."];
    echo $_SESSION['errors'];
    $_SESSION['form_data'] = $_POST;

    header('Location: register.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollBack();
    
    // Set specific error message
    $_SESSION['errors'] = [$e->getMessage()];
    $_SESSION['form_data'] = $_POST;
    
    header('Location: register.php');
    exit();
}
?>