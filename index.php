<?php
session_start();
require 'settings/database.php'; 

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST['user'];
    $password_input = $_POST['password'];

    if (!empty($user_input) && !empty($password_input)) {
        // Selezioniamo anche nome e cognome nella query
        $stmt = $pdo->prepare("SELECT id, username, email, password, nome, cognome FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$user_input, $user_input]);
        $user = $stmt->fetch();

        if ($user && password_verify($password_input, $user['password'])) {
            // Salviamo tutto in sessione
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nome'] = $user['nome'];       // <--- Fondamentale
            $_SESSION['cognome'] = $user['cognome']; // <--- Fondamentale
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error_msg = "Credenziali errate.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | WikkHistory</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* RESET & BASE */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Titillium Web', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), 
                        url('https://images.unsplash.com/photo-1552832230-c0197dd311b5?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }

        /* CARD PRINCIPALE */
        .login-card {
            display: flex;
            width: 900px;
            height: 550px;
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
            width: 120px; 
            margin-bottom: 20px; 
            filter: invert(1) sepia(1) saturate(5) hue-rotate(10deg); 
        }

        .brand-text {
            font-size: 2rem;
            font-weight: 700;
            color: #aa8b56;
            letter-spacing: 5px;
            text-shadow: 0 0 15px rgba(170, 139, 86, 0.5);
            text-align: center;
        }

        .right-panel {
            flex: 1.2;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }

        .right-panel h2 {
            font-size: 2.8rem;
            margin-bottom: 40px;
            font-weight: 700;
            color: #fff;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #aa8b56;
            font-size: 1.2rem;
        }

        .input-group input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 18px 20px 18px 60px;
            border-radius: 15px;
            color: white;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #aa8b56;
            outline: none;
            box-shadow: 0 0 20px rgba(170, 139, 86, 0.2);
        }

        .login-btn {
            background: #aa8b56;
            color: #1a1212;
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 10px;
        }

        .login-btn:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(170, 139, 86, 0.4);
        }

        .footer-links {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.6);
        }

        .footer-links a {
            color: #aa8b56;
            text-decoration: none;
            font-weight: 700;
        }

        @media (max-width: 850px) {
            .login-card { flex-direction: column; width: 90%; height: auto; }
            .left-panel { border-right: none; border-bottom: 1px solid rgba(170,139,86,0.2); }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="left-panel">
            <img src="https://cdn-icons-png.flaticon.com/512/2232/2232688.png" alt="WikkHistory" class="logo-icon">
            <div class="brand-text">WIKKHISTORY</div>
        </div>

        <div class="right-panel">
            <h2>Accedi</h2>
            
            <?php if ($error_msg): ?>
                <p style="color: #ff6b6b; margin-bottom: 20px; font-weight: bold;"><?php echo $error_msg; ?></p>
            <?php endif; ?>

            <form method="POST" style="display: flex; flex-direction: column;">
                <div class="input-group">
                    <i class="fa fa-user"></i>
                    <input type="text" name="user" placeholder="Username o Email" required>
                </div>

                <div class="input-group">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="login-btn">Entra</button>
            </form>

            <div class="footer-links">
                Password dimenticata? <a href="#">Recuperala</a><br><br>
                Nuovo utente? <a href="auth/register.php">Registrati qui</a>
            </div>
        </div>
    </div>

</body>
</html>