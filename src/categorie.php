<?php
require_once __DIR__ . '/middleware.php';

require_auth();
$user = current_user();
$is_admin = is_admin();

// Recupera le categorie (tutte se admin, solo proprie se utente)
if ($is_admin) {
    $stmt = $db->query("SELECT c.*, u.nome AS proprietario FROM categorie c LEFT JOIN utenti u ON c.utente_id = u.id ORDER BY c.nome");
    $categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->prepare("SELECT * FROM categorie WHERE utente_id = ? ORDER BY nome");
    $stmt->execute([$user['id']]);
    $categorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Categorie RSS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .copy-btn { cursor: pointer; }
        .feed-list { font-size: 0.95em; color: #555; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="categorie.php">RSSFlow</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <?php if (is_authenticated()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?= htmlspecialchars($user['nome']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-4">
    <h1 class="mb-4">Le tue categorie RSS</h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($categorie as $cat): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($cat['nome']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($cat['slug']) ?></h6>
                    <?php if ($is_admin): ?>
                        <p class="card-text"><small class="text-muted">Proprietario: <?= htmlspecialchars($cat['proprietario'] ?? '-') ?></small></p>
                    <?php endif; ?>
                    <ul class="list-group list-group-flush mb-3 flex-grow-1">
                        <?php
                            $stmtf = $db->prepare("SELECT * FROM feed WHERE categoria_id = ?");
                            $stmtf->execute([$cat['id']]);
                            $feeds = $stmtf->fetchAll(PDO::FETCH_ASSOC);
                            if (count($feeds) === 0) {
                                echo '<li class="list-group-item text-muted">Nessun feed</li>';
                            } else {
                                foreach ($feeds as $feed) {
                                    echo '<li class="list-group-item">' . htmlspecialchars($feed['url']) . '</li>';
                                }
                            }
                        ?>
                    </ul>
                    <div class="mt-auto d-flex justify-content-between">
                        <a href="feed_gestione.php?categoria_id=<?= $cat['id'] ?>" class="btn btn-sm btn-secondary">Gestisci feed</a>
                        <a href="categoria_edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning">Modifica</a>
                        <a href="categoria_delete.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancellare la categoria?')">Elimina</a>
                        <button class="btn btn-sm btn-info copy-btn" data-link="<?= htmlspecialchars('/feed.php?categoria=' . $cat['slug']) ?>">Copia link feed</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <a href="categoria_edit.php" class="btn btn-primary mt-4">Nuova categoria</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.copy-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const link = location.origin + btn.getAttribute('data-link');
        navigator.clipboard.writeText(link).then(function() {
            btn.textContent = 'Copiato!';
            setTimeout(() => btn.textContent = 'Copia link feed', 1500);
        });
    });
});
</script>
</body>
</html>
