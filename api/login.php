<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success'=>false,'message'=>'Missing email or password']);
    exit;
}

$stmt = $pdo->prepare("SELECT id,password FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid credentials']);
    exit;
}

$userId = (int)$user['id'];
$token = bin2hex(random_bytes(32));

// store session in redis (key: session:<token> => userId)
if ($redis) {
    $redis->setex("session:{$token}", 60*60*24, $userId); // 24 hours expiry
} else {
    // fallback: do not allow login if redis required by task
    echo json_encode(['success'=>false,'message'=>'Redis not available on server']);
    exit;
}

echo json_encode(['success'=>true,'token'=>$token]);