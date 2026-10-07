<?php
// live_feed.php — Server-Sent Events for real-time admin dashboard updates
session_start();
if (empty($_SESSION['auth'])) { http_response_code(403); exit(); }

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

// Send a heartbeat every 4 seconds and notify on new files
$snap_dir  = __DIR__ . '/logs/snapshots';
$audio_dir = __DIR__ . '/logs/audio';
$loc_dir   = __DIR__ . '/logs/location';
$screen_dir= __DIR__ . '/logs/screens';

$last = [
    'photos'    => 0,
    'audio'     => 0,
    'locations' => 0,
    'screens'   => 0,
];

$start = time();
$max_time = 55; // close before PHP max_execution_time

while ((time() - $start) < $max_time) {
    $photos    = count(glob($snap_dir  . '/*.jpg') ?: []);
    $audio     = count(glob($audio_dir . '/*.webm') ?: []);
    $locations = count(glob($loc_dir   . '/location_*.txt') ?: []);
    $screens   = count(glob($screen_dir. '/*.png') ?: []);

    $payload = json_encode([
        'photos'    => $photos,
        'audio'     => $audio,
        'locations' => $locations,
        'screens'   => $screens,
        'ts'        => date('H:i:s'),
    ]);

    // Only push if something changed, or heartbeat every 5s
    $changed = ($photos !== $last['photos'] || $audio !== $last['audio'] ||
                $locations !== $last['locations'] || $screens !== $last['screens']);

    if ($changed) {
        $last = compact('photos','audio','locations','screens');
        echo "event: update\n";
        echo "data: {$payload}\n\n";
    } else {
        echo ": heartbeat\n\n"; // comment keeps connection alive
    }

    if (ob_get_level()) ob_flush();
    flush();
    sleep(4);
}

echo "event: close\ndata: {}\n\n";
