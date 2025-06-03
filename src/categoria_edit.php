<?php
require_once __DIR__ . '/middleware.php';

require_auth();
$user = current_user();
$is_admin = is_admin();

$id = $_GET['id'] ?? null;
$editing = false;
$nome = '';
$slug = '';
$rimuovi_duplicati = 0;
$utente_id = $user['id'];
$error = '';

if ($id) {
    // Modifica: carica dati categoria
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
    $editing = true;
    $nome = $cat['nome'];
    $slug = $cat['slug'];
    $rimuovi_duplicati = $cat['rimuovi_duplicati'];
    $utente_id = $cat['utente_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $rimuovi_duplicati = isset($_POST['rimuovi_duplicati']) ? 1 : 0;
    if ($nome === '' || $slug === '') {
        $error = 'Tutti i campi sono obbligatori.';
    } else {
        // Verifica unicità slug
        $stmt = $db->prepare("SELECT id FROM categorie WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id ?? 0]);
        if ($stmt->fetch()) {
            $error = 'Slug già in uso.';
        } else {
            if ($editing) {
                $stmt = $db->prepare("UPDATE categorie SET nome = ?, slug = ?, rimuovi_duplicati = ? WHERE id = ?");
                $stmt->execute([$nome, $slug, $rimuovi_duplicati, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO categorie (nome, slug, utente_id, rimuovi_duplicati) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $slug, $utente_id, $rimuovi_duplicati]);
            }
            header('Location: categorie.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?= $editing ? 'Modifica' : 'Nuova' ?> categoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1><?= $editing ? 'Modifica' : 'Nuova' ?> categoria</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" class="mt-3" autocomplete="off">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" required value="<?= htmlspecialchars($nome) ?>">
        </div>
        <div class="mb-3">
            <label for="slug" class="form-label">Slug (univoco, senza spazi)</label>
            <input type="text" class="form-control" id="slug" name="slug" required value="<?= htmlspecialchars($slug) ?>">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="1" id="rimuovi_duplicati" name="rimuovi_duplicati" <?= $rimuovi_duplicati ? 'checked' : '' ?>>
            <label class="form-check-label" for="rimuovi_duplicati">
                Rimuovi duplicati nel feed aggregato
            </label>
        </div>
        <button type="submit" class="btn btn-success"><?= $editing ? 'Salva modifiche' : 'Crea categoria' ?></button>
        <a href="categorie.php" class="btn btn-secondary">Annulla</a>
    </form>
</div>
</body>
</html>
