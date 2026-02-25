<?php
session_start();
require 'settings/database.php';

// Reindirizzamento se non loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WikkHistory | Archeo-Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&family=Cinzel:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root { 
            --gold: #aa8b56; 
            --gold-bright: #d4af37;
            --glass: rgba(255, 255, 255, 0.08);
            --dark: #0f0f0f;
        }

        body {
            font-family: 'Titillium Web', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), 
                        url('https://images.unsplash.com/photo-1552832230-c0197dd311b5?auto=format&fit=crop&w=1920&q=80');
            background-size: cover; background-attachment: fixed; color: white; display: flex; margin:0;
        }

        .sidebar { 
            width: 280px; background: rgba(0, 0, 0, 0.9); padding: 40px 20px; 
            display: flex; flex-direction: column; border-right: 1px solid var(--gold); height: 100vh; position: sticky; top:0;
        }
        .sidebar h2 { font-family: 'Cinzel', serif; color: var(--gold); text-align: center; margin-bottom: 40px; }
        .nav-link { color: #ccc; text-decoration: none; padding: 15px; margin-bottom: 10px; border-radius: 12px; transition: 0.3s; display: flex; align-items: center; cursor: pointer; border: 1px solid transparent; }
        .nav-link i { margin-right: 15px; color: var(--gold); width: 20px; }
        .nav-link:hover, .nav-link.active { background: var(--glass); border-color: var(--gold); color: white; }

        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--glass); border: 1px solid rgba(255,255,255,0.1); padding: 20px; border-radius: 15px; text-align: center; }
        .stat-card i { color: var(--gold); font-size: 1.5rem; margin-bottom: 10px; }

        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        #map { height: 500px; border-radius: 20px; border: 1px solid var(--gold); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }

        .activity-feed { background: var(--glass); border-radius: 20px; padding: 25px; border: 1px solid rgba(255,255,255,0.1); max-height: 550px; overflow-y: auto; }
        .activity-item { padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.85rem; display: flex; gap: 10px; }
        .activity-item i { color: var(--gold-bright); }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(10px); align-items: center; justify-content: center; }
        .modal-content { background: #1a1a1a; width: 90%; max-width: 550px; padding: 30px; border-radius: 20px; border: 1px solid var(--gold); position: relative;}
        .rating { color: #444; font-size: 1.5rem; margin: 15px 0; }
        .rating i { cursor: pointer; transition: 0.2s; }
        .rating i.active { color: var(--gold-bright); }
        textarea { width: 100%; height: 150px; background: #222; color: white; border: 1px solid var(--gold); padding: 15px; border-radius: 10px; margin: 15px 0; resize: none; font-family: inherit; }
        .btn-save { background: var(--gold); color: black; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-save:hover { background: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>WikkHistory</h2>
        <nav>
            <a class="nav-link active"><i class="fa fa-home"></i> Homepage</a>
            <a href="monuments/myNotes.php" class="nav-link"><i class="fa fa-book"></i> I Miei Appunti</a>
        </nav>
        <a href="logout.php" style="margin-top:auto; color:#ff4d4d; text-decoration:none;"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="stats-row">
            <div class="stat-card"><i class="fa fa-map-marked-alt"></i><h3>17</h3><p>Siti Archeologici</p></div>
            <div class="stat-card"><i class="fa fa-pen-nib"></i><h3 id="stat-notes"><?php echo $totalNotes; ?></h3><p>Note Salvate</p></div>
            <div class="stat-card"><i class="fa fa-star"></i><h3 id="stat-rating"><?php echo $avgRating; ?></h3><p>Valutazione Media</p></div>
        </div>

        <div class="dashboard-grid">
            <div class="map-section">
                <div style="text-align:center; margin-bottom:20px; font-size:1.6rem; color:var(--gold);">
                    Bentornato, <b><?php echo htmlspecialchars($fullName); ?></b>
                </div>
                <h2 style="font-family:'Cinzel'; margin-bottom:15px;">Mappa Augusta Taurinorum</h2>
                <div id="map"></div>
            </div>

            <div class="activity-feed">
                <h3 style="font-family:'Cinzel'; margin-bottom:15px; color:var(--gold);">Attività Recenti</h3>
                <div id="activity-list">
                    <?php
                    $stmt = $pdo->prepare("SELECT action_description, created_at FROM recent_activities WHERE user_id = ? ORDER BY created_at DESC LIMIT 8");
                    $stmt->execute([$user_id]);
                    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($activities):
                        foreach ($activities as $row): ?>
                            <div class='activity-item'>
                                <i class='fa fa-history'></i> 
                                <span>
                                    <?php echo htmlspecialchars($row['action_description']); ?> 
                                    <br>
                                    <small style='opacity:0.5'><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></small>
                                </span>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p style="opacity:0.5; padding:10px;">Nessuna attività recente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="notesModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle" style="color:var(--gold); font-family: 'Cinzel', serif;">Nome Sito</h2>
            <div class="rating" id="ratingStars">
                <i class="fa fa-star" data-value="1"></i>
                <i class="fa fa-star" data-value="2"></i>
                <i class="fa fa-star" data-value="3"></i>
                <i class="fa fa-star" data-value="4"></i>
                <i class="fa fa-star" data-value="5"></i>
            </div>
            <textarea id="noteText" placeholder="Scrivi qui le tue scoperte..."></textarea>
            <button id="btnSave" class="btn-save" onclick="saveData()">Sincronizza Esperienza <i class="fa fa-sync"></i></button>
            <button onclick="closeModal()" style="background:none; border:none; color:grey; width:100%; margin-top:10px; cursor:pointer;">Chiudi</button>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let currentMonumentId = '';
        let currentRating = 0;

        const map = L.map('map').setView([45.0715, 7.6840], 15);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        const monumenti = [
            { id: 'porta_decumana', name: "Porta Decumana (Palazzo Madama)", coords: [45.0709, 7.6862] },
            { id: 'foro_romano', name: "Foro Romano", coords: [45.0725, 7.6825] },
            { id: 'necropol_carignano', name: "Necropoli Palazzo Carignano", coords: [45.0689, 7.6861] },
            { id: 'necropoli_san_carlo', name: "Necropoli Piazza San Carlo", coords: [45.0677, 7.6824] },
            { id: 'necropoli_dora', name: "Necropoli oltre la Dora", coords: [45.0800, 7.6860] },
            { id: 'tomba_cernaia', name: "Tomba Via Cernaia", coords: [45.0695, 7.6745] },
            { id: 'tomba_cenisia', name: "Tomba Borgata Cenisia", coords: [45.0665, 7.6585] },
            { id: 'necropoli_fanciullo', name: "Necropoli e Tomba Fanciullo", coords: [45.0755, 7.6830] },
            { id: 'teatro_romano', name: "Teatro Romano", coords: [45.0741, 7.6858] },
            { id: 'porta_palatina', name: "Porta Palatina", coords: [45.0748, 7.6844] },
            { id: 'domus_bonelli', name: "Domus Via Bonelli 11", coords: [45.0732, 7.6798] },
            { id: 'domus_castello', name: "Domus Piazza Castello 51", coords: [45.0720, 7.6855] },
            { id: 'domus_garibaldi', name: "Domus Via Garibaldi 18", coords: [45.0718, 7.6805] },
            { id: 'domus_san_carlo', name: "Domus Piazza San Carlo", coords: [45.0673, 7.6828] },
            { id: 'domus_bellezia', name: "Domus Via Bellezia 16", coords: [45.0740, 7.6812] },
            { id: 'domus_orfane', name: "Domus Via delle Orfane 20", coords: [45.0735, 7.6791] },
            { id: 'torre_consolata', name: "Torre Angolare", coords: [45.0763, 7.6795] }
        ];

        monumenti.forEach(loc => {
            L.marker(loc.coords).addTo(map).bindPopup(`<b>${loc.name}</b><br><button onclick="openNotes('${loc.id}', '${loc.name}')" style="margin-top:8px; cursor:pointer; padding: 5px 10px;">Dettagli / Note</button>`);
        });

        // Gestione click stelle
        document.querySelectorAll('#ratingStars i').forEach(star => {
            star.addEventListener('click', function() {
                currentRating = this.dataset.value;
                updateStars(currentRating);
            });
        });

        function updateStars(val) {
            document.querySelectorAll('#ratingStars i').forEach(s => {
                s.classList.toggle('active', s.dataset.value <= val);
            });
        }

        // Recupero Note esistenti
        async function openNotes(id, name) {
            currentMonumentId = id;
            document.getElementById('modalTitle').innerText = name;
            document.getElementById('noteText').value = "Caricamento archivi...";
            updateStars(0);
            document.getElementById('notesModal').style.display = 'flex';

            try {
                const response = await fetch(`settings/getNotes.php?id=${id}`);
                const data = await response.json();
                
                if (data.status === 'success' && data.note) {
                    document.getElementById('noteText').value = data.note.note_text;
                    currentRating = data.note.rating;
                    updateStars(currentRating);
                } else {
                    document.getElementById('noteText').value = "";
                }
            } catch (e) {
                document.getElementById('noteText').value = "";
                console.error("Errore recupero note:", e);
            }
        }

        // Salvataggio Note
        function saveData() {
            const note = document.getElementById('noteText').value;
            const btn = document.getElementById('btnSave');
            
            btn.innerHTML = "Sincronizzazione... <i class='fa fa-spinner fa-spin'></i>";
            btn.disabled = true;

            const formData = new FormData();
            formData.append('monument_id', currentMonumentId);
            formData.append('monument_name', document.getElementById('modalTitle').innerText);
            formData.append('note_text', note);
            formData.append('rating', currentRating);

            fetch('settings/saveNotes.php', { method: 'POST', body: formData })
            .then(res => res.json())    
            .then(data => {
                if(data.status === 'success') {
                    location.reload(); 
                } else {
                    alert("Errore nel salvataggio: " + (data.message || "Sessione scaduta?"));
                    btn.innerHTML = "Riprova <i class='fa fa-sync'></i>";
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert("Errore di rete");
                btn.disabled = false;
            });
        }

        function closeModal() { document.getElementById('notesModal').style.display = 'none'; }

        function exportNotes() { window.location.href = 'export_csv.php'; }
    </script>
</body>
</html>