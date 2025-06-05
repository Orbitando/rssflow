<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/auth.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    exit('Token mancante.');
}

// Check token validity
$stmt = $db->prepare("SELECT user_id, expires_at FROM password_reset_tokens WHERE token = ?");
$stmt->execute([$token]);
$token_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$token_data || $token_data['expires_at'] < time()) {
    exit('Token non valido o scaduto.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password === '' || $password_confirm === '') {
        $error = 'Entrambi i campi password sono obbligatori.';
    } elseif ($password !== $password_confirm) {
        $error = 'Le password non corrispondono.';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE utenti SET password_hash = ? WHERE id = ?");
        $stmt->execute([$password_hash, $token_data['user_id']]);

        // Delete token after use
        $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        $stmt->execute([$token]);

        $success = 'Password aggiornata con successo. Puoi ora effettuare il login.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1>Reset Password</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <p><a href="login.php">Vai al login</a></p>
    <?php else: ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="password" class="form-label">Nuova Password</label>
                <input type="password" class="form-control" id="password" name="password" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password_confirm" class="form-label">Conferma Password</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-primary">Aggiorna Password</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
