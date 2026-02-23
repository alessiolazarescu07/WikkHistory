<?php
require '../settings/database.php';

// Funzione per aggiungere un utente in modo sicuro
function aggiungiUtente($pdo, $username, $email, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$username, $email, $hashed_password]);
        echo "Utente <strong>$username</strong> creato con successo!<br>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "Errore: L'utente <strong>$username</strong> o l'email esistono già.<br>";
        } else {
            echo "Errore: " . $e->getMessage() . "<br>";
        }
    }
}

// Creazione credenziali di default
aggiungiUtente($pdo, 'admin', 'admin@wikkhistory.it', 'admin');

// Puoi aggiungere altri utenti qui sotto se vuoi
// aggiungiUtente($pdo, 'mario', 'mario@test.it', '12345');

echo "<br><a href='../index.php'>Vai al Login</a>";
?>