#!/bin/bash
# cleanup.sh — Remove all CamPhish logs, captures, and temp files

echo ""
echo "╔══════════════════════════════════════╗"
echo "║      CamPhish — Cleanup Script       ║"
echo "╚══════════════════════════════════════╝"
echo ""

read -p "⚠  This will DELETE all logs, photos, audio, and sessions. Continue? [y/N] " confirm
if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
  echo "Aborted."
  exit 0
fi

DIRS=(
  "logs/snapshots"
  "logs/audio"
  "logs/location"
  "logs/device"
  "logs/ip"
  "logs/screens"
  "logs/sessions"
  "saved_locations"
)

FILES=(
  "logs/Log.log"
  "logs/AudioLog.log"
  "logs/link_meta.json"
  "logs/AUTOKILL.signal"
  ".cloudflared.log"
  "qrcode.png"
)

for dir in "${DIRS[@]}"; do
  if [ -d "$dir" ]; then
    rm -rf "$dir"
    echo "  ✓ Removed $dir"
  fi
done

for file in "${FILES[@]}"; do
  if [ -f "$file" ]; then
    rm -f "$file"
    echo "  ✓ Removed $file"
  fi
done

# Re-create empty log structure
mkdir -p logs/snapshots logs/audio logs/location logs/device logs/ip logs/screens logs/sessions

echo ""
echo "✅ Cleanup complete. All captures removed."
echo ""