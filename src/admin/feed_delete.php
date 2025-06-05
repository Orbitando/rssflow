<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../middleware.php';

require_auth();
$user = current_user();
$is_admin = is_admin();

$id = $_GET['id'] ?? null;
$categoria_id = $_GET['categoria_id'] ?? null;

if (!$id || !$categoria_id) {
    http_response_code(400);
    exit('ID feed o categoria mancante.');
}

// Check ownership or admin
if (!$is_admin) {
    // Verify feed belongs to a category owned by the user
    $stmt = $db->prepare("SELECT f.id FROM feed f JOIN categorie c ON f.categoria_id = c.id WHERE f.id = ? AND c.utente_id = ?");
    $stmt->execute([$id, $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        exit('Accesso negato.');
    }
}

$stmt = $db->prepare("DELETE FROM feed WHERE id = ?");
$stmt->execute([$id]);

header('Location: feed_gestione.php?categoria_id=' . $categoria_id);
exit;
?>
