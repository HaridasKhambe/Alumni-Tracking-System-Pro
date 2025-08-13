<?php
// api/manage_admins.php
header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

// Check if user is directorate
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'directorate') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        handleGetAdmins($pdo);
        break;
    case 'POST':
        handleAddAdmin($pdo);
        break;
    case 'PUT':
        handleUpdateAdminStatus($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function handleGetAdmins($pdo) {
    // Get search parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    
    $offset = ($page - 1) * $limit;
    
    try {
        // Base query - only admins
        $baseQuery = "FROM users WHERE role = 'admin'";
        
        $conditions = [];
        $params = [];
        
        // Search condition (Email only)
        if (!empty($search)) {
            $conditions[] = "email LIKE ?";
            $params[] = "%$search%";
        }
        
        // Add conditions to base query
        $whereClause = !empty($conditions) ? " AND " . implode(" AND ", $conditions) : "";
        $fullBaseQuery = $baseQuery . $whereClause;
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total " . $fullBaseQuery;
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetch()['total'];
        
        // Get data with pagination - newest first
        $dataQuery = "SELECT 
                        id,
                        email,
                        role,
                        status,
                        created_at
                      " . $fullBaseQuery . "
                      ORDER BY created_at DESC
                      LIMIT $limit OFFSET $offset";
        
        $dataStmt = $pdo->prepare($dataQuery);
        $dataStmt->execute($params);
        $admins = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate pagination info
        $totalPages = ceil($totalRecords / $limit);
        
        echo json_encode([
            'success' => true,
            'data' => $admins,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords,
                'limit' => $limit,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ],
            'filters' => [
                'search' => $search
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleAddAdmin($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $status = $input['status'] ?? 'pending';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password are required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        return;
    }
    
    if (!in_array($status, ['active', 'pending', 'rejected'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        return;
    }
    
    try {
        // Check if email already exists
        $checkQuery = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$email]);
        
        if ($checkStmt->fetch()['count'] > 0) {
            echo json_encode(['success' => false, 'error' => 'Email already exists']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new admin
        $insertQuery = "INSERT INTO users (email, password, role, status) VALUES (?, ?, 'admin', ?)";
        $insertStmt = $pdo->prepare($insertQuery);
        $result = $insertStmt->execute([$email, $hashedPassword, $status]);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Admin added successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add admin']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleUpdateAdminStatus($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $admin_id = $input['admin_id'] ?? '';
    $status = $input['status'] ?? '';
    
    if (empty($admin_id) || empty($status)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    if (!in_array($status, ['active', 'pending', 'rejected'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        return;
    }
    
    try {
        // Update admin status
        $query = "UPDATE users SET status = ? WHERE id = ? AND role = 'admin'";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([$status, $admin_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => "Admin status updated to $status"
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Admin not found']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Update failed: ' . $e->getMessage()]);
    }
}
?>