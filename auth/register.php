<?php
require '../settings/database.php';
$msg = "";
$status = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $conf_pass = $_POST['confirm_password'];

    if ($pass !== $conf_pass) {
        $msg = "Le password non coincidono!";
        $status = "error";
    } else {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        // Query aggiornata con nome e cognome
        $stmt = $pdo->prepare("INSERT INTO users (nome, cognome, username, email, password) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$nome, $cognome, $user, $email, $hashed_pass]);
            $msg = "Registrazione completata! <a href='../index.php' style='color:#fff;'>Accedi ora</a>";
            $status = "success";
        } catch (Exception $e) {
            $msg = "Errore: Username o Email già esistenti.";
            $status = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | WikkHistory</title>
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

        .register-card {
            display: flex;
            width: 950px;
            height: 650px; /* Aumentata leggermente per i nuovi campi */
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 50px 100px rgba(0,0,0,0.8);
            overflow: hidden;
        }

        .left-panel {
            flex: 1;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            border-right: 1px solid rgba(170, 139, 86, 0.2);
        }

        .logo-icon { 
            width: 100%; 
        }

        .right-panel {
            flex: 1.5;
            padding: 30px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }

        .right-panel h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            font-weight: 700;
            color: #fff;
        }

        .input-row {
            display: flex;
            gap: 15px;
        }

        .input-group {
            position: relative;
            margin-bottom: 15px;
            flex: 1;
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

        .register-btn {
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

        .register-btn:hover {
            background: #1a1212;
            color: #aa8b56;
            border: #aa8b56 1px solid;
        }

        .msg {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .msg.error {
            color: #ff6b6b; 
            border: 1px solid #ff6b6b; 
            background: rgba(81, 207, 102, 0.1); 
        }

        .msg.success { 
            color: #51cf66; 
            border: 1px solid #51cf66; 
            background: rgba(81, 207, 102, 0.1); 
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

        @media (max-width: 850px) {
            .register-card { flex-direction: column; width: 90%; height: auto; margin-top: 20px; overflow-y: auto; }
            .left-panel { border-right: none; border-bottom: 1px solid rgba(170,139,86,0.2); padding: 30px; }
            .input-row { flex-direction: column; gap: 0; }
        }
    </style>
</head>
<body>

    <div class="register-card">
        <div class="left-panel">
            <img src="../img/logo.png" alt="WikkHistory" class="logo-icon">
        </div>

        <div class="right-panel">
            <h2>Create Account</h2>
            
            <?php if ($msg): ?>
                <div class="msg <?php echo $status; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-row">
                    <div class="input-group">
                        <i class="fa fa-id-card"></i>
                        <input type="text" name="nome" placeholder="First Name" required>
                    </div>
                    <div class="input-group">
                        <i class="fa fa-id-card"></i>
                        <input type="text" name="cognome" placeholder="Last Name" required>
                    </div>
                </div>

                <div class="input-group">
                    <i class="fa fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>

                <div class="input-group">
                    <i class="fa fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-group">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="input-group">
                    <i class="fa fa-circle-check"></i>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>

                <button type="submit" class="register-btn">REGISTER</button>
            </form>

            <div class="footer-links">
                You look  familiar... <a href="../index.php">Sign In</a>
            </div>
        </div>
    </div>

</body>
</html>