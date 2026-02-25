<?php
session_start();
require 'database.php';

$user_id = $_SESSION['user_id'] ?? 0;
$monument_id = $_GET['id'] ?? '';

$stmt = $pdo->prepare("SELECT note_text, rating FROM notes WHERE user_id = ? AND monument_id = ?");
$stmt->execute([$user_id, $monument_id]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'note' => $note]);