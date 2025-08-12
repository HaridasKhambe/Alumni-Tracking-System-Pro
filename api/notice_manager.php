<?php
// api/notice_manager.php
header('Content-Type: application/json');
require_once '../config/database.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'directorate'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$current_user_id = $_SESSION['user_id'];

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        handleGetNotices($pdo, $current_user_id);
        break;
    case 'PUT':
        handleUpdateNotice($pdo, $current_user_id);
        break;
    case 'DELETE':
        handleDeleteNotice($pdo, $current_user_id);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function handleGetNotices($pdo, $current_user_id) {
    // Get search parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    
    $offset = ($page - 1) * $limit;
    
    try {
        // Base query - notices created by current user only
        $baseQuery = "FROM notices n 
                      INNER JOIN users u ON n.recipient_id = u.id 
                      INNER JOIN alumni a ON u.id = a.user_id 
                      WHERE n.sender_id = ?";
        
        $conditions = [];
        $params = [$current_user_id];
        
        // Search condition (Name OR PRN)
        if (!empty($search)) {
            $conditions[] = "(CONCAT(a.first_name, ' ', a.last_name) LIKE ? OR a.prn_no LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Status filter
        if (!empty($status)) {
            $conditions[] = "n.status = ?";
            $params[] = $status;
        }
        
        // Add conditions to base query
        $whereClause = !empty($conditions) ? " AND " . implode(" AND ", $conditions) : "";
        $fullBaseQuery = $baseQuery . $whereClause;
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total " . $fullBaseQuery;
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetch()['total'];
        
        // Get data with pagination
        $dataQuery = "SELECT 
                        n.id,
                        n.title,
                        n.message,
                        n.type,
                        n.status,
                        n.expiry_date,
                        n.created_at,
                        a.first_name,
                        a.last_name,
                        a.prn_no,
                        u.email
                      " . $fullBaseQuery . "
                      ORDER BY n.created_at DESC
                      LIMIT $limit OFFSET $offset";
        
        $dataStmt = $pdo->prepare($dataQuery);
        $dataStmt->execute($params);
        $notices = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate pagination info
        $totalPages = ceil($totalRecords / $limit);
        
        echo json_encode([
            'success' => true,
            'data' => $notices,
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
                'status' => $status
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleUpdateNotice($pdo, $current_user_id) {
    
    $input = json_decode(file_get_contents('php://input'), true);
    $notice_id = $input['id'] ?? '';
    $title = isset($input['title']) ? trim($input['title']) : '';
    $message = isset($input['message']) ? trim($input['message']) : '';
    $type = $input['type'] ?? '';
    $status = $input['status'] ?? '';
    $expiry_date = !empty($input['expiry_date']) ? $input['expiry_date'] : null;
    
    // Validation
    if (empty($notice_id) || empty($title) || empty($message) || empty($type) || empty($status)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    if (!in_array($status, ['pending', 'accepted', 'rejected'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        return;
    }
    
    if (!in_array($type, ['invitation', 'event', 'reminder', 'general'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid type']);
        return;
    }
    
    try {
        $query = "UPDATE notices SET title = ?, message = ?, type = ?, status = ?, expiry_date = ? 
                  WHERE id = ? AND sender_id = ?";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([$title, $message, $type, $status, $expiry_date, $notice_id, $current_user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Notice updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Notice not found or unauthorized']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Update failed: ' . $e->getMessage()]);
    }
}

function handleDeleteNotice($pdo, $current_user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $notice_id = $input['id'] ?? '';
    
    if (empty($notice_id)) {
        echo json_encode(['success' => false, 'error' => 'Notice ID required']);
        return;
    }
    
    try {
        // Delete only notices created by current user
        $query = "DELETE FROM notices WHERE id = ? AND sender_id = ?";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([$notice_id, $current_user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Notice deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Notice not found or unauthorized']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Delete failed: ' . $e->getMessage()]);
    }
}
?>