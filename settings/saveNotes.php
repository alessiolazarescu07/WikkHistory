<?php
session_start();
header('Content-Type: application/json');
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['nome'] . " " . $_SESSION['cognome'];
    $monument_id = $_POST['monument_id'] ?? '';
    $monument_name = $_POST['monument_name'] ?? '';
    $note_text = trim($_POST['note_text'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);

    if (empty($note_text) || $rating === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Incomplete data']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO notes (monument_id, monument_name, user_id, user_name, note_text, rating) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$monument_id, $monument_name, $user_id, $user_name, $note_text, $rating]);

        $action = "You added a note for: " . $monument_name;
        $stmtAct = $pdo->prepare("INSERT INTO recent_activities (user_id, action_description) VALUES (?, ?)");
        $stmtAct->execute([$user_id, $action]);

        $pdo->commit();

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Saving error: ' . $e->getMessage()]);
    }
}
?>