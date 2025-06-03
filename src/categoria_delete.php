<?php
require_once __DIR__ . '/middleware.php';

require_auth();
$user = current_user();
$is_admin = is_admin();

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit('ID categoria mancante.');
}

// Carica la categoria
$stmt = $db->prepare("SELECT * FROM categorie WHERE id = ?");
$stmt->execute([$id]);
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
    // Cancella la categoria (i feed vengono cancellati in cascata)
    $stmt = $db->prepare("DELETE FROM categorie WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: categorie.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Cancella categoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1>Cancella categoria</h1>
    <div class="alert alert-warning">
        Sei sicuro di voler cancellare la categoria <b><?= htmlspecialchars($cat['nome']) ?></b>?
        <br>
        <small>I feed associati saranno eliminati.</small>
    </div>
    <form method="post">
        <button type="submit" class="btn btn-danger">Conferma cancellazione</button>
        <a href="categorie.php" class="btn btn-secondary">Annulla</a>
    </form>
</div>
</body>
</html>
