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
            $error_msg = "Wrong credentials. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | WikkHistory</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* RESET & BASE */
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
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('img/background.jpg');
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
            width: 100%; 
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

        .right-panel h2::selection {
            background-color: #aa8b56
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

        .input-group input::selection {
            background-color: #aa8b56;
            color: white
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #aa8b56;
            outline: none;
            box-shadow: 0 0 20px rgba(170, 139, 86, 0.2);
        }

        .login-btn {
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

        .login-btn::selection {
            background-color: #1a1212;
            color: #aa8b56;
        }

        .login-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            border: #aa8b56 1px solid;
        }

        .footer-links {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.6);
            display: flex;
            justify-content: space-between;
        }

        .footer-links a {
            color: #aa8b56;
            text-decoration: none;
            font-weight: 700;
        }

        .footer-links ::selection {
            background-color: #aa8b56;
            color: rgba(255,255,255,0.6);
        }

        .footer-links a:hover{
            color: #fff;
        }

        .logo-mobile {
            display: none;
        }

        @media screen and (max-width: 1024px) {
            .login-card {
                width: 95%;
                height: auto;
            }
        }

        @media screen and (max-width: 932px) and (orientation: landscape) {
            .login-card {
                width: 95%;
                height: 96vh; 
                display: flex; 
            }

            .left-panel {
                padding: 20px;
                flex: 0.8;    
            }

            .right-panel {
                flex: 1.5;
                padding: 20px 40px; 
                overflow-y: auto;  
            }

            .right-panel::-webkit-scrollbar {
                display: none;
            }

            .right-panel h2 {
                font-size: 1.5rem;
                margin-top: 0;
                margin-bottom: 15px;
                display: block !important;
            }
            
            .input-group {
                margin-bottom: 10px; 
            }
        }

        @media screen and (max-width: 480px) {
            body {
                height: auto;
                min-height: 100vh;
                overflow-y: auto;
            }

            body::-webkit-scrollbar {
                display: none;
            }

            .login-card { 
                flex-direction: column; 
                width: 90%; 
                height: auto; 
            }

            .left-panel {
                border-right: none; 
                border-bottom: 1px solid rgba(170,139,86,0.2); 
            }

            .right-panel {
                padding: 30px 25px;
                flex: none;
            }

            .right-panel h2 {
                font-size: 1.8rem;
                text-align: center;
                margin-bottom: 25px;
            }

            .logo-icon {
                display: none;
            }

            .logo-mobile {
                display: flex;
                width: 90%;
            }

            .input-group input {
                padding: 14px 15px 14px 50px; 
                font-size: 16px;
            }

            .login-btn {
                font-size: 1.1rem;
                padding: 14px;
                margin-top: 10px;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 15px;
                font-size: 0.85rem;
                margin-top: 25px;
                line-height: 1.5;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="left-panel">
            <img src="img/logo.png" alt="WikkHistory" class="logo-icon">
            <img src="img/wordmark.png" alt="" class="logo-mobile">
        </div>

        <div class="right-panel">
            <h2>Sign In</h2>
            
            <?php if ($error_msg): ?>
                <p style="color: #ff6b6b; margin-bottom: 20px; font-weight: bold;"><?php echo $error_msg; ?></p>
            <?php endif; ?>

            <form method="POST" style="display: flex; flex-direction: column;">
                <div class="input-group">
                    <i class="fa fa-user"></i>
                    <input type="text" name="user" placeholder="Username or Email" required>
                </div>

                <div class="input-group">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="login-btn">LOGIN</button>
            </form>

            <div class="footer-links">
                <div>
                    Never been here before? <a href="auth/register.php">Sign Up</a>
                </div>
                <a href="auth/recovery.php">Forgot Password?</a>
            </div>
        </div>
    </div>

</body>
</html>