<?php
// api/alumni_events.php
header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

// Check if user is logged in and is alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'alumni') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        handleGetEvents($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function handleGetEvents($pdo) {
    // Get parameters
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    try {
        // Update event statuses first
        updateEventStatuses($pdo);
        
        // Base query - get all events with creator info
        $baseQuery = "FROM events e 
                     INNER JOIN users u ON e.created_by = u.id
                     LEFT JOIN alumni a ON u.id = a.user_id";
        $conditions = [];
        $params = [];

        // Status filter
        if ($status !== 'all') {
            $conditions[] = "e.status = ?";
            $params[] = $status;
        }

        // Search condition (Title only)
        if (!empty($search)) {
            $conditions[] = "e.title LIKE ?";
            $params[] = "%$search%";
        }

        // Add conditions to base query
        $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";
        $fullBaseQuery = $baseQuery . $whereClause;

        // Get events ordered by status priority (upcoming first, then live, then recent past)
        $dataQuery = "SELECT e.id, e.title, e.details, e.event_date, e.location, 
                            e.status, e.created_at, e.image_url, e.registration_link,
                            u.email as created_by_email, 
                            CONCAT(COALESCE(a.first_name, ''), ' ', COALESCE(a.last_name, '')) as created_by_name,
                            CASE 
                                WHEN e.status = 'upcoming' THEN 1
                                WHEN e.status = 'live' THEN 2  
                                WHEN e.status = 'past' THEN 3
                            END as status_priority
                     " . $fullBaseQuery . "
                     ORDER BY status_priority ASC, 
                              CASE WHEN e.status = 'past' THEN e.event_date END DESC,
                              CASE WHEN e.status != 'past' THEN e.event_date END ASC
                     LIMIT $limit";
        
        $dataStmt = $pdo->prepare($dataQuery);
        $dataStmt->execute($params);
        $events = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get counts for different statuses
        $countsQuery = "SELECT 
                           COUNT(*) as total,
                           SUM(CASE WHEN e.status = 'upcoming' THEN 1 ELSE 0 END) as upcoming,
                           SUM(CASE WHEN e.status = 'live' THEN 1 ELSE 0 END) as live,
                           SUM(CASE WHEN e.status = 'past' THEN 1 ELSE 0 END) as past
                       " . $fullBaseQuery;
        
        $countsStmt = $pdo->prepare($countsQuery);
        $countsStmt->execute($params);
        $counts = $countsStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $events,
            'counts' => $counts,
            'filters' => [
                'status' => $status,
                'search' => $search
            ]
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateEventStatuses($pdo) {
    try {
        $currentDate = date('Y-m-d');
        
        // Update to past
        $pdo->prepare("UPDATE events SET status = 'past' WHERE event_date < ? AND status != 'past'")->execute([$currentDate]);
        
        // Update to live
        $pdo->prepare("UPDATE events SET status = 'live' WHERE event_date = ? AND status != 'live'")->execute([$currentDate]);
        
        // Update to upcoming
        $pdo->prepare("UPDATE events SET status = 'upcoming' WHERE event_date > ? AND status != 'upcoming'")->execute([$currentDate]);
        
    } catch (Exception $e) {
        // Log error but don't break the main functionality
        error_log('Failed to update event statuses: ' . $e->getMessage());
    }
}
?>