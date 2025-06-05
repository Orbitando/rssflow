<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/layout_admin.php';

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

render_header_admin('Gestione Categorie');
?>
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
                <ul class="list-group list-group-flush mb-3 flex-grow-1"></ul>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.copy-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const link = location.origin + btn.getAttribute('data-link');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(function() {
                btn.textContent = 'Copiato!';
                setTimeout(() => btn.textContent = 'Copia link feed', 1500);
            }).catch(function(err) {
                console.error('Errore nella copia:', err);
                fallbackCopyTextToClipboard(link, btn);
            });
        } else {
            fallbackCopyTextToClipboard(link, btn);
        }
    });
});

function fallbackCopyTextToClipboard(text, btn) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    // Avoid scrolling to bottom
    textArea.style.position = "fixed";
    textArea.style.top = 0;
    textArea.style.left = 0;
    textArea.style.width = "2em";
    textArea.style.height = "2em";
    textArea.style.padding = 0;
    textArea.style.border = "none";
    textArea.style.outline = "none";
    textArea.style.boxShadow = "none";
    textArea.style.background = "transparent";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            btn.textContent = 'Copiato!';
            setTimeout(() => btn.textContent = 'Copia link feed', 1500);
        } else {
            btn.textContent = 'Errore copia';
            setTimeout(() => btn.textContent = 'Copia link feed', 1500);
        }
    } catch (err) {
        console.error('Fallback copia non riuscita', err);
        btn.textContent = 'Errore copia';
        setTimeout(() => btn.textContent = 'Copia link feed', 1500);
    }

    document.body.removeChild(textArea);
}
</script>
<?php
render_footer_admin();
?>
