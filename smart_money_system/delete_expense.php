<?php
require 'db_connect.php';
session_start();
$user_id = $_SESSION['user_id'] ?? null;

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;

header('Content-Type: application/json');

if (!$user_id) {
    echo json_encode(['success'=>false,'error'=>'Not authenticated']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM expenses WHERE id = ? AND user_id = ?');
$res = $stmt->execute([$id, $user_id]);
echo json_encode(['success' => (bool)$res]);
?>