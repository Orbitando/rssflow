<?php
require_once __DIR__ . '/auth.php';

$error = '';

if (is_authenticated()) {
    // Se già autenticato, reindirizza (es. a index.php)
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] == '1';
    if (login($email, $password, $remember)) {
        // Login riuscito: reindirizza in base al ruolo
        if (is_admin()) {
            // Redirect admin to control panel
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = 'Email o password non corretti.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: sans-serif; background: #f8f8f8; margin: 0; padding: 2em; }
        .container { background: #fff; padding: 2em; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 2px 8px #0001; }
        h1 { color: #2c3e50; }
        .error { color: #c0392b; margin-bottom: 1em; }
        label { display: block; margin-top: 1em; }
        input[type="email"], input[type="password"] { width: 100%; padding: 0.5em; margin-top: 0.2em; }
        button { margin-top: 1.5em; padding: 0.7em 2em; background: #2c3e50; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #34495e; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
<form method="post" autocomplete="off">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required autofocus>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <div class="form-check" style="margin-top: 1em;">
                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Ricordami
                </label>
            </div>
            <button type="submit" style="margin-top: 1em;">Accedi</button>
        </form>
        <p><a href="forgot_password.php">Password dimenticata?</a></p>
        <p style="margin-top:2em;font-size:0.9em;color:#888;">
            Admin di default: <b>admin@example.com</b> / <b>admin123</b>
        </p>
    </div>
</body>
</html>
