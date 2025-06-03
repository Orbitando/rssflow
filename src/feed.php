<?php
require_once __DIR__ . '/init.php';

header('Content-Type: application/rss+xml; charset=utf-8');

$slug = $_GET['categoria'] ?? '';
if (!$slug) {
    http_response_code(400);
    echo "Parametro categoria mancante.";
    exit;
}

// Recupera categoria
$stmt = $db->prepare("SELECT * FROM categorie WHERE slug = ?");
$stmt->execute([$slug]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cat) {
    http_response_code(404);
    echo "Categoria non trovata.";
    exit;
}

// Recupera feed associati
$stmt = $db->prepare("SELECT url FROM feed WHERE categoria_id = ?");
$stmt->execute([$cat['id']]);
$feeds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (count($feeds) === 0) {
    http_response_code(404);
    echo "Nessun feed associato a questa categoria.";
    exit;
}

// Funzione per scaricare e parsare feed RSS
function fetch_feed_items($url) {
    $content = @file_get_contents($url);
    if (!$content) return [];
    $xml = @simplexml_load_string($content);
    if (!$xml) return [];
    $items = [];
    if (isset($xml->channel->item)) {
        foreach ($xml->channel->item as $item) {
            $items[] = $item;
        }
    } elseif (isset($xml->entry)) { // Atom feed
        foreach ($xml->entry as $item) {
            $items[] = $item;
        }
    }
    return $items;
}

function remove_duplicates($items) {
    $seen = [];
    $result = [];
    foreach ($items as $item) {
        $guid = (string)($item->guid ?? $item->link ?? '');
        if ($guid === '') {
            // fallback: title + pubDate
            $guid = md5((string)$item->title . (string)($item->pubDate ?? $item->updated ?? ''));
        }
        if (!isset($seen[$guid])) {
            $seen[$guid] = true;
            $result[] = $item;
        }
    }
    return $result;
}

// Aggrega tutti gli item
$all_items = [];
foreach ($feeds as $feed_url) {
    $items = fetch_feed_items($feed_url);
    $all_items = array_merge($all_items, $items);
}

// Rimuovi duplicati se impostato
if ($cat['rimuovi_duplicati']) {
    $all_items = remove_duplicates($all_items);
}

// Ordina per data (se presente)
usort($all_items, function($a, $b) {
    $dateA = strtotime((string)($a->pubDate ?? $a->updated ?? ''));
    $dateB = strtotime((string)($b->pubDate ?? $b->updated ?? ''));
    return $dateB <=> $dateA;
});

// Limita a 20 item
$all_items = array_slice($all_items, 0, 20);

// Output RSS
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0">
<channel>
    <title>Feed aggregato: <?= htmlspecialchars($cat['nome']) ?></title>
    <link><?= htmlspecialchars((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?></link>
    <description>Feed aggregato per la categoria <?= htmlspecialchars($cat['nome']) ?></description>
    <language>it-it</language>
    <?php foreach ($all_items as $item): ?>
        <item>
            <title><?= htmlspecialchars((string)$item->title) ?></title>
            <link><?= htmlspecialchars((string)$item->link) ?></link>
            <description><![CDATA[<?= (string)$item->description ?? $item->summary ?? '' ?>]]></description>
            <pubDate><?= htmlspecialchars((string)$item->pubDate ?? $item->updated ?? '') ?></pubDate>
            <guid><?= htmlspecialchars((string)$item->guid ?? $item->link ?? '') ?></guid>
        </item>
    <?php endforeach; ?>
</channel>
</rss>
