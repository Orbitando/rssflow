<?php
// Separate layout template for admin area with full admin sidebar menu

function render_header_admin($title = 'Admin - Applicazione PHP + SQLite') {
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($title) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #121212;
                color: #e0e0e0;
                margin: 0;
                padding: 0;
            }
            .container-fluid {
                padding: 0;
            }
            .sidebar {
                background-color: #1e1e1e;
                height: 100vh;
                position: fixed;
                top: 0;
                left: 0;
                width: 250px;
                overflow-y: auto;
                padding-top: 1rem;
            }
            .sidebar a {
                color: #e0e0e0;
                display: block;
                padding: 0.75rem 1.25rem;
                text-decoration: none;
            }
            .sidebar a:hover, .sidebar a.active {
                background-color: #333333;
                color: #ffffff;
            }
            .content {
                margin-left: 250px;
                padding: 1.5rem;
                min-height: 100vh;
            }
            .navbar-dark {
                background-color: #1e1e1e;
            }
        </style>
    </head>
    <body>
    <div class="container-fluid">
        <div class="sidebar">
            <h3 class="text-center">Rss Flow - Admin</h3>
            <a href="index.php">Home</a>
            <a href="categorie.php">Categorie</a>
            <a href="feed_gestione.php?categoria_id=1">Gestione Feed</a>
            <?php if (is_admin()): ?>
                <a href="users.php">Gestione Utenti</a>
            <?php endif; ?>
            <a href="account_edit.php">Modifica Profilo</a>
            <a href="../logout.php">Logout</a>
 
        </div>
        <div class="content">
    <?php
}

function render_footer_admin() {
    ?>
        </div>
    </div>
    </body>
    </html>
    <?php
}
?>
