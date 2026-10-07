# CamPhish — Feature Update Changelog

## Version 3.0 — Major Feature Release

### 🆕 New Files
| File | Purpose |
|---|---|
| `config.php` | **Central config** for all settings (replaces multiple scattered configs) |
| `alerts.php` | **Unified multi-channel alert dispatcher** |
| `helpers.php` | **Shared utility functions** |
| `live_feed.php` | **Server-Sent Events** for real-time dashboard push |
| `download_zip.php` | **One-click ZIP download** of all logs |
| `session_api.php` | **JSON API** for session/timeline data |
| `templates/ARFilter.html` | **AR Filter lure** template |
| `templates/PrizeClaim.html` | **QR Prize claim** lure template |

---

### 📸 Capture Enhancements

- **Interval mode**: Auto-captures a photo every 30 seconds after initial burst
- **Rear camera burst**: 3 additional shots from rear camera (where supported)
- **Video clips**: 8-second video clip recorded and sent to server
- **Audio recording**: 15-second microphone capture sent to `audio.php`
- **Screen capture**: `screen_shot` field in `post.php` saves screenshots to `logs/screens/`
- **Higher quality**: JPEG quality bumped from 0.9 → 0.92
- **Snap-on-demand**: AR template has a shutter button for manual captures

---

### 🧠 Enhanced Device Fingerprinting

New fields captured via `device_info.php`:
- Canvas fingerprint hash
- WebGL vendor/renderer string
- Color depth
- Browser plugin list
- WiFi/connection type
- Device memory (GB)

---

### 🗺️ Location Upgrades

- **Altitude** — from `Geolocation.altitude`
- **Speed** — from `Geolocation.speed` (m/s)
- **Heading** — from `Geolocation.heading` + compass direction (N/NE/E…)
- **Reverse geocoding** — lat/lon → human-readable address via Nominatim (no API key)
- **IP geolocation enrichment** — mobile/proxy/hosting flags from ip-api.com

---

### 📬 Alert System Overhaul (`alerts.php`)

- **Telegram photo sending**: First captured photo is sent directly as image via Bot API
- **Discord webhook**: Rich embeds with color-coded event types
- **Email alerts**: HTML email via PHP `mail()` or SMTP
- **Generic webhook**: POST JSON to any URL (Zapier, Make.com, n8n, etc.)
- **Back-compat shim**: `telegram_alert()` still works everywhere

---

### 🔒 Security & Control

- **Bot detection**: Detects 30+ known bots/crawlers and serves harmless response
- **Link expiry**: Configure time-based or visit-count-based link death
- **Autokill**: Server signals cleanup after first successful photo
- **UUID sessions**: Each visitor assigned a UUID; all events grouped per session
- **Admin password**: Now also supports bcrypt-hashed passwords

---

### 🖥️ Admin Dashboard (`admin.php`) — Complete Rebuild

- **Sidebar navigation** with 9 sections
- **Leaflet.js map** with GPS pin markers and popups (address + Maps link)
- **Lightbox viewer** for photos: full-screen, keyboard nav (←/→/Esc)
- **Inline audio player** for every recording
- **Session timeline** — click any session UUID to see a chronological activity feed
- **Live SSE push** — counts update in real-time without page reload
- **Search/filter** for visitors (by IP), devices (text search), photos (by camera)
- **Screen captures** page
- **ZIP download** button (calls `download_zip.php`)
- **JSON export** updated with all new data fields

---

### 🎭 New Lure Templates

| Template | Scenario |
|---|---|
| `templates/KYCVerify.html` | Bank/crypto identity verification — face scan |
| `templates/PrizeClaim.html` | Prize claim with QR scanner — auto-triggered |
| `templates/ARFilter.html` | AR face filter app — most natural permission request |

All templates include the full capture stack:
- Front burst (5 shots) → Rear burst (3 shots) → Video clip → Audio → Interval shots

---

### 🛠️ Infrastructure

- `cleanup.sh` — Updated to remove `logs/screens/` and `logs/sessions/`
- `index2.html` — BACKEND now uses `window.location.origin` (no hardcoded URL)
- All handlers now use `require_once alerts.php` for unified notifications
- All handlers now call `get_visitor_uuid()` for session tracking

---

### ⚙️ Quick Setup

1. Edit **`config.php`** — set `admin_password`, `telegram_token`, `telegram_chat_id`, and any other channels
2. Run the launcher: `bash camphish.sh` or `python3 launcher.py`
3. Send the generated link to target
4. Open `admin.php` to monitor captures in real time
