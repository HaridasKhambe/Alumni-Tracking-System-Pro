<?php
// api/notices.php
header('Content-Type: application/json');
require_once '../config/database.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Fetch notices for current user
        $query = "SELECT id, sender_id, title, message, type, status, expiry_date, created_at 
                  FROM notices 
                  WHERE recipient_id = ? 
                  ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $notices = [];
        foreach ($rows as $row) {
            // Calculate expiry status
            $expiry_info = null;
            if ($row['expiry_date']) {
                $expiry_date = new DateTime($row['expiry_date']);
                $current_date = new DateTime();
                $diff = $current_date->diff($expiry_date);
                
                if ($expiry_date < $current_date) {
                    $expiry_info = [
                        'expired' => true,
                        'text' => 'Expired ' . $diff->days . ' days ago'
                    ];
                } else {
                    $expiry_info = [
                        'expired' => false,
                        'text' => 'Expires in ' . $diff->days . ' days'
                    ];
                }
            }
            
            $notices[] = [
                'id' => $row['id'],
                'sender_id' => $row['sender_id'],
                'title' => $row['title'],
                'message' => $row['message'],
                'preview' => strlen($row['message']) > 80 ? substr($row['message'], 0, 80) . '...' : $row['message'],
                'type' => $row['type'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'expiry_date' => $row['expiry_date'],
                'expiry_info' => $expiry_info
            ];
        }
        
        echo json_encode(['success' => true, 'notices' => $notices]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Update notice status
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['notice_id']) || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        $notice_id = $input['notice_id'];
        $status = $input['status'];
        
        // Validate status
        if (!in_array($status, ['accepted', 'rejected'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        // Update status only if notice belongs to current user and is pending
        $query = "UPDATE notices 
                  SET status = ? 
                  WHERE id = ? AND recipient_id = ? AND status = 'pending'";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$status, $notice_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Notice ' . $status . ' successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update notice or notice not found'
            ]);
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>