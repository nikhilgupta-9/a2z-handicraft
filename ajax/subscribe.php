<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/util/function.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$email = filter_var(request_string($_POST, 'email'), FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

global $pdo;

try {
    // Check if already subscribed recently
    $checkStmt = $pdo->prepare("SELECT id FROM leads WHERE email = ? AND subject = 'Newsletter Subscription' LIMIT 1");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => true, 'message' => 'You are already subscribed to our newsletter!']);
        exit;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, subject, message, status, is_read, ip_address) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
    $stmt->execute([
        'Newsletter Subscriber',
        $email,
        '',
        'Newsletter Subscription',
        'Subscribed via website footer newsletter form.',
        'new',
        $ip
    ]);

    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to A2Z Handicraft updates!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save subscription. Please try again.']);
}
