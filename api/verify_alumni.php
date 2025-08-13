<?php
// api/verify_alumni.php
header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'directorate'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        handleGetPendingAlumni($pdo);
        break;
    case 'PUT':
        handleVerifyAlumni($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function handleGetPendingAlumni($pdo) {
    // Get search parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $branch = isset($_GET['branch']) ? $_GET['branch'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    
    $offset = ($page - 1) * $limit;
    
    try {
        // Base query - only pending alumni
        $baseQuery = "FROM alumni a 
                      INNER JOIN users u ON a.user_id = u.id 
                      WHERE u.status = 'pending' AND u.role = 'alumni'";
        
        $conditions = [];
        $params = [];
        
        // Search condition (Name OR PRN)
        if (!empty($search)) {
            $conditions[] = "(CONCAT(a.first_name, ' ', a.last_name) LIKE ? OR a.prn_no LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Branch filter
        if (!empty($branch)) {
            $conditions[] = "a.branch = ?";
            $params[] = $branch;
        }
        
        // Add conditions to base query
        $whereClause = !empty($conditions) ? " AND " . implode(" AND ", $conditions) : "";
        $fullBaseQuery = $baseQuery . $whereClause;
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total " . $fullBaseQuery;
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetch()['total'];
        
        // Get data with pagination - newest registrations first
        $dataQuery = "SELECT 
                        a.id,
                        a.user_id,
                        a.prn_no,
                        a.first_name,
                        a.last_name,
                        a.branch,
                        a.passout_year,
                        a.phone,
                        a.employment_status,
                        a.company_name,
                        u.email,
                        u.created_at as registration_date
                      " . $fullBaseQuery . "
                      ORDER BY u.created_at
                      LIMIT $limit OFFSET $offset";
        
        $dataStmt = $pdo->prepare($dataQuery);
        $dataStmt->execute($params);
        $alumni = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate pagination info
        $totalPages = ceil($totalRecords / $limit);
        
        echo json_encode([
            'success' => true,
            'data' => $alumni,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords,
                'limit' => $limit,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ],
            'filters' => [
                'search' => $search,
                'branch' => $branch
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleVerifyAlumni($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? '';
    $action = $input['action'] ?? '';
    
    if (empty($user_id) || empty($action)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    if (!in_array($action, ['approve', 'reject'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        return;
    }
    
    try {
        // Determine new status
        $newStatus = ($action === 'approve') ? 'active' : 'rejected';
        
        // Update user status
        $query = "UPDATE users SET status = ? WHERE id = ? AND role = 'alumni' AND status = 'pending'";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([$newStatus, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $actionText = ($action === 'approve') ? 'approved' : 'rejected';
            echo json_encode([
                'success' => true, 
                'message' => "Alumni $actionText successfully"
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Alumni not found or already processed']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Action failed: ' . $e->getMessage()]);
    }
}
?>