<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$user_id = $_SESSION['user_id'];
$monument_id = $_POST['monument_id'];
$note_text = $_POST['note_text'];
$rating = (int)$_POST['rating'];
$monument_name = $_POST['monument_name'];

try {
    // 1. Inserisce o aggiorna l'appunto
    $sql = "INSERT INTO notes (user_id, monument_id, note_text, rating) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE note_text = VALUES(note_text), rating = VALUES(rating)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $monument_id, $note_text, $rating]);

    // 2. Registra l'attività (per il feed della dashboard)
    $action_desc = "Updated notes for $monument_name (Rating: $rating/5)";
    $stmt_act = $pdo->prepare("INSERT INTO recent_activities (action_description, created_at) VALUES (?, NOW())");
    $stmt_act->execute([$action_desc]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}