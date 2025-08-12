<?php

session_start();
header('Content-Type: application/json');
require_once '../config/database.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$sender_id    = $_SESSION['user_id'] ?? null;
$recipient_id = intval($_POST['noticeReceiverId'] ?? 0);
$title        = trim($_POST['title'] ?? '');
$message      = trim($_POST['message'] ?? '');
$type         = $_POST['type'] ?? 'general';
$expiry_date  = $_POST['expiry_date'] ?? null;

if (!$sender_id || !$recipient_id || !$title || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO notices (sender_id, recipient_id, title, message, type, expiry_date) 
        VALUES (:sender_id, :recipient_id, :title, :message, :type, :expiry_date)
    ");
    
    $success = $stmt->execute([
        ':sender_id'    => $sender_id,
        ':recipient_id' => $recipient_id,
        ':title'        => $title,
        ':message'      => $message,
        ':type'         => $type,
        ':expiry_date'  => $expiry_date ?: null
    ]);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Notice sent successfully.' : 'Failed to send notice.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Database error: ' . $e->getMessage()
    ]);
}
