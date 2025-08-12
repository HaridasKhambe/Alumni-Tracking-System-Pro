<?php
// ATS/api/auth.php - Fixed version

// Configure session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

session_start();

// FIX 1: Set proper headers first, before any output
header('Content-Type: application/json; charset=utf-8');

// FIX 2: For development, allow specific origin instead of *
$allowed_origin = 'http://localhost';
if (isset($_SERVER['HTTP_ORIGIN']) && strpos($_SERVER['HTTP_ORIGIN'], $allowed_origin) === 0) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// FIX 3: Error handling for database connection
try {
    require_once __DIR__ . '/../config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// FIX 4: Better error handling for invalid methods
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

if (!$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action parameter required']);
    exit;
}

//----------- logout -----------------
if ($action === 'logout') {
    session_start();
    session_unset();
    session_destroy();
    http_response_code(200);
    exit;
}

// FIX 5: Better JSON input handling
$json_input = file_get_contents('php://input');
if (empty($json_input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$input = json_decode($json_input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

/* ---------- REGISTER ---------- */
if ($action === 'register') {
    $required = ['first_name','last_name','email','phone','prn_no','branch','passout_year','employment_status','password','confirm_password'];
    $errors = [];

    // Check required fields
    foreach ($required as $k) {
        if (empty($input[$k])) {
            $errors[] = ucfirst(str_replace('_', ' ', $k)) . " is required";
        }
    }

    // Basic validations
    if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (!empty($input['phone']) && !preg_match('/^[0-9]{10}$/', $input['phone'])) {
        $errors[] = "Phone must be exactly 10 digits";
    }
    if (!empty($input['password']) && strlen($input['password']) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (isset($input['password'], $input['confirm_password']) && $input['password'] !== $input['confirm_password']) {
        $errors[] = "Passwords do not match";
    }

    // FIX 6: Return errors early if validation fails
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors, 'message' => 'Validation failed']);
        exit;
    }

    // Sanitize fields
    $first_name = trim($input['first_name']);
    $last_name  = trim($input['last_name']);
    $email      = trim($input['email']);
    $phone      = trim($input['phone']);
    $prn_no     = trim($input['prn_no']);
    $branch     = trim($input['branch']);
    $passout_year = (int)$input['passout_year'];
    $employment_status = $input['employment_status'];
    $company_name = $input['employment_status'] === 'unemployed' ? null : trim($input['company_name'] ?? '');
    $password = $input['password'];

    try {
        // FIX 7: Check if $pdo exists
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        $pdo->beginTransaction();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }

        // Check if PRN already exists
        $stmt = $pdo->prepare("SELECT id FROM alumni WHERE prn_no = ?");
        $stmt->execute([$prn_no]);
        if ($stmt->fetch()) {
            throw new Exception('PRN number already registered');
        }

        // Create user
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, 'alumni', 'pending')");
        $stmt->execute([$email, $hashed]);
        $user_id = $pdo->lastInsertId();

        // Create alumni record
        $stmt = $pdo->prepare("INSERT INTO alumni (user_id, prn_no, first_name, last_name, branch, passout_year, phone, employment_status, company_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $prn_no, $first_name, $last_name, $branch, $passout_year, $phone, $employment_status, $company_name]);

        $pdo->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Registration successful! Your account is pending admin approval.'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

/* ---------- LOGIN ---------- */
if ($action === 'login') {
    // Handle both JSON and form POST
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input)) {
        $input = $_POST;
    }

    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }

    try {
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }

        // $stmt = $pdo->prepare("SELECT id, password, role, status FROM users WHERE email = ?");
        $stmt = $pdo->prepare("
            SELECT u.id, u.password, u.role, u.status, a.first_name, a.last_name
            FROM users u
            LEFT JOIN alumni a ON u.id = a.user_id
            WHERE u.email = ?
        ");

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // var_dump(password_verify($password, $user['password']));

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit;
        }

        if ($user['status'] !== 'active') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Account is not active. Please contact admin.']);
            exit;
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            $_SESSION['user_name'] = 'Admin';
        }else if($user['role'] === 'directorate'){
            $_SESSION['user_name'] = 'Directorate';
        } else {
            $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $_SESSION['user_name'] = $fullName ?: 'User';  // fallback 'User' if empty
        }

        // TO:
        $redirectUrl = 'modules/alumni/dashboard.php';
        if ($user['role'] === 'admin') {
            $redirectUrl = 'modules/admin/dashboard.php';
        }else if($user['role'] === 'directorate'){
            $redirectUrl = 'modules/directorate/dashboard.php';
        }

        // If AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => $redirectUrl]);
        } else {
            // Normal form submit
            header("Location: $redirectUrl");
        }
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
}



// Unknown action
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
?>