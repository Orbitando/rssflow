<?php
// Public RSS feed aggregator for a category by fetching original feed URLs

header('Content-Type: application/rss+xml; charset=utf-8');

require_once __DIR__ . '/init.php';

$categoria_slug = $_GET['categoria'] ?? null;
if (!$categoria_slug) {
    http_response_code(400);
    echo "Categoria mancante.";
    exit;
}

// Load category
$stmt = $db->prepare("SELECT * FROM categorie WHERE slug = ?");
$stmt->execute([$categoria_slug]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cat) {
    http_response_code(404);
    echo "Categoria non trovata.";
    exit;
}

// Fetch feeds in category
$stmt = $db->prepare("SELECT url FROM feed WHERE categoria_id = ?");
$stmt->execute([$cat['id']]);
$feed_urls = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (count($feed_urls) === 0) {
    // No feeds in category, output empty RSS feed
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    ?>
    <rss version="2.0">
    <channel>
        <title><?= htmlspecialchars($cat['nome']) ?> - RSS Aggregator</title>
        <link><?= htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?></link>
        <description>Aggregated RSS feed for category <?= htmlspecialchars($cat['nome']) ?></description>
        <language>it-it</language>
        <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
    </channel>
    </rss>
    <?php
    exit;
}

// Function to fetch and parse RSS feed from URL
function fetch_feed_items($url) {
    $items = [];
    $content = @file_get_contents($url);
    if ($content === false) {
        return $items;
    }
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xml === false) {
        return $items;
    }
    if (isset($xml->channel->item)) {
        $count = 0;
        foreach ($xml->channel->item as $item) {
            if ($count >= 5) break;
            $items[] = [
                'title' => (string)$item->title,
                'link' => (string)$item->link,
                'description' => (string)$item->description,
                'pubDate' => (string)$item->pubDate,
            ];
            $count++;
        }
    }
    return $items;
}

// Aggregate items from all feeds
$all_items = [];
foreach ($feed_urls as $url) {
    $feed_items = fetch_feed_items($url);
    $all_items = array_merge($all_items, $feed_items);
}

// Optionally remove duplicates by link
if ($cat['rimuovi_duplicati']) {
    $unique_items = [];
    $links = [];
    foreach ($all_items as $item) {
        if (!in_array($item['link'], $links)) {
            $links[] = $item['link'];
            $unique_items[] = $item;
        }
    }
    $all_items = $unique_items;
}

// Sort items by pubDate descending
usort($all_items, function($a, $b) {
    return strtotime($b['pubDate']) - strtotime($a['pubDate']);
});

// Generate RSS feed XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0">
<channel>
    <title><?= htmlspecialchars($cat['nome']) ?> - RSS Aggregator</title>
    <link><?= htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?></link>
    <description>Aggregated RSS feed for category <?= htmlspecialchars($cat['nome']) ?></description>
    <language>it-it</language>
    <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
    <?php foreach ($all_items as $item): ?>
    <item>
        <title><?= htmlspecialchars($item['title']) ?></title>
        <link><?= htmlspecialchars($item['link']) ?></link>
        <description><![CDATA[<?= $item['description'] ?>]]></description>
        <pubDate><?= htmlspecialchars($item['pubDate']) ?></pubDate>
        <guid><?= htmlspecialchars($item['link']) ?></guid>
    </item>
    <?php endforeach; ?>
</channel>
</rss>
