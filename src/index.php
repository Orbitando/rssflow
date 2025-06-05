<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout_index.php';
require_once __DIR__ . '/init.php';

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$user = current_user();

// Fetch categories for the logged-in user
$stmt = $db->prepare("SELECT * FROM categorie WHERE utente_id = ? ORDER BY nome");
$stmt->execute([$user['id']]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

render_header_index('Lettore Feed');
?>
<div style="display: flex; height: 100vh;">
    <nav style="width: 250px; background-color: #1e1e1e; padding: 1rem; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <h3 style="color: #e0e0e0; margin-bottom: 1rem;">Categorie</h3>
            <?php if (count($categories) === 0): ?>
                <p style="color: #e0e0e0;">Nessuna categoria disponibile.</p>
                <a href="admin/categorie.php" class="btn btn-primary">Aggiungi Categoria</a>
            <?php else: ?>
                <ul id="category-list" style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="#" class="category-link" data-slug="<?= htmlspecialchars($category['slug']) ?>" style="color: #e0e0e0; text-decoration: none; display: block; padding: 0.5rem 0;">
                                <?= htmlspecialchars($category['nome']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </nav>
    <div style="flex-grow: 1; display: flex; flex-direction: column; background-color: #121212; color: #e0e0e0;">
        <div style="display: flex; justify-content: flex-end; padding: 1rem; background-color: #1e1e1e;">
            <a href="admin/index.php" title="Admin" style="color: #e0e0e0; text-decoration: none; font-size: 1.2rem; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" viewBox="0 0 24 24" style="margin-right: 0.5rem;">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                Admin
            </a>
        </div>
        <main id="feed-content" style="padding: 0;">
            <p>Seleziona una categoria per visualizzare i feed.</p>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryLinks = document.querySelectorAll('.category-link');
    const feedContent = document.getElementById('feed-content');

    function clearActive() {
        categoryLinks.forEach(link => link.style.backgroundColor = '');
    }

    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const categorySlug = this.getAttribute('data-slug');
            clearActive();
            this.style.backgroundColor = '#333333';

            fetch('feed.php?categoria=' + encodeURIComponent(categorySlug))
                .then(response => response.text())
                .then(xmlText => {
                    // Parse RSS XML and display items
                    const parser = new DOMParser();
                    const xmlDoc = parser.parseFromString(xmlText, "application/xml");
                    const items = xmlDoc.querySelectorAll('item');
                    if (items.length === 0) {
                        feedContent.innerHTML = '<p>Nessun articolo disponibile.</p>';
                        return;
                    }
                    let html = '';
                    items.forEach(item => {
                        const title = item.querySelector('title')?.textContent || '';
                        const link = item.querySelector('link')?.textContent || '#';
                        const description = item.querySelector('description')?.textContent || '';
                        const pubDate = item.querySelector('pubDate')?.textContent || '';
                        html += `
                            <div class="feed-item">
                                <div class="feed-title"><a href="${link}" target="_blank" rel="noopener noreferrer">${title}</a></div>
                                <div class="feed-description">${description}</div>
                                <div class="feed-time">${pubDate}</div>
                            </div>
                        `;
                    });
                    feedContent.innerHTML = html;
                })
                .catch(err => {
                    feedContent.innerHTML = '<p>Errore nel caricamento dei feed.</p>';
                });
        });
    });
});
</script>

<style>
.feed-item {
    background-color: #1e1e1e;
    border-radius: 5px;
    padding: 1rem;
    margin-bottom: 1rem;
    color: #e0e0e0;
}
.feed-title a {
    color: #61dafb;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.1rem;
}
.feed-title a:hover {
    text-decoration: underline;
}
.feed-description {
    margin-top: 0.5rem;
    font-size: 0.9rem;
}
.feed-time {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #888;
}
</style>

<?php
render_footer_index();
?>
