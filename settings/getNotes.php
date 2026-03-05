<?php
// settings/getNotes.php
header('Content-Type: application/json');
require 'database.php';

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID monumento mancante']);
    exit();
}

$monument_id = $_GET['id'];

try {
    // Recuperiamo le note ordinate dalla più recente
    $stmt = $pdo->prepare("SELECT user_name, note_text, rating, DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as created_at 
                           FROM notes 
                           WHERE monument_id = ? 
                           ORDER BY id DESC");
    $stmt->execute([$monument_id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'notes' => $notes
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Errore database: ' . $e->getMessage()]);
}
?>