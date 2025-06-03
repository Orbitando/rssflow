<?php
require_once __DIR__ . '/middleware.php';

require_auth();
$user = current_user();
$is_admin = is_admin();

$id = $_GET['id'] ?? null;
$categoria_id = $_GET['categoria_id'] ?? null;
if (!$id || !$categoria_id) {
    http_response_code(400);
    exit('Parametri mancanti.');
}

// Carica il feed
$stmt = $db->prepare("SELECT * FROM feed WHERE id = ?");
$stmt->execute([$id]);
$feed = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$feed) {
    http_response_code(404);
    exit('Feed non trovato.');
}

// Carica la categoria per controllo permessi
$stmt = $db->prepare("SELECT * FROM categorie WHERE id = ?");
$stmt->execute([$categoria_id]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cat) {
    http_response_code(404);
    exit('Categoria non trovata.');
}

// Solo admin o proprietario
if (!$is_admin && $cat['utente_id'] != $user['id']) {
    http_response_code(403);
    exit('Accesso negato.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("DELETE FROM feed WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: feed_gestione.php?categoria_id=' . $categoria_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Cancella feed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1>Cancella feed</h1>
    <div class="alert alert-warning">
        Sei sicuro di voler cancellare il feed <b><?= htmlspecialchars($feed['url']) ?></b>?
    </div>
    <form method="post">
        <button type="submit" class="btn btn-danger">Conferma cancellazione</button>
        <a href="feed_gestione.php?categoria_id=<?= $categoria_id ?>" class="btn btn-secondary">Annulla</a>
    </form>
</div>
</body>
</html>
