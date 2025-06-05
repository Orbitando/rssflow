<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/layout_admin.php';

require_auth();

render_header_admin('Admin Dashboard');
?>
    <h1>Admin Dashboard</h1>
    <p>Benvenuto, amministratore!</p>
    <nav>
        <ul>
            <li><a href="categorie.php">Gestione Categorie</a></li>
            <li><a href="feed_gestione.php?categoria_id=1">Gestione Feed</a></li>
            <li><a href="users.php">Gestione Utenti</a></li>
            <li><a href="../index.php">Torna alla home</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
<?php
render_footer_admin();
?>
