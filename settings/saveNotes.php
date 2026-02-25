<?php
session_start();
require '../settings/database.php'; // Percorso corretto per uscire dalla cartella monuments

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non autorizzato']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $monument_id = $_POST['monument_id'];
    $monument_name = $_POST['monument_name'];
    $note_text = $_POST['note_text'];
    $rating = (int)$_POST['rating'];

    try {
        // 1. Salva o aggiorna la nota
        $stmt = $pdo->prepare("INSERT INTO notes (user_id, monument_id, note_text, rating) 
                               VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE note_text = ?, rating = ?, updated_at = CURRENT_TIMESTAMP");
        $stmt->execute([$user_id, $monument_id, $note_text, $rating, $note_text, $rating]);

        // 2. Registra l'attività recente
        $desc = "Hai aggiornato le note per: " . $monument_name;
        $stmtAct = $pdo->prepare("INSERT INTO recent_activities (user_id, action_description) VALUES (?, ?)");
        $stmtAct->execute([$user_id, $desc]);

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}