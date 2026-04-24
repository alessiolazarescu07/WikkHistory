<?php
session_start();
require '../settings/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$fullName = $_SESSION['nome'] . " " . $_SESSION['cognome'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monuments | WikkHistory</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../img/favicon.png">
    <style>
        :root { 
            --gold: #aa8b56; 
            --gold-bright: #d4af37;
            --glass: rgba(255, 255, 255, 0.08);
            --sidebar-width: 280px;
        }

        * { 
            box-sizing: border-box; 
        }

        body {
            font-family: 'Titillium Web', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('../img/background.jpg');
            background-size: cover; background-attachment: fixed;
            color: white; margin: 0; display: flex; min-height: 100vh;
        }

        .sidebar { 
            width: var(--sidebar-width); 
            background: rgba(0, 0, 0, 0.95); 
            padding: 40px 20px; 
            display: flex; 
            flex-direction: column; 
            border-right: 1px solid var(--gold); 
            height: 100vh; 
            position: sticky; 
            top: 0; 
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            z-index: 1001;
        }

        .sidebar .logo { 
            align-items: center;
            margin-top: 40px;
            margin-bottom: 40px; 
        }

        .nav-link { 
            color: #ccc; 
            text-decoration: none; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-radius: 12px; 
            transition: 0.3s; 
            display: flex; 
            align-items: center; 
            border: 1px solid transparent; 
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active { 
            background: var(--glass); 
            border-color: var(--gold); 
            color: white;
        }

        .nav-link.active {
            font-weight: bold;
        }

        .nav-link i { 
            margin-right: 15px; 
            color: var(--gold); 
            width: 20px; 
        }

        .main-content { 
            flex: 1; 
            padding: 40px; 
            width: 100%; 
        }
        
        .monuments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px; margin-top: 30px;
        }

        .monument-card {
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none; color: inherit;
            display: flex; flex-direction: column;
        }

        .monument-card:hover {
            transform: translateY(-10px);
            border-color: var(--gold);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .card-img {
            width: 100%; height: 200px; object-fit: cover;
            border-bottom: 2px solid var(--gold);
        }

        .card-body { padding: 20px; }
        .card-body h3 { color: var(--gold); margin: 0 0 10px 0; font-size: 1.4rem; }
        .card-body p { color: #ccc; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px; }
        
        .btn-view {
            color: var(--gold-bright); font-weight: bold; font-size: 0.8rem;
            text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center;
        }
        .btn-view i { margin-left: 10px; transition: 0.3s; }
        .monument-card:hover .btn-view i { transform: translateX(5px); }

        @media (max-width: 932px) {
            body { flex-direction: column; }
            .sidebar { display: none; } /* Qui potresti riutilizzare l'hamburger menu dell'altra pagina */
            .main-content { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <img src="../img/wordmark.png" class="logo" alt="Logo">
        <nav>
            <a href="../dashboard.php" class="nav-link"><i class="fa fa-home"></i> Home Page</a>
            <a href="monuments.php" class="nav-link active"><i class="fa fa-university"></i> Monuments</a>
            <a href="myNotes.php" class="nav-link"><i class="fa fa-book"></i>My Notes</a>
        </nav>
        <a href="../logout.php" style="margin-top:auto; color:#ff4d4d; text-decoration:none; padding:15px;"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 style="color:var(--gold); border-left: 4px solid var(--gold); padding-left: 20px;">MONUMENTS</h1>
        <p style="opacity: 0.7;">Select a monument to look it up on the map and discover what others had to say</p>

        <div class="monuments-grid" id="monumentsGrid">
            <a href="../dashboard.php?monument=porta_palatina" class="monument-card">
                <img src="../img/monuments/porta_palatina.jpg" class="card-img" alt="Porta Palatina">
                <div class="card-body">
                    <h3>Porta Palatina</h3>
                    <p>La principale testimonianza archeologica dell'antica Augusta Taurinorum, una delle porte romane meglio conservate al mondo.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=teatro_romano" class="monument-card">
                <img src="../img/monuments/teatro_romano.jpg" class="card-img" alt="Teatro Romano">
                <div class="card-body">
                    <h3>Teatro Romano</h3>
                    <p>Situato nel cuore del polo reale, i resti del teatro raccontano la vita sociale e culturale della colonia romana.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>
            
             <a href="../dashboard.php?monument=porta_decumana" class="monument-card">
                <img src="../img/monuments/porta_decumana.jpg" class="card-img" alt="Porta Palatina">
                <div class="card-body">
                    <h3>Porta Decumana (Palazzo Madama)</h3>
                    <p>La principale testimonianza archeologica dell'antica Augusta Taurinorum, una delle porte romane meglio conservate al mondo.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=teatro_romano" class="monument-card">
                <img src="../img/monuments/foro_romano.jpg" class="card-img" alt="Teatro Romano">
                <div class="card-body">
                    <h3>Foro Romano</h3>
                    <p>Situato nel cuore del polo reale, i resti del teatro raccontano la vita sociale e culturale della colonia romana.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

             <a href="../dashboard.php?monument=torre_consolata" class="monument-card">
                <img src="../img/monuments/torre_consolata.jpg" class="card-img" alt="Porta Palatina">
                <div class="card-body">
                    <h3>Torre Angolare</h3>
                    <p>La principale testimonianza archeologica dell'antica Augusta Taurinorum, una delle porte romane meglio conservate al mondo.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_castello" class="monument-card">
                <img src="../img/monuments/domus_castello.jpg" class="card-img" alt="Domus Castello">
                <div class="card-body">
                    <h3>Domus Piazza Castello 51</h3>
                    <p>Situato nel cuore del polo reale, i resti del teatro raccontano la vita sociale e culturale della colonia romana.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

             <a href="../dashboard.php?monument=domus_san_carlo" class="monument-card">
                <img src="../img/monuments/domus_san_carlo.jpg" class="card-img" alt="Domus San Carlo">
                <div class="card-body">
                    <h3>Domus Piazza San Carlo</h3>
                    <p>La principale testimonianza archeologica dell'antica Augusta Taurinorum, una delle porte romane meglio conservate al mondo.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_bonelli" class="monument-card">
                <img src="../img/monuments/domus_bonelli.jpg" class="card-img" alt="Domus Bonelli">
                <div class="card-body">
                    <h3>Domus Via Bonelli 11</h3>
                    <p>Situato nel cuore del polo reale, i resti del teatro raccontano la vita sociale e culturale della colonia romana.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

             <a href="../dashboard.php?monument=domus_garibaldi" class="monument-card">
                <img src="../img/monuments/domus_garibaldi.jpg" class="card-img" alt="Domus Garibaldi">
                <div class="card-body">
                    <h3>Domus Via Garibaldi 18</h3>
                    <p>La principale testimonianza archeologica dell'antica Augusta Taurinorum, una delle porte romane meglio conservate al mondo.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            <a href="../dashboard.php?monument=domus_orfane" class="monument-card">
                <img src="../img/monuments/domus_orfane.jpg" class="card-img" alt="Domus Orfane">
                <div class="card-body">
                    <h3>Domus Via delle Orfane 20</h3>
                    <p>La principale testimonianza archeologica dell'antica Augusta Taurinorum, una delle porte romane meglio conservate al mondo.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

             <a href="../dashboard.php?monument=domus_bellezia" class="monument-card">
                <img src="../img/monuments/domus_bellezia.jpg" class="card-img" alt="Domus Bellezia">
                <div class="card-body">
                    <h3>Domus Via Bellezia 16</h3>
                    <p>La principale testimonianza archeologica dell'antica Augusta Taurinorum, una delle porte romane meglio conservate al mondo.</p>
                    <div class="btn-view">Open in the map <i class="fa fa-arrow-right"></i></div>
                </div>
            </a>

            </div>
    </div>
</body>
</html>