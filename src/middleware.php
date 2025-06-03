<?php
require_once __DIR__ . '/auth.php';

// Protegge una pagina: accesso solo se autenticato
function require_auth() {
    if (!is_authenticated()) {
        header('Location: login.php');
        exit;
    }
}

// Protegge una pagina: accesso solo se admin
function require_admin() {
    if (!is_admin()) {
        http_response_code(403);
        echo "<h1>403 Accesso negato</h1><p>Questa pagina è riservata agli amministratori.</p>";
        exit;
    }
}
?>
