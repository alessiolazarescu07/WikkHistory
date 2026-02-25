<?php
session_start();
require '../settings/database.php';

// Protezione pagina


$user_id = $_SESSION['user_id'];

// Recupero di tutte le note dell'utente con una JOIN (opzionale se hai una tabella monumenti)
// Qui usiamo la tabella 'notes' che abbiamo sistemato prima
$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->execute([$user_id]);
$myNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Appunti | WikkHistory</title>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&family=Cinzel:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --gold: #aa8b56; 
            --glass: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Titillium Web', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.9)), 
                        url('https://images.unsplash.com/photo-1552832230-c0197dd311b5?auto=format&fit=crop&w=1920&q=80');
            background-size: cover; background-attachment: fixed;
            color: white; margin: 0; padding: 40px;
        }

        .container { max-width: 1000px; margin: 0 auto; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--gold);
            padding-bottom: 20px;
        }

        h1 { font-family: 'Cinzel', serif; color: var(--gold); margin: 0; }

        .btn-back {
            text-decoration: none;
            color: white;
            background: var(--glass);
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid var(--gold);
            transition: 0.3s;
        }

        .btn-back:hover { background: var(--gold); color: black; }

        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .note-card {
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .note-card:hover { transform: translateY(-5px); border-color: var(--gold); }

        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .monument-name {
            font-family: 'Cinzel', serif;
            color: var(--gold);
            font-size: 1.2rem;
            margin: 0;
        }

        .rating { color: #d4af37; font-size: 0.9rem; }

        .note-text {
            font-style: italic;
            line-height: 1.6;
            color: #ddd;
            margin-bottom: 20px;
            min-height: 60px;
        }

        .note-footer {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            background: var(--glass);
            border-radius: 20px;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Archivio Scoperte</h1>
        <a href="../dashboard.php" class="btn-back"><i class="fa fa-arrow-left"></i> Torna alla Mappa</a>
    </div>

    <div class="notes-grid">
        <?php if (count($myNotes) > 0): ?>
            <?php foreach ($myNotes as $note): ?>
                <div class="note-card">
                    <div class="note-header">
                        <h3 class="monument-name">
                            <?php 
                            // Rendiamo l'ID più leggibile (es. porta_palatina -> Porta Palatina)
                            echo ucwords(str_replace('_', ' ', $note['monument_id'])); 
                            ?>
                        </h3>
                        <div class="rating">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fa<?php echo ($i <= $note['rating']) ? '-solid' : '-regular'; ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="note-text">
                        "<?php echo nl2br(htmlspecialchars($note['note_text'])); ?>"
                    </div>

                    <div class="note-footer">
                        <span><i class="fa fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($note['updated_at'])); ?></span>
                        <span><i class="fa fa-id-badge"></i> ID: <?php echo htmlspecialchars($note['monument_id']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-feather-alt" style="font-size: 3rem; color: var(--gold); margin-bottom: 20px;"></i>
                <p>Non hai ancora registrato nessuna scoperta. Esplora la mappa e scrivi la tua prima nota!</p>
                <a href="dashboard.php" style="color: var(--gold);">Vai alla mappa</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>