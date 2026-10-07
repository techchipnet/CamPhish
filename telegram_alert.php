<?php
// telegram_alert.php — legacy shim, now delegates to alerts.php
if (!function_exists('telegram_alert')) {
    require_once __DIR__ . '/alerts.php';
}
