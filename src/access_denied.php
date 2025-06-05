<?php
require_once __DIR__ . '/layout_index.php';

render_header('Accesso Negato');
?>
<div class="container py-4">
    <h1>Accesso Negato</h1>
    <p>Non hai i permessi necessari per accedere a questa pagina.</p>
    <a href="login.php" class="btn btn-primary">Vai al Login</a>
</div>
<?php
render_footer();
?>
