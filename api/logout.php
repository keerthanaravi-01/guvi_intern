<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

$token = $_POST['token'] ?? '';
if ($token && $redis) {
    $redis->del("session:{$token}");
}
echo json_encode(['success'=>true]);