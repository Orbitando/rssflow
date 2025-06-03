<?php
require_once __DIR__ . '/../src/init.php';

function fetch_feed_items($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'PHP Feed Aggregator/1.0'
        ]
    ]);
    $content = @file_get_contents($url, false, $context);
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

// Pulisce la cache esistente
$db->exec("DELETE FROM feed_cache");

$stmt = $db->query("SELECT id, url FROM feed");
$feeds = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($feeds as $feed) {
    $items = fetch_feed_items($feed['url']);
    $now = time();
    foreach ($items as $item) {
        $titolo = (string)($item->title ?? '');
        $link = (string)($item->link ?? '');
        $descrizione = (string)($item->description ?? $item->summary ?? '');
        $pubDate = (string)($item->pubDate ?? $item->updated ?? '');
        $guid = (string)($item->guid ?? $link);

        $insert = $db->prepare("INSERT INTO feed_cache (feed_id, titolo, link, descrizione, pubDate, guid, data_cache) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$feed['id'], $titolo, $link, $descrizione, $pubDate, $guid, $now]);
    }
}
?>
