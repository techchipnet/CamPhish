# wifi_passwords.ps1
# Reads all saved WiFi passwords on this Windows device
# Generates a self-contained wifi_viewer.html with data embedded
#
# HOW TO USE:
#   Right-click -> Run with PowerShell  (as Administrator for best results)

$ErrorActionPreference = 'SilentlyContinue'
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host ""
Write-Host "=== WiFi Password Viewer ===" -ForegroundColor Cyan
Write-Host "Scanning saved networks..." -ForegroundColor Gray

$profileNames = netsh wlan show profiles |
    Select-String "All User Profile" |
    ForEach-Object { ($_ -split ":",2)[1].Trim() }

$results = @()
foreach ($p in $profileNames) {
    $details = netsh wlan show profile name=$p key=clear 2>$null
    $keyLine  = $details | Select-String "Key Content"
    $pw = if ($keyLine) { ($keyLine.ToString() -split ":\s*",2)[1].Trim() } else { "(open network)" }
    $results += [pscustomobject]@{ ssid = $p; password = $pw }
    Write-Host ("  OK  " + $p.PadRight(36) + " " + $pw)
}

Write-Host ""
Write-Host ("Found " + $results.Count + " saved networks.") -ForegroundColor Green

# Build JSON rows for embedding
$jsonRows = ($results | ForEach-Object {
    $s = $_.ssid     -replace '"','\"'
    $w = $_.password -replace '"','\"'
    '{"ssid":"' + $s + '","password":"' + $w + '"}'
}) -join ","

$genTime = Get-Date -Format "yyyy-MM-dd HH:mm"
$count   = $results.Count

# Build self-contained HTML (no fetch needed - data is embedded)
$html = @"
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WiFi Password Viewer</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;padding:30px 20px;}
h1{font-size:1.6rem;font-weight:700;margin-bottom:6px;background:linear-gradient(90deg,#818cf8,#38bdf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.sub{color:#64748b;font-size:.85rem;margin-bottom:20px;}
.badge{display:inline-block;background:#164e63;color:#38bdf8;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:600;margin-left:8px;vertical-align:middle;}
#search{width:100%;max-width:420px;padding:10px 16px;background:#1e293b;border:1px solid #334155;border-radius:10px;color:#e2e8f0;font-size:.9rem;outline:none;font-family:'Inter',sans-serif;margin-bottom:12px;display:block;}
#search:focus{border-color:#818cf8;}
.count{color:#64748b;font-size:.82rem;margin-bottom:10px;}
table{width:100%;border-collapse:collapse;background:#1e293b;border-radius:12px;overflow:hidden;}
thead{background:#1a2744;}
th{padding:12px 16px;text-align:left;font-size:.78rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em;}
td{padding:13px 16px;border-top:1px solid #0f172a;font-size:.88rem;}
tr:hover td{background:#243048;}
.pw{font-family:'Courier New',monospace;color:#4ade80;font-size:.85rem;}
.open{color:#64748b;font-style:italic;}
button{background:#4f46e5;color:#fff;border:none;padding:6px 14px;border-radius:8px;font-size:.8rem;cursor:pointer;font-family:'Inter',sans-serif;transition:.15s;}
button:hover{background:#6366f1;}
button.copied{background:#22c55e;}
</style>
</head>
<body>
<h1>WiFi Password Viewer <span class="badge">$count networks</span></h1>
<p class="sub">Generated: $genTime</p>
<input id="search" type="text" placeholder="Search network name..." oninput="filter()">
<div class="count" id="ct">$count saved networks</div>
<table>
<thead><tr><th>#</th><th>Network (SSID)</th><th>Password</th><th>Copy</th></tr></thead>
<tbody id="tb"></tbody>
</table>
<script>
var data = [$jsonRows];
var all = data;
function esc(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function render(d){
  document.getElementById('ct').textContent = d.length + ' network' + (d.length!==1?'s':'');
  document.getElementById('tb').innerHTML = d.map(function(r,i){
    var isPw = r.password !== '(open network)';
    return '<tr><td style="color:#475569;font-size:.8rem">'+(i+1)+'</td><td><strong>'+esc(r.ssid)+'</strong></td>'+
      '<td class="'+(isPw?'pw':'open')+'">'+esc(r.password)+'</td>'+
      '<td><button onclick="copy(this,\''+r.password.replace(/\\/g,'\\\\').replace(/'/g,"\\'")+'\'  )">Copy</button></td></tr>';
  }).join('');
}
function filter(){
  var q=document.getElementById('search').value.toLowerCase();
  render(data.filter(function(r){return r.ssid.toLowerCase().indexOf(q)>=0;}));
}
function copy(btn,pw){
  navigator.clipboard.writeText(pw).then(function(){
    btn.textContent='Copied!';btn.classList.add('copied');
    setTimeout(function(){btn.textContent='Copy';btn.classList.remove('copied');},1800);
  });
}
render(data);
</script>
</body>
</html>
"@

$outPath = Join-Path $scriptDir "wifi_viewer.html"
[System.IO.File]::WriteAllText($outPath, $html, [System.Text.Encoding]::UTF8)

Write-Host ("Viewer saved to: " + $outPath) -ForegroundColor Cyan
Write-Host "Opening in browser..." -ForegroundColor Gray
Start-Process $outPath

Write-Host ""
Write-Host "Done. Press Enter to close."
Read-Host
