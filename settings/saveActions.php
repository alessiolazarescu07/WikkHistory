<?php
session_start();
require '../settings/database.php';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action_text'];
    $monument = $_POST['monument_id'];

    $stmt = $pdo->prepare("INSERT INTO recent_activities (user_id, action_text, monument_id) VALUES (?, ?, ?)");
    if ($stmt->execute([$user_id, $action, $monument])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>