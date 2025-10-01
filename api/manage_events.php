<?php

header('Content-Type: application/json');
require_once '../config/database.php';
session_start();

// Check if user is logged in and is admin or directorate
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'directorate'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        handleGetEvents($pdo);
        break;
    case 'POST':
        handleCreateEvent($pdo);
        break;
    case 'PUT':
        handleUpdateEvent($pdo);
        break;
    case 'DELETE':
        handleDeleteEvent($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function handleGetEvents($pdo) {
    // Get search parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $offset = ($page - 1) * $limit;

    try {
        // Base query
        $baseQuery = "FROM events e 
                     INNER JOIN users u ON e.created_by = u.id";
        $conditions = [];
        $params = [];

        // Search condition (Title only)
        if (!empty($search)) {
            $conditions[] = "e.title LIKE ?";
            $params[] = "%$search%";
        }

        // Add conditions to base query
        $whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";
        $fullBaseQuery = $baseQuery . $whereClause;

        // Get total count
        $countQuery = "SELECT COUNT(*) as total " . $fullBaseQuery;
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetch()['total'];

        // Update event statuses based on current date
        updateEventStatuses($pdo);

        // Get data with pagination - newest events first
        $dataQuery = "SELECT e.id, e.title, e.details, e.event_date, e.location, 
                            e.status, e.created_at, e.image_url, e.registration_link,
                            u.email as created_by_email, 
                            CONCAT(COALESCE(a.first_name, ''), ' ', COALESCE(a.last_name, '')) as created_by_name
                     " . $fullBaseQuery . "
                     LEFT JOIN alumni a ON u.id = a.user_id
                     ORDER BY e.created_at DESC 
                     LIMIT $limit OFFSET $offset";
        
        $dataStmt = $pdo->prepare($dataQuery);
        $dataStmt->execute($params);
        $events = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate pagination info
        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            'success' => true,
            'data' => $events,
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

function handleCreateEvent($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['title', 'details', 'event_date', 'location'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
            return;
        }
    }

    try {
        // Determine status based on event date
        $eventDate = new DateTime($input['event_date']);
        $currentDate = new DateTime();
        $status = $eventDate > $currentDate ? 'upcoming' : ($eventDate->format('Y-m-d') === $currentDate->format('Y-m-d') ? 'live' : 'past');

        $stmt = $pdo->prepare("
            INSERT INTO events (title, details, event_date, location, status, created_by, image_url, registration_link) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $input['title'],
            $input['details'],
            $input['event_date'],
            $input['location'],
            $status,
            $_SESSION['user_id'],
            $input['image_url'] ?? null,
            $input['registration_link'] ?? null
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Event created successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create event']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleUpdateEvent($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id'])) {
        echo json_encode(['success' => false, 'error' => 'Event ID is required']);
        return;
    }

    // Validate required fields
    $required = ['title', 'details', 'event_date', 'location'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
            return;
        }
    }

    try {
        // Determine status based on event date
        $eventDate = new DateTime($input['event_date']);
        $currentDate = new DateTime();
        $status = $eventDate > $currentDate ? 'upcoming' : ($eventDate->format('Y-m-d') === $currentDate->format('Y-m-d') ? 'live' : 'past');

        $stmt = $pdo->prepare("
            UPDATE events 
            SET title = ?, details = ?, event_date = ?, location = ?, status = ?, 
                image_url = ?, registration_link = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $input['title'],
            $input['details'],
            $input['event_date'],
            $input['location'],
            $status,
            $input['image_url'] ?? null,
            $input['registration_link'] ?? null,
            $input['id']
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update event']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDeleteEvent($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id'])) {
        echo json_encode(['success' => false, 'error' => 'Event ID is required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $result = $stmt->execute([$input['id']]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete event']);
        }

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