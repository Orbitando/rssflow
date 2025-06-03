<?php
// Percorso del database SQLite
$dbPath = __DIR__ . '/../data/app.sqlite';

// Se la cartella data non esiste, la crea
if (!is_dir(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0777, true);
}

// Crea il database se non esiste e una tabella di esempio
if (!file_exists($dbPath)) {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS test (id INTEGER PRIMARY KEY, value TEXT)");
    $db->exec("INSERT INTO test (value) VALUES ('Benvenuto in PHP + SQLite!')");
} else {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// Recupera un valore di test
$stmt = $db->query("SELECT value FROM test LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$message = $row ? $row['value'] : 'Database vuoto';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>PHP + SQLite + Docker</title>
    <style>
        body { font-family: sans-serif; background: #f8f8f8; margin: 0; padding: 2em; }
        .container { background: #fff; padding: 2em; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0 2px 8px #0001; }
        h1 { color: #2c3e50; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Applicazione PHP + SQLite</h1>
        <p><?= htmlspecialchars($message) ?></p>
        <small>File: <code>src/index.php</code></small>
    </div>
</body>
</html>
