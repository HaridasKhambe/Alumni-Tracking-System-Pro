<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Check user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_profile') {
        // Get user profile...................... 
        $query = "SELECT u.email, a.prn_no, a.first_name, a.last_name, a.branch, 
                         a.passout_year, a.phone, a.employment_status, a.company_name 
                  FROM users u 
                  JOIN alumni a ON u.id = a.user_id 
                  WHERE u.id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profile) {
            echo json_encode(['success' => true, 'profile' => $profile]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Profile not found']);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'update_profile') {
            // Validate required fields
            $required_fields = ['first_name', 'last_name', 'email', 'phone', 'prn_no', 'branch', 'passout_year', 'employment_status'];
            $errors = [];
            
            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required';
                }
            }
            
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            
            // Check if email is unique (exclude current user)
            $email_check = "SELECT u.id FROM users u WHERE u.email = ? AND u.id != ?";
            $stmt = $pdo->prepare($email_check);
            $stmt->execute([$input['email'], $user_id]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                exit;
            }
            
            // Check if PRN is unique (exclude current user)
            $prn_check = "SELECT a.id FROM alumni a JOIN users u ON a.user_id = u.id WHERE a.prn_no = ? AND u.id != ?";
            $stmt = $pdo->prepare($prn_check);
            $stmt->execute([$input['prn_no'], $user_id]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'PRN number already exists']);
                exit;
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Update users table
            $update_user = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_user);
            $stmt->execute([$input['email'], $user_id]);
            
            // Update alumni table
            $company_name = ($input['employment_status'] === 'employed' || $input['employment_status'] === 'self-employed') 
                          ? ($input['company_name'] ?? null) : null;
            
            $update_alumni = "UPDATE alumni SET 
                             first_name = ?, last_name = ?, prn_no = ?, branch = ?, 
                             passout_year = ?, phone = ?, employment_status = ?, company_name = ?
                             WHERE user_id = ?";
            
            $stmt = $pdo->prepare($update_alumni);
            $stmt->execute([
                $input['first_name'], $input['last_name'], $input['prn_no'], $input['branch'],
                $input['passout_year'], $input['phone'], $input['employment_status'], 
                $company_name, $user_id
            ]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
            
        } elseif ($action === 'change_password') {
            // Validate password fields
            $current_password = $input['current_password'] ?? '';
            $new_password = $input['new_password'] ?? '';
            $confirm_new_password = $input['confirm_new_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
                echo json_encode(['success' => false, 'message' => 'All password fields are required']);
                exit;
            }
            
            if ($new_password !== $confirm_new_password) {
                echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
                exit;
            }
            
            if (strlen($new_password) < 8) {
                echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters']);
                exit;
            }
            
            // Verify current password
            $verify_query = "SELECT password FROM users WHERE id = ?";
            $stmt = $pdo->prepare($verify_query);
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($current_password, $user['password'])) {
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                exit;
            }
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_password);
            $stmt->execute([$hashed_password, $user_id]);
            
            echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>