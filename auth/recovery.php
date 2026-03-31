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
    <title>Password Recovery | WikkHistory</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body {
            font-family: 'Titillium Web', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('../img/background.jpg');
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }

        .card { 
            display: flex;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 50px 100px rgba(0,0,0,0.8);
            overflow: hidden;
            width: 550px;
            height: 350px;
            padding: 40px 60px;
            align-items: stretch;
            justify-content: center;
        }

        h3, p, .footer-links {
            text-align: center;
        }

        h3 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            font-weight: 700;
            color: #fff;
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
        }

        .input-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #aa8b56;
            font-size: 1.1rem;
        }

        .input-group input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px 20px 15px 55px;
            border-radius: 12px;
            color: white;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #aa8b56;
            outline: none;
            box-shadow: 0 0 20px rgba(170, 139, 86, 0.2);
        }

        button { 
            width: 100%;
            background: #aa8b56;
            color: #1a1212;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1.3rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            margin-top: 5px;
            font-family: 'Titillium Web', sans-serif;
        }

        button:hover {
            background: #1a1212;
            color: #aa8b56;
            border: #aa8b56 1px solid;
        }

        .footer-links {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.6);
        }

        .footer-links a {
            color: #aa8b56;
            text-decoration: none;
            font-weight: 700;
        }

        .footer-links a:hover{
            color: #fff;
        }

    </style>
</head>
<body>
    <div class="card">
        <h3>Password Recovery</h3>
        <p><?php echo $msg; ?></p>
        <form method="POST">
            <div class="input-group">
                <i class="fa fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <button type="submit">SEND RECOVERY LINK</button>
        </form>
        <div class="footer-links">
            You remember your password now? <a href="../index.php">Go back to login!</a>
        </div>
    </div>
</body>
</html>