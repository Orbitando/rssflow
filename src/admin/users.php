<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../middleware.php';

if (!is_admin()) {
    header('Location: ../access_denied.php');
    exit;
}
require_admin();

$error = '';
$success = '';

// Handle user deletion
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id === $_SESSION['user_id']) {
        $error = "Non puoi eliminare te stesso.";
    } else {
        $stmt = $db->prepare("DELETE FROM utenti WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success = "Utente eliminato con successo.";
    }
}

// Handle user addition or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ruolo = $_POST['ruolo'] ?? 'utente';

    if ($nome === '' || $email === '') {
        $error = "Nome e email sono obbligatori.";
    } else {
        if ($id) {
            // Update existing user
            if ($password !== '') {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE utenti SET nome = ?, email = ?, password_hash = ?, ruolo = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $password_hash, $ruolo, $id]);
            } else {
                $stmt = $db->prepare("UPDATE utenti SET nome = ?, email = ?, ruolo = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $ruolo, $id]);
            }
            $success = "Utente aggiornato con successo.";
        } else {
            // Add new user
            $stmt = $db->prepare("SELECT id FROM utenti WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email già in uso.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO utenti (nome, email, password_hash, ruolo) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $email, $password_hash, $ruolo]);
                $success = "Utente aggiunto con successo.";
            }
        }
    }
}

// Fetch users list
$stmt = $db->query("SELECT id, nome, email, ruolo FROM utenti ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If editing user
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT id, nome, email, ruolo FROM utenti WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/layout_admin.php';

require_admin();

render_header_admin('Gestione Utenti');
?>
<div class="container py-4">
    <h1>Gestione Utenti</h1>
    <a href="index.php" class="btn btn-secondary mb-3">Torna al pannello admin</a>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="mb-4">
        <input type="hidden" name="id" value="<?= $edit_user['id'] ?? '' ?>">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome utente</label>
            <input type="text" class="form-control" id="nome" name="nome" required value="<?= htmlspecialchars($edit_user['nome'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email utente</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password (lascia vuoto per non cambiare)</label>
            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
        </div>
        <div class="mb-3">
            <label for="ruolo" class="form-label">Ruolo</label>
            <select class="form-select" id="ruolo" name="ruolo">
                <option value="utente" <?= (isset($edit_user['ruolo']) && $edit_user['ruolo'] === 'utente') ? 'selected' : '' ?>>Utente</option>
                <option value="admin" <?= (isset($edit_user['ruolo']) && $edit_user['ruolo'] === 'admin') ? 'selected' : '' ?>>Amministratore</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><?= $edit_user ? 'Aggiorna' : 'Aggiungi' ?> utente</button>
        <?php if ($edit_user): ?>
            <a href="users.php" class="btn btn-secondary">Annulla</a>
        <?php endif; ?>
    </form>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Ruolo</th>
            <th>Azioni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['nome']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['ruolo']) ?></td>
                <td>
                    <a href="users.php?edit=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Modifica</a>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminare questo utente?')">Elimina</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
render_footer_admin();
?>
