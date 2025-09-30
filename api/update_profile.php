<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

$token = $_POST['token'] ?? '';
if (!$token) { echo json_encode(['success'=>false,'message'=>'Missing token']); exit; }
if (!$redis) { echo json_encode(['success'=>false,'message'=>'Redis not available']); exit; }

$userId = $redis->get("session:{$token}");
if (!$userId) { echo json_encode(['success'=>false,'message'=>'Session expired']); exit; }

$age = $_POST['age'] ?? null;
$dob = $_POST['dob'] ?? null;
$contact = $_POST['contact'] ?? null;

$stmt = $pdo->prepare("UPDATE users SET age = ?, dob = ?, contact = ? WHERE id = ?");
$stmt->execute([$age ?: null, $dob ?: null, $contact ?: null, (int)$userId]);

// optional: log to MongoDB
if ($mongoManager) {
    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert(['user_id'=>(int)$userId, 'action'=>'update_profile', 'ts'=>new MongoDB\BSON\UTCDateTime()]);
        $mongoManager->executeBulkWrite('guvi.activity_logs', $bulk);
    } catch (Exception $e) { /* ignore */ }
}

echo json_encode(['success'=>true,'message'=>'Profile updated']);