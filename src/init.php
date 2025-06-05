<?php
// Percorso del database SQLite
$dbPath = __DIR__ . '/../data/app.sqlite';

// Crea la cartella data se non esiste
if (!is_dir(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0777, true);
}

// Connessione al database
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Avvia la sessione se non già avviata
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Crea la tabella utenti se non esiste
$db->exec("
    CREATE TABLE IF NOT EXISTS utenti (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        ruolo TEXT NOT NULL CHECK(ruolo IN ('admin', 'utente'))
    )
");

// Crea la tabella categorie se non esiste
$db->exec("
    CREATE TABLE IF NOT EXISTS categorie (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        utente_id INTEGER NOT NULL,
        rimuovi_duplicati INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (utente_id) REFERENCES utenti(id) ON DELETE CASCADE
    )
");

// Crea la tabella password_reset_tokens se non esiste
$db->exec("
    CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expires_at INTEGER NOT NULL,
        FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
    )
");

// Crea la tabella remember_tokens se non esiste
$db->exec("
    CREATE TABLE IF NOT EXISTS remember_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expires_at INTEGER NOT NULL,
        FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
    )
");

// Crea la tabella feed se non esiste
$db->exec("
    CREATE TABLE IF NOT EXISTS feed (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        url TEXT NOT NULL,
        categoria_id INTEGER NOT NULL,
        FOREIGN KEY (categoria_id) REFERENCES categorie(id) ON DELETE CASCADE
    )
");

// Crea la tabella feed_cache se non esiste
$db->exec("
    CREATE TABLE IF NOT EXISTS feed_cache (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        feed_id INTEGER NOT NULL,
        titolo TEXT,
        link TEXT,
        descrizione TEXT,
        pubDate TEXT,
        guid TEXT,
        data_cache INTEGER NOT NULL,
        FOREIGN KEY (feed_id) REFERENCES feed(id) ON DELETE CASCADE
    )
");

// Crea un admin di default se non esiste (email: admin@example.com, password: admin123)
$adminEmail = 'admin@example.com';
$adminPassword = 'admin123';
$adminNome = 'Admin';

$stmt = $db->prepare("SELECT COUNT(*) FROM utenti WHERE email = ?");
$stmt->execute([$adminEmail]);
if ($stmt->fetchColumn() == 0) {
    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $insert = $db->prepare("INSERT INTO utenti (nome, email, password_hash, ruolo) VALUES (?, ?, ?, 'admin')");
    $insert->execute([$adminNome, $adminEmail, $passwordHash]);
}
?>
