<?php
session_start();
require '../settings/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $myNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Errore nel caricamento degli archivi: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes | WikkHistory</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&family=Cinzel:wght@700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            background: linear-gradient(rgba(0, 0, 0, 0.92), rgba(0, 0, 0, 0.92)), url('../img/background.jpg');
            background-size: cover;
            background-attachment: fixed;
            color: white;
            margin: 0;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
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

        .mobile-header {
            display: none;
            background: #000;
            padding: 15px 20px;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--gold);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1002;
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

        .nav-link:hover,
        .nav-link.active {
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

        .container {
            flex: 1;
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--gold);
            padding-bottom: 20px;
        }

        h1 {
            font-family: 'Titillium Web', serif;
            color: var(--gold);
            margin: 0;
            font-size: 1.8rem;
        }

        .btn-back {
            text-decoration: none;
            color: white;
            background: var(--glass);
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid var(--gold);
            transition: 0.3s;
            font-weight: 600;
        }

        .btn-back:hover {
            background: var(--gold);
            color: #1a1212;
        }

        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .note-card {
            background: var(--glass);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            transition: 0.3s;
            display: flex;
            flex-direction: column;
        }

        .note-card:hover {
            transform: translateY(-5px);
            border-color: var(--gold);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
        }

        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .monument-name {
            font-family: 'Titillium Web', serif;
            color: var(--gold);
            font-size: 1.1rem;
            margin: 0;
            line-height: 1.2;
            flex: 1;
            padding-right: 10px;
        }

        .rating {
            color: var(--gold-bright);
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .note-text {
            font-style: italic;
            line-height: 1.6;
            color: #eee;
            margin-bottom: 20px;
            flex-grow: 1;
            border-left: 2px solid var(--gold);
            padding-left: 15px;
        }

        .note-footer {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--glass);
            border-radius: 20px;
            grid-column: 1 / -1;
            border: 1px dashed var(--gold);
        }

         @media screen and (min-width: 481px) and (max-width: 1024px) and (orientation: portrait) {
            .sidebar {
                position: fixed;
                left: -100%;
                width: 100%;
                height: 100vh;
                z-index: 2000;
                transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                padding-top: 80px;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.8);
                border-right: none;
                border-top: 1px solid var(--gold);
                transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .sidebar a {
                font-size: 1.3rem;
                padding: 20px;
            }

            .sidebar a i {
                font-size: 1.6rem;
            }

            .sidebar.active {
                left: 0;
            }

            .mobile-header {
                display: flex !important;
                z-index: 2100;
            }

            .close-menu {
                display: block !important;
                position: absolute;
                top: 20px;
                right: 20px;
            }

            .container {
                margin-left: 0 !important;
                padding-top: 100px;
            }

            .sidebar .logo {
                display: none;
            }

            .logo-mobile {
                width: 150px;
                height: auto;
            }
        }

        @media screen and (max-width: 932px) and (orientation: landscape) {
            body {
                flex-direction: column;
            }

            .mobile-header {
                display: flex;
                z-index: 2100;
            }

            .logo-mobile {
                width: 110px;
                height: auto;
            }

            .sidebar {
                position: fixed;
                left: -100%;
                width: 100%;
                top: 0;
                padding-top: 80px;
                z-index: 2000;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.8);
                border-right: none;
                border-top: 1px solid var(--gold);
                transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .sidebar.active {
                left: 0;
            }

            .container {
                padding: 75px 20px 20px 20px;
            }

            .container h1 {
                font-size: 1.5rem !important;
            }

            .logo {
                display: none;
            }
        }

        @media screen and (max-width: 480px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                position: fixed;
                left: -100%;
                width: 100%;
                height: 100vh;
                top: 0;
                padding-top: 80px;
                background-color: #000;
                border-right: none;
            }

            .sidebar.active {
                left: 0;
            }

            .mobile-header {
                display: flex;
            }

            .container {
                padding: 100px 20px 40px 20px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .close-menu {
                position: absolute;
                top: 25px;
                right: 25px;
                font-size: 2rem;
                color: var(--gold);
                display: block;
            }

            .logo {
                display: none;
            }

            .logo-mobile {
                width: 150px;
                align-self: center
            }
        }
    </style>
</head>

<body>
    <div class="mobile-header">
        <img class="logo-mobile" src="../img/wordmark.png" alt="logo">
        <i class="fa fa-bars" style="font-size:1.5rem; color:var(--gold); cursor:pointer;"
            onclick="toggleSidebar()"></i>
    </div>

    <div class="sidebar" id="sidebar">
        <img src="../img/wordmark.png" class="logo" alt="Logo">
        <nav>
            <a href="../dashboard.php" class="nav-link"><i class="fa fa-home"></i> Home Page</a>
            <a href="monuments.php" class="nav-link"><i class="fa fa-university"></i> Monuments</a>
            <a href="myNotes.php" class="nav-link active"><i class="fa fa-book"></i>My Notes</a>
        </nav>
        <a href="../logout.php" style="margin-top:auto; color:#ff4d4d; text-decoration:none; padding:15px;"><i
                class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="container">
        <div class="header">
            <h1><i class="fa fa-scroll"></i> MY ARCHEOLOGY DIARY</h1>
            <a href="../dashboard.php" class="btn-back"><i class="fa fa-map-marked-alt"></i> Back To Map</a>
        </div>

        <div class="notes-grid">
            <?php if (count($myNotes) > 0): ?>
                <?php foreach ($myNotes as $note): ?>
                    <div class="note-card">
                        <div class="note-header">
                            <h3 class="monument-name">
                                <?php echo htmlspecialchars($note['monument_name'] ?: ucwords(str_replace('_', ' ', $note['monument_id']))); ?>
                            </h3>
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo ($i <= $note['rating']) ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="note-text">
                            "<?php echo nl2br(htmlspecialchars($note['note_text'])); ?>"
                        </div>

                        <div class="note-footer">
                            <span><i class="fa fa-calendar-day"></i>
                                <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?></span>
                            <span style="text-transform: uppercase; letter-spacing: 1px;">
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa fa-compass"
                        style="font-size: 4rem; color: var(--gold); margin-bottom: 20px; opacity: 0.5;"></i>
                    <h2 style="font-family: 'Titillium Web', sans-serif; color: var(--gold);">No Notes Yet</h2>
                    <p style="opacity: 0.7; margin-bottom: 25px;">Your diary is still empty. Visit the archaeological sites
                        in Turin on the map to start documenting your discoveries.</p>
                    <a href="../dashboard.php" class="btn-back">Start Exploring</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }
</script>

</html>