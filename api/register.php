<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$age      = $_POST['age'] ?? null;
$dob      = $_POST['dob'] ?? null;
$contact  = $_POST['contact'] ?? null;

if (!$username || !$email || !$password) {
    echo json_encode(['success'=>false,'message'=>'Please fill required fields (username, email, password)']);
    exit;
}

// check if email exists and insert inside try/catch to handle DB errors gracefully
try {
    // check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success'=>false,'message'=>'Email already registered']);
        exit;
    }

    // insert (prepared statement)
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username,email,password,age,dob,contact) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$username,$email,$hash, $age ?: null, $dob ?: null, $contact ?: null]);
    $userId = $pdo->lastInsertId();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
    exit;
}

// optional: insert into MongoDB (if driver present)
if ($mongoManager) {
    $bulk = new MongoDB\Driver\BulkWrite;
    $doc = [
        'user_id' => (int)$userId,
        'username' => $username,
        'email' => $email,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    try {
        $bulk->insert($doc);
        $mongoManager->executeBulkWrite('guvi.users', $bulk);
    } catch (Exception $e) {
        // ignore mongo errors for now
    }
}

echo json_encode(['success'=>true,'message'=>'Registration successful']);