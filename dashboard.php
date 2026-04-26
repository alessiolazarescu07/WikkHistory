<?php
session_start();
require 'settings/database.php';

// Reindirizzamento se non loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullName = $_SESSION['nome'] . " " . $_SESSION['cognome'];

// 1. Recupero Statistiche dal DB
$stmtStats = $pdo->prepare("SELECT COUNT(*) as total_notes, AVG(rating) as avg_rating FROM notes WHERE user_id = ?");
$stmtStats->execute([$user_id]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

$totalNotes = $stats['total_notes'] ?? 0;
$avgRating = number_format($stats['avg_rating'] ?? 0, 1);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archeo-Dashboard | WikkHistory</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&family=Cinzel:wght@700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --gold: #aa8b56;
            --gold-bright: #d4af37;
            --glass: rgba(255, 255, 255, 0.08);
            --dark: #0f0f0f;
            --sidebar-width: 280px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Titillium Web', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), url('img/background.jpg');
            background-size: cover;
            background-attachment: fixed;
            color: white;
            margin: 0;
            display: flex;
            min-height: 100vh;
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

        .nav-link i {
            margin-right: 15px;
            color: var(--gold);
            width: 20px;
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

        .main-content {
            flex: 1;
            padding: 40px;
            width: 100%;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--glass);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            backdrop-filter: blur(5px);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--gold);
        }

        .stat-card i {
            color: var(--gold);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        #map {
            height: 550px;
            border-radius: 20px;
            border: 1px solid var(--gold);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            z-index: 1;
        }

        .map-container {
            position: relative;
        }

        .map-overlay-btn,
        .close-map-btn {
            display: none;
        }

        .activity-feed {
            background: var(--glass);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            height: fit-content;
            max-height: 600px;
            overflow-y: auto;
            border-color: var(--gold);
        }

        /* --- POPUP MAPPA --- */
        .leaflet-popup-content-wrapper {
            background: #1a1a1a;
            color: white;
            border: 1px solid var(--gold);
            ù border-radius: 12px;
        }

        .leaflet-popup-tip {
            background: var(--gold);
        }

        .popup-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid var(--gold);
        }

        .popup-btn {
            background: var(--gold);
            color: black;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: 5px;
            transition: 0.3s;
        }

        .popup-btn:hover {
            background: white;
        }

        /* --- MODALE --- */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 10001 !important;
            /* Più alto del 9999 della mappa */
        }

        .modal-content {
            background: #1a1a1a;
            width: 100%;
            max-width: 550px;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid var(--gold);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .rating {
            color: #444;
            font-size: 1.8rem;
            margin: 10px 0;
            text-align: center;
        }

        .rating i {
            cursor: pointer;
            transition: 0.2s;
            padding: 0 5px;
        }

        .rating i.active {
            color: var(--gold-bright);
        }

        textarea {
            width: 100%;
            height: 100px;
            background: #222;
            color: white;
            border: 1px solid var(--gold);
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            resize: none;
            font-family: inherit;
            font-size: 16px;
        }

        #publicNotesList {
            margin-top: 20px;
            max-height: 250px;
            overflow-y: auto;
            border-top: 1px solid rgba(170, 139, 86, 0.3);
            padding-top: 15px;
        }

        .note-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 3px solid var(--gold);
        }

        .btn-save {
            background: var(--gold);
            color: black;
            border: none;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: 0.3s;
            font-size: 1rem;
        }

        .btn-save:hover {
            background: white;
        }

        .close-menu {
            display: none;
            cursor: pointer;
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

            /* 4. Facciamo apparire la "X" per chiudere il menu dentro la sidebar */
            .close-menu {
                display: block !important;
                position: absolute;
                top: 20px;
                right: 20px;
            }

            .main-content {
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

        /* --- SMARTPHONE ORIZZONTALE (max 932px) --- */
        @media screen and (max-width: 932px) and (orientation: landscape) and (max-height: 500px) {
            body {
                flex-direction: column;
            }

            /* Header Mobile più sottile in orizzontale */
            .mobile-header {
                display: flex;
                z-index: 2100;
            }

            /* Logo rimpicciolito drasticamente */
            .logo-mobile {
                width: 110px;
                /* Ridotto da 150px */
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

            /* Riduciamo il margine del contenuto principale per l'header più piccolo */
            .main-content {
                padding: 75px 20px 20px 20px;
            }

            /* Ottimizzazione titoli per risparmiare spazio verticale */
            .main-content h1 {
                font-size: 1.5rem !important;
                margin-bottom: 20px !important;
            }

            /* Stats in linea anche qui per salvare spazio */
            .stats-row {
                grid-template-columns: repeat(1, 1fr);
                gap: 10px;
            }

            .stat-card {
                padding: 10px;
                width: 33%;
                height: 50px;
                display: flex;
                flex: wrap;
                flex-direction: row;
                align-items: center;
                gap: 10px;
            }

            .stat-card i {
                margin-bottom: 0;
            }

            .stat-card p {
                margin-bottom: 10px;
                font-size: 0.85rem;
            }

            .activity-feed{
                width: 156%;
            }

            .map-section {
                width: 60%;
                top: 162px;
                right: 20px;
                position: absolute;
            }

            .map-section h2 {
                display: none;
            }

            #map {
                height: 170px;
            }

            .map-container {
                position: relative;
                border-radius: 20px;
                overflow: hidden;
            }

            .map-overlay-btn {
                display: flex !important;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
                z-index: 10;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }

            .map-overlay-btn span {
                background: var(--gold);
                color: black;
                padding: 10px 20px;
                border-radius: 50px;
                font-weight: bold;
                font-size: 0.8rem;
                text-transform: uppercase;
            }

            /* Stato Full Screen per Mobile */
            .map-fullscreen {
                position: fixed !important;
                top: 0;
                left: 0;
                width: 100vw !important;
                height: 100vh !important;
                z-index: 9999 !important;
                border-radius: 0 !important;
                pointer-events: auto !important;
                border: none !important;
            }

            /* Assicurati che i popup di Leaflet siano sopra tutto dentro la mappa */
            .leaflet-pane {
                z-index: 400 !important;
            }

            .leaflet-top,
            .leaflet-bottom {
                z-index: 1000 !important;
            }

            .close-map-btn {
                display: none;
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                background: var(--gold);
                color: black;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            }

            .logo {
                display: none;
            }

            .nav-link {
                padding: 10px 15px;
                /* Menu più compatto */
                margin-bottom: 5px;
            }
        }

        /* --- RESPONSIVE DESIGN --- */
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

            .main-content {
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

            .stats-row {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
                align-items: start;
                /* Importante per permettere alle card di crescere singolarmente */
                margin-bottom: 25px;
            }

            .stat-card {
                padding: 10px 5px;
                min-height: 60px;
                /* Altezza contratta (solo icona e numero) */
                height: auto;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                /* Effetto rimbalzo */
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                overflow: hidden;
            }

            .stat-card i {
                font-size: 1.1rem;
                margin-bottom: 2px;
                transition: transform 0.3s ease;
            }

            .stat-card h3 {
                font-size: 0.9rem;
                margin: 0;
            }

            /* Testo nascosto e rimpicciolito */
            .stat-card p {
                font-size: 0.55rem;
                text-transform: uppercase;
                font-weight: bold;
                margin: 0;
                opacity: 0;
                max-height: 0;
                transform: scale(0.8);
                transition: all 0.3s ease;
            }

            /* Stato di espansione al tocco/hover */
            .stat-card:hover,
            .stat-card:active {
                min-height: 90px;
                /* Si allunga per ospitare il testo */
                border-color: var(--gold-bright);
                background: rgba(170, 139, 86, 0.2);
                /* Colore oro leggero */
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            }

            .stat-card:hover i,
            .stat-card:active i {
                transform: translateY(-2px);
                /* L'icona sale un po' */
            }

            .stat-card:hover p,
            .stat-card:active p {
                opacity: 1;
                max-height: 40px;
                margin-top: 8px;
                transform: scale(1);
            }

            /* Logica Mappa Ridotta */
            .map-section h2 {
                font-size: 1rem;
            }

            #map {
                height: 200px;
                /* Altezza ridotta come richiesto */
                filter: grayscale(0.5);
                pointer-events: none;
                /* Impedisce interazione accidentale nello scroll */
            }

            .map-container {
                position: relative;
                border-radius: 20px;
                overflow: hidden;
            }

            .map-overlay-btn {
                display: flex !important;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
                z-index: 10;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }

            .map-overlay-btn span {
                background: var(--gold);
                color: black;
                padding: 10px 20px;
                border-radius: 50px;
                font-weight: bold;
                font-size: 0.8rem;
                text-transform: uppercase;
            }

            /* Stato Full Screen per Mobile */
            .map-fullscreen {
                position: fixed !important;
                top: 0;
                left: 0;
                width: 100vw !important;
                height: 100vh !important;
                z-index: 9999 !important;
                border-radius: 0 !important;
                pointer-events: auto !important;
                border: none !important;
            }

            /* Assicurati che i popup di Leaflet siano sopra tutto dentro la mappa */
            .leaflet-pane {
                z-index: 400 !important;
            }

            .leaflet-top,
            .leaflet-bottom {
                z-index: 1000 !important;
            }

            .close-map-btn {
                display: none;
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                background: var(--gold);
                color: black;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            }

            .modal-content {
                width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="mobile-header">
        <img class="logo-mobile" src="img/wordmark.png" alt="logo">
        <i class="fa fa-bars" style="font-size:1.5rem; color:var(--gold); cursor:pointer;"
            onclick="toggleSidebar()"></i>
    </div>

    <div class="sidebar" id="sidebar">
        <i class="fa fa-times close-menu" onclick="toggleSidebar()"></i>
        <img class="logo" src="img/wordmark.png" alt="logo">
        <nav>
            <a class="nav-link active"><i class="fa fa-home"></i> Home Page</a>
            <a href="monuments/monuments.php" class="nav-link"><i class="fa fa-university"></i> Monuments</a>
            <a href="monuments/myNotes.php" class="nav-link"><i class="fa fa-book"></i> My Notes</a>
        </nav>
        <a href="logout.php" style="margin-top:auto; color:#ff4d4d; text-decoration:none; padding:15px;"><i
                class="fa fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div style="text-align:center; margin-bottom:35px;">
            <h1
                style="font-family:'Titillium Web'; color:var(--gold); margin-bottom:5px; font-size:clamp(1.5rem, 5vw, 2.5rem);">
                Welcome Back, <?php echo htmlspecialchars($fullName); ?>
            </h1>
        </div>

        <div class="stats-row">
            <div class="stat-card" onclick="window.location.href='monuments/monuments.php'" style="cursor: pointer;"><i
                    class="fa fa-map-marked-alt"></i>
                <h3>17</h3>
                <p>Archealogical Sites</p>
            </div>
            <div class="stat-card" onclick="window.location.href='monuments/myNotes.php'" style="cursor: pointer;"><i
                    class="fa fa-pen-nib"></i>
                <h3 id="stat-notes"><?php echo $totalNotes; ?></h3>
                <p>Saved Notes</p>
            </div>
            <div class="stat-card"><i class="fa fa-star"></i>
                <h3 id="stat-rating"><?php echo $avgRating; ?></h3>
                <p>Average Rating</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="map-section">
                <h2
                    style="font-family:'Titillium Web'; margin-bottom:15px; font-size:1.3rem; border-left: 3px solid var(--gold); padding-left: 15px; font-weight: bold; color: var(--gold);">
                    AUGUSTA TAURINORUM MAP</h2>
                <div class="map-container">
                    <div id="closeMap" class="close-map-btn" onclick="toggleMapFullscreen()">
                        <i class="fa fa-times"></i>
                    </div>

                    <div class="map-overlay-btn" id="mapOverlay" onclick="toggleMapFullscreen()">
                        <span><i class="fa fa-expand"></i> Open Full Map</span>
                    </div>
                    <div id="map"></div>
                </div>
            </div>

            <div class="activity-feed">
                <h3
                    style="font-family:'Titillium Web'; margin-bottom:15px; font-size:1.3rem; border-left: 3px solid var(--gold); padding-left: 15px; font-weight: bold; color: var(--gold);">
                    RECENT ACTIVITIES</h3>
                <div id="activity-list">
                    <?php
                    $stmt = $pdo->prepare("SELECT action_description, created_at FROM recent_activities WHERE user_id = ? ORDER BY created_at DESC LIMIT 8");
                    $stmt->execute([$user_id]);
                    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($activities):
                        foreach ($activities as $row): ?>
                            <div class='activity-item'
                                style="padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.85rem; display: flex; gap: 10px;">
                                <i class='fa fa-history' style="color:var(--gold-bright); margin-top:3px;"></i>
                                <span>
                                    <?php echo htmlspecialchars($row['action_description']); ?> <br>
                                    <small
                                        style='opacity:0.5'><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></small>
                                </span>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p style="opacity:0.5; padding:10px;">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="notesModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle"
                style="color:var(--gold); font-family: 'Titillium Web', serif; margin-top:0; text-align:center;">Site
                Name</h2>

            <div id="publicNotesList"></div>

            <h4
                style="color:var(--gold); margin: 20px 0 5px 0; font-size:0.9rem; text-transform:uppercase; border-top:1px solid #333; padding-top:15px;">
                ADD YOUR THOUGHTS</h4>
            <div class="rating" id="ratingStars">
                <i class="fa fa-star" data-value="1"></i>
                <i class="fa fa-star" data-value="2"></i>
                <i class="fa fa-star" data-value="3"></i>
                <i class="fa fa-star" data-value="4"></i>
                <i class="fa fa-star" data-value="5"></i>
            </div>
            <textarea id="noteText" placeholder="Write your thoughts here..."></textarea>

            <button id="btnSave" class="btn-save" onclick="saveData()">Save Note <i class="fa fa-sync"></i></button>
            <button onclick="closeModal()"
                style="background:none; border:none; color:grey; width:100%; margin-top:15px; cursor:pointer; font-size:0.9rem;">Cancel
                & Close </button>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let currentMonumentId = '';
        let currentRating = 0;

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Inizializzazione Mappa
        const map = L.map('map').setView([45.0715, 7.6840], 15);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const monumenti = [
            { id: 'porta_decumana', name: "Porta Decumana (Palazzo Madama)", coords: [45.0709, 7.6862], img: "img/monuments/porta_decumana.jpg" },
            { id: 'foro_romano', name: "Foro Romano", coords: [45.0725, 7.6825], img: "img/monuments/foro_romano.jpg" },
            { id: 'necropol_carignano', name: "Necropoli Palazzo Carignano", coords: [45.0689, 7.6861], img: "img/monuments/necropol_carignano.jpg" },
            { id: 'necropoli_san_carlo', name: "Necropoli Piazza San Carlo", coords: [45.0677, 7.6824], img: "img/monuments/necropoli_san_carlo.jpg" },
            { id: 'necropoli_dora', name: "Necropoli oltre la Dora", coords: [45.0800, 7.6860], img: "img/monuments/necropoli_dora.jpg" },
            { id: 'tomba_cernaia', name: "Tomba Via Cernaia", coords: [45.0695, 7.6745], img: "img/monuments/tomba_cernaia.jpg" },
            { id: 'tomba_cenisia', name: "Tomba Borgata Cenisia", coords: [45.0665, 7.6585], img: "img/monuments/tomba_cenisia.jpg" },
            { id: 'necropoli_fanciullo', name: "Necropoli e Tomba Fanciullo", coords: [45.0755, 7.6830], img: "img/monuments/necropoli_fanciullo.jpg" },
            { id: 'teatro_romano', name: "Teatro Romano", coords: [45.0741, 7.6858], img: "img/monuments/teatro_romano.jpg" },
            { id: 'porta_palatina', name: "Porta Palatina", coords: [45.0748, 7.6844], img: "img/monuments/porta_palatina.jpg" },
            { id: 'domus_bonelli', name: "Domus Via Bonelli 11", coords: [45.0732, 7.6798], img: "img/monuments/domus_bonelli.jpg" },
            { id: 'domus_castello', name: "Domus Piazza Castello 51", coords: [45.0720, 7.6855], img: "img/monuments/domus_castello.jpg" },
            { id: 'domus_garibaldi', name: "Domus Via Garibaldi 18", coords: [45.0718, 7.6805], img: "img/monuments/domus_garibaldi.jpg" },
            { id: 'domus_san_carlo', name: "Domus Piazza San Carlo", coords: [45.0673, 7.6828], img: "img/monuments/domus_san_carlo.jpg" },
            { id: 'domus_bellezia', name: "Domus Via Bellezia 16", coords: [45.0740, 7.6812], img: "img/monuments/domus_bellezia.jpg" },
            { id: 'domus_orfane', name: "Domus Via delle Orfane 20", coords: [45.0735, 7.6791], img: "img/monuments/domus_orfane.jpg" },
            { id: 'torre_consolata', name: "Torre Angolare", coords: [45.0763, 7.6795], img: "img/monuments/torre_consolata.jpg" }
        ];

        const myPin = L.icon({
            iconUrl: 'img/pin-mappa.png',
            iconSize: [38, 38],
            iconAnchor: [19, 38],
            popupAnchor: [0, -35]
        });

        monumenti.forEach(loc => {
            const popupContent = `
                <div style="min-width: 200px;">
                    <img src="${loc.img}" class="popup-img" alt="${loc.name}" onerror="this.src='https://via.placeholder.com/200x120?text=Immagine+Archeologica'">
                    <b style="font-size:1.1rem; display:block; margin-bottom:5px; font-family: 'Titillium Web', sans-serif;">${loc.name}</b>
                    <button class="popup-btn" onclick="openNotes('${loc.id}', '${loc.name.replace(/'/g, "\\'")}')">
                        <i class="fa fa-pen-nib"></i> Details & Notes
                    </button>
                </div>
            `;
            L.marker(loc.coords, { icon: myPin }).addTo(map).bindPopup(popupContent);
        });

        document.querySelectorAll('#ratingStars i').forEach(star => {
            star.addEventListener('click', function () {
                currentRating = this.dataset.value;
                updateStars(currentRating);
            });
        });

        function updateStars(val) {
            document.querySelectorAll('#ratingStars i').forEach(s => {
                s.classList.toggle('active', s.dataset.value <= val);
            });
        }

        async function openNotes(id, name) {
            // Chiudi sidebar se mobile
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('active')) sidebar.classList.remove('active');

            currentMonumentId = id;
            document.getElementById('modalTitle').innerText = name;
            document.getElementById('noteText').value = "";
            updateStars(0);
            currentRating = 0;

            const listContainer = document.getElementById('publicNotesList');
            listContainer.innerHTML = "<p style='opacity:0.5; font-size:0.8rem;'><i class='fa fa-sync fa-spin'></i> Consulting archives...</p>";

            document.getElementById('notesModal').style.display = 'flex';

            try {
                const response = await fetch(`settings/getNotes.php?id=${id}`);
                const data = await response.json();

                listContainer.innerHTML = "";

                if (data.status === 'success' && data.notes && data.notes.length > 0) {
                    data.notes.forEach(nota => {
                        const noteDiv = document.createElement('div');
                        noteDiv.className = 'note-item';

                        let starsHtml = "";
                        const r = parseInt(nota.rating) || 0;
                        for (let i = 1; i <= 5; i++) {
                            starsHtml += `<i class="fa fa-star" style="color: ${i <= r ? '#d4af37' : '#444'}; font-size:0.7rem;"></i>`;
                        }

                        noteDiv.innerHTML = `
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                                <strong style="color:var(--gold-bright); font-size:0.85rem;">${nota.user_name}</strong>
                                <span>${starsHtml}</span>
                            </div>
                            <p style="margin:0; font-size:0.9rem; line-height:1.4;">${nota.note_text}</p>
                            <small style="opacity:0.3; font-size:0.65rem;">${nota.created_at}</small>
                        `;
                        listContainer.appendChild(noteDiv);
                    });
                } else {
                    listContainer.innerHTML = "<p style='opacity:0.5; font-size:0.85rem; text-align:center; padding:10px;'>No notes available for this site</p>";
                }
            } catch (e) {
                listContainer.innerHTML = "<p style='color:red;'>Network error while fetching notes.</p>";
                console.error(e);
            }
        }

        function saveData() {
            const note = document.getElementById('noteText').value.trim();
            if (!note) { alert("Please enter a note!"); return; }
            if (currentRating == 0) { alert("Please select a rating (stars)!"); return; }

            const btn = document.getElementById('btnSave');
            btn.innerHTML = "Syncing... <i class='fa fa-spinner fa-spin'></i>";
            btn.disabled = true;

            const formData = new FormData();
            formData.append('monument_id', currentMonumentId);
            formData.append('monument_name', document.getElementById('modalTitle').innerText);
            formData.append('note_text', note);
            formData.append('rating', currentRating);

            fetch('settings/saveNotes.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert("Error: " + (data.message || "Unable to save"));
                        btn.innerHTML = "Try again <i class='fa fa-sync'></i>";
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert("Network error");
                    btn.disabled = false;
                    btn.innerHTML = "Try again <i class='fa fa-sync'></i>";
                });
        }

        function closeModal() {
            document.getElementById('notesModal').style.display = 'none';
        }

        // Chiudi modale cliccando fuori
        window.onclick = function (event) {
            if (event.target == document.getElementById('notesModal')) closeModal();
        }

        function toggleMapFullscreen() {
            // Funziona solo su schermi piccoli
            if (window.innerWidth > 932) return;

            const mapElement = document.getElementById('map');
            const overlay = document.getElementById('mapOverlay');
            const closeBtn = document.getElementById('closeMap');

            mapElement.classList.toggle('map-fullscreen');

            if (mapElement.classList.contains('map-fullscreen')) {
                overlay.style.display = 'none';
                closeBtn.style.display = 'flex';
                // Forza il ridisegno della mappa Leaflet
                setTimeout(() => { map.invalidateSize(); }, 300);
            } else {
                overlay.style.display = 'flex';
                closeBtn.style.display = 'none';
                setTimeout(() => { map.invalidateSize(); }, 300);
            }
        }
        window.addEventListener('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const monumentId = urlParams.get('monument');

            if (monumentId) {
                const target = monumenti.find(m => m.id === monumentId);

                if (target) {
                    map.setView(target.coords, 17, { animate: true });
                    map.eachLayer((layer) => {
                        if (layer instanceof L.Marker) {
                            const latLng = layer.getLatLng();
                            if (latLng.lat === target.coords[0] && latLng.lng === target.coords[1]) {
                                layer.openPopup();
                            }
                        }
                    });
                    if (window.innerWidth <= 932) {
                        const mapContainer = document.getElementById('map');
                        mapContainer.classList.add('map-fullscreen');
                    }
                }
            }
        });
    </script>
</body>

</html>