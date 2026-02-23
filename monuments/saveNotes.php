<?php
require '../settings/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $m_id = $_POST['monument_id'];
    $content = $_POST['notes_content'];

    $sql = "INSERT INTO historical_notes (monument_id, notes_content) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE notes_content = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$m_id, $content, $content]);
    
    echo json_encode(['status' => 'success']);
}
?>