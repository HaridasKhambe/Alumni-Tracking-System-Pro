<?php
// api/alumni_search.php
header('Content-Type: application/json');
require_once '../config/database.php';

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$employment_status = isset($_GET['employment_status']) ? $_GET['employment_status'] : '';
$passout_year = isset($_GET['passout_year']) ? $_GET['passout_year'] : '';
$company_name = isset($_GET['company_name']) ? $_GET['company_name'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;

// Calculate offset
$offset = ($page - 1) * $limit;

try {
    // Base query with JOIN
    $baseQuery = "FROM alumni a 
                  INNER JOIN users u ON a.user_id = u.id 
                  WHERE u.status = 'active'";
    
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
    
    // Employment Status filter
    if (!empty($employment_status)) {
        $conditions[] = "a.employment_status = ?";
        $params[] = $employment_status;
    }

    // Passout Year filter
    if (!empty($passout_year)) {
        $conditions[] = "a.passout_year = ?";
        $params[] = $passout_year;
    }

    // Company Name filter  
    if (!empty($company_name)) {
        $conditions[] = "a.company_name LIKE ?";
        $params[] = "%$company_name%";
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
                    u.email
                  " . $fullBaseQuery . "
                  ORDER BY a.first_name ASC, a.last_name ASC
                   LIMIT $limit OFFSET $offset";
    
    // $params[] = $limit;
    // $params[] = $offset;
    
    $dataStmt = $pdo->prepare($dataQuery);
    $dataStmt->execute($params);
    $alumni = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate pagination info
    $totalPages = ceil($totalRecords / $limit);
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    // Response
    echo json_encode([
        'success' => true,
        'data' => $alumni,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'passout_year' => $passout_year,
            'company_name' => $company_name,
            'limit' => $limit,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev
        ],
        'filters' => [
            'search' => $search,
            'branch' => $branch,
            'employment_status' => $employment_status
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>