<?php
require '../settings/database.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $token = bin2hex(random_bytes(32));
        $pdo->prepare("UPDATE users SET reset_token = ? WHERE email = ?")->execute([$token, $email]);
        // Qui andrebbe la funzione mail()
        $msg = "Se l'email esiste, abbiamo inviato un link di recupero (Simulato).";
    } else {
        $msg = "Email non trovata.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recupero Password</title>
    <style>
        body { font-family: 'Titillium Web', sans-serif; background: #2c2c2c; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; color: white;}
        .card { background: #aa8b56; padding: 40px; border-radius: 30px; text-align: center; width: 350px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 20px; border: none; box-sizing: border-box;}
        button { width: 100%; padding: 12px; border-radius: 20px; border: none; background: #1a1212; color: white; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h3>Recupera Password</h3>
        <p><?php echo $msg; ?></p>
        <form method="POST">
            <input type="email" name="email" placeholder="Inserisci la tua email" required>
            <button type="submit">Invia Link</button>
        </form>
        <br><a href="../index.php" style="color:white;">Torna al Login</a>
    </div>
</body>
</html>