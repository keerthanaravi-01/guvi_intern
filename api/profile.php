<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

$token = $_POST['token'] ?? $_GET['token'] ?? '';
if (!$token) {
    echo json_encode(['success'=>false,'message'=>'Missing token']);
    exit;
}

if (!$redis) {
    echo json_encode(['success'=>false,'message'=>'Redis not available']);
    exit;
}

$userId = $redis->get("session:{$token}");
if (!$userId) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized or session expired']);
    exit;
}

$stmt = $pdo->prepare("SELECT id,username,email,age,dob,contact FROM users WHERE id = ? LIMIT 1");
$stmt->execute([(int)$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(['success'=>false,'message'=>'User not found']);
    exit;
}

echo json_encode(['success'=>true,'user'=>$user]);