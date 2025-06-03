<?php
require_once __DIR__ . '/init.php';

// Funzione per autenticare l'utente
function login($email, $password) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM utenti WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        // Salva dati essenziali in sessione
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['ruolo'];
        return true;
    }
    return false;
}

// Funzione per effettuare il logout
function logout() {
    session_unset();
    session_destroy();
}

// Verifica se l'utente è autenticato
function is_authenticated() {
    return isset($_SESSION['user_id']);
}

// Restituisce i dati dell'utente autenticato
function current_user() {
    global $db;
    if (!is_authenticated()) return null;
    $stmt = $db->prepare("SELECT * FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verifica se l'utente autenticato è admin
function is_admin() {
    return is_authenticated() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
?>
