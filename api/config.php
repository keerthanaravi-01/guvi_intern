<?php
// api/config.php
header('Content-Type: application/json; charset=utf-8');

// MySQL (PDO)
$MYSQL_HOST = '127.0.0.1';
$MYSQL_DB   = 'guvi_intern';
$MYSQL_USER = 'root';
$MYSQL_PASS = ''; 

try {
    $pdo = new PDO("mysql:host={$MYSQL_HOST};dbname={$MYSQL_DB};charset=utf8mb4",
                   $MYSQL_USER, $MYSQL_PASS, [
                     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                   ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'DB connection error: '.$e->getMessage()]);
    exit;
}

// Predis (autoload via composer)
// autoload may not exist in some environments (dev/test). Load it if present.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    try {
        $redis = new Predis\Client(); // default localhost:6379
    } catch (Exception $e) {
        $redis = null;
    }
} else {
    $redis = null;
}

// MongoDB manager (if extension installed)
$mongoManager = null;
if (class_exists('MongoDB\\Driver\\Manager')) {
    try {
        $mongoManager = new MongoDB\Driver\Manager("mongodb://127.0.0.1:27017");
    } catch (Exception $e) {
        $mongoManager = null;
    }
}