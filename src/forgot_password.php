<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $error = 'Inserisci un indirizzo email.';
    } else {
        // Check if user exists
        $stmt = $db->prepare("SELECT id FROM utenti WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $error = 'Nessun utente trovato con questa email.';
        } else {
            // Generate token and expiration (1 hour)
            $token = bin2hex(random_bytes(16));
            $expires = time() + 3600;

            // Store token in database
            $stmt = $db->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);

            // Send email with reset link
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token";

            $subject = "Reset password";
            $message = "Clicca sul link per resettare la password: $reset_link";
            $headers = "From: no-reply@example.com\r\n";

            // Use mail() or PHPMailer here
            if (mail($email, $subject, $message, $headers)) {
                $success = 'Email di reset inviata.';
            } else {
                $error = 'Errore nell\'invio dell\'email.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Recupera Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h1>Recupera Password</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label for="email" class="form-label">Inserisci la tua email</label>
            <input type="email" class="form-control" id="email" name="email" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary">Invia email di reset</button>
    </form>
    <p><a href="login.php">Torna al login</a></p>
</div>
</body>
</html>
