<?php
session_start();
require '../settings/database.php';

// 1. Protezione pagina: Reindirizzamento se non loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Recupero note: Usiamo 'monument_name' e 'created_at' che abbiamo allineato nel DB
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
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&family=Cinzel:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --gold: #aa8b56; 
            --gold-bright: #d4af37;
            --glass: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Titillium Web', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.92), rgba(0,0,0,0.92)), url('../img/background.jpg');
            background-size: cover; background-attachment: fixed;
            color: white; margin: 0; padding: 20px;
        }

        .container { max-width: 1100px; margin: 40px auto; }

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
            border: 1px solid rgba(255,255,255,0.1);
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
            box-shadow: 0 10px 20px rgba(0,0,0,0.4); 
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
            color: rgba(255,255,255,0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255,255,255,0.05);
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

        @media (max-width: 600px) {
            .header { flex-direction: column; gap: 20px; text-align: center; }
        }

    </style>
</head>
<body>

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
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="<?php echo ($i <= $note['rating']) ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="note-text">
                        "<?php echo nl2br(htmlspecialchars($note['note_text'])); ?>"
                    </div>

                    <div class="note-footer">
                        <span><i class="fa fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?></span>
                        <span style="text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fa fa-fingerprint"></i> <?php echo htmlspecialchars($note['monument_id']); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-compass" style="font-size: 4rem; color: var(--gold); margin-bottom: 20px; opacity: 0.5;"></i>
                <h2 style="font-family: 'Titillium Web', sans-serif; color: var(--gold);">No Notes Yet</h2>
                <p style="opacity: 0.7; margin-bottom: 25px;">Your diary is still empty. Visit the archaeological sites in Turin on the map to start documenting your discoveries.</p>
                <a href="../dashboard.php" class="btn-back">Start Exploring</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>