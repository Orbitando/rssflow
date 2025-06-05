<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../middleware.php';

require_auth();

$user = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($email === '') {
        $error = 'L\'email è obbligatoria.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email non valida.';
    } elseif ($password !== '' && $password !== $password_confirm) {
        $error = 'Le password non corrispondono.';
    } else {
        global $db;
        // Check if email is already used by another user
        $stmt = $db->prepare("SELECT id FROM utenti WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $error = 'Email già in uso da un altro utente.';
        } else {
            if ($password !== '') {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE utenti SET email = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([$email, $password_hash, $user['id']]);
            } else {
                $stmt = $db->prepare("UPDATE utenti SET email = ? WHERE id = ?");
                $stmt->execute([$email, $user['id']]);
            }
            $success = 'Profilo aggiornato con successo.';
            // Update session email if stored
            $_SESSION['user_email'] = $email;
            // Refresh user data
            $user = current_user();
        }
    }
}

require_once __DIR__ . '/layout_admin.php';
render_header_admin('Modifica Profilo');
?>
<div class="container py-4" style="max-width: 600px;">
    <h1>Modifica Profilo</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Nuova Password (lascia vuoto per mantenere quella attuale)</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
        </div>
        <div class="mb-3">
            <label for="password_confirm" class="form-label">Conferma Nuova Password</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary">Salva modifiche</button>
        <a href="index.php" class="btn btn-secondary">Annulla</a>
    </form>
</div>
<?php
render_footer_admin();
?>
