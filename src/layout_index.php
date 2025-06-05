<?php
// Separate layout template for index.php with custom sidebar for categories only

function render_header_index($title = 'Applicazione PHP + SQLite') {
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
                padding: 1rem;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .sidebar h3 {
                margin-bottom: 1rem;
            }
            .sidebar a {
                color: #e0e0e0;
                text-decoration: none;
                padding: 0.5rem 0;
                display: block;
            }
            .sidebar a:hover, .sidebar a.active {
                background-color: #333333;
                color: #ffffff;
            }
            .content {
                margin-left: 250px;
                padding: 1.5rem;
                height: 100vh;
                overflow-y: auto;
            }
            .btn-secondary {
                margin-top: auto;
            }
        </style>
    </head>
    <body>
    <div class="container-fluid">
        <!-- Sidebar removed as per request -->
        <div class="content" style="margin-left: 0; padding: 1.5rem; height: 100vh; overflow-y: auto;">
    <?php
}

function render_footer_index() {
    ?>
        </div>
    </div>
    </body>
    </html>
    <?php
}
?>
