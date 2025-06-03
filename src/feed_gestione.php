<?php
require_once __DIR__ . '/middleware.php';

require_auth();
$user = current_user();
$is_admin = is_admin();

$categoria_id = $_GET['categoria_id'] ?? null;
if (!$categoria_id) {
    http_response_code(400);
    exit('ID categoria mancante.');
}

// Carica la categoria
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

$error = '';
// Aggiunta feed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    if ($url === '') {
        $error = 'URL obbligatorio.';
    } else {
        // Verifica duplicati
        $stmt = $db->prepare("SELECT id FROM feed WHERE url = ? AND categoria_id = ?");
        $stmt->execute([$url, $categoria_id]);
        if ($stmt->fetch()) {
            $error = 'Feed già presente in questa categoria.';
        } else {
            $stmt = $db->prepare("INSERT INTO feed (url, categoria_id) VALUES (?, ?)");
            $stmt->execute([$url, $categoria_id]);
            header('Location: feed_gestione.php?categoria_id=' . $categoria_id);
            exit;
        }
    }
}

// Elenco feed
$stmt = $db->prepare("SELECT * FROM feed WHERE categoria_id = ? ORDER BY id");
$stmt->execute([$categoria_id]);
$feeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione feed - <?= htmlspecialchars($cat['nome']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1>Feed per la categoria: <?= htmlspecialchars($cat['nome']) ?></h1>
    <a href="categorie.php" class="btn btn-secondary mb-3">Torna alle categorie</a>
    <form method="post" class="row g-3 mb-4" autocomplete="off">
        <div class="col-md-8">
            <input type="url" name="url" class="form-control" placeholder="URL feed RSS" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-success w-100">Aggiungi feed</button>
        </div>
        <?php if ($error): ?>
            <div class="col-12">
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>URL</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($feeds as $feed): ?>
            <tr>
                <td><?= htmlspecialchars($feed['url']) ?></td>
                <td>
                    <a href="feed_delete.php?id=<?= $feed['id'] ?>&categoria_id=<?= $categoria_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancellare questo feed?')">Elimina</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (count($feeds) === 0): ?>
            <tr><td colspan="2" class="text-muted">Nessun feed presente.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
