#!/usr/bin/env bash
# Menangkap screenshot yang butuh chrome browser (address bar & DevTools)
# menggunakan Xvfb + Chromium headed + xdotool + ImageMagick.
# Pemakaian: bash tests/e2e/capture-browser-chrome.sh addressbar|devtools
set -uo pipefail

MODE="${1:-addressbar}"
export DISPLAY=:99
OUT="/tmp/chrome-shots"
mkdir -p "$OUT"

cleanup() {
  pkill -f hold-session.mjs 2>/dev/null
  pkill -f "Xvfb :99" 2>/dev/null
}
trap cleanup EXIT

pkill -f "Xvfb :99" 2>/dev/null
sleep 0.5
Xvfb :99 -screen 0 1440x900x24 >/tmp/xvfb.log 2>&1 &
sleep 1.5

if [ "$MODE" = "devtools" ]; then
  AUTO_DEVTOOLS=1 HOLD_MS=120000 node tests/e2e/hold-session.mjs >/tmp/hold.log 2>&1 &
else
  HOLD_MS=60000 node tests/e2e/hold-session.mjs >/tmp/hold.log 2>&1 &
fi

for _ in $(seq 1 40); do
  grep -q READY /tmp/hold.log 2>/dev/null && break
  sleep 1
done
sleep 3

WID=$(xdotool search --name "Google Chrome for Testing" | tail -1)
echo "window: $WID"
xdotool windowactivate --sync "$WID" 2>/dev/null
sleep 2

if [ "$MODE" = "addressbar" ]; then
  # Ctrl+L menampilkan URL lengkap beserta skema https:// dan ikon koneksi aman.
  xdotool key ctrl+l
  sleep 1.5
  import -window root "$OUT/addressbar.png"
  echo "captured addressbar"
  exit 0
fi

# DevTools terbuka otomatis; buka Application panel lewat command palette.
xdotool key ctrl+shift+p
sleep 1.5
xdotool type --delay 60 "Show Application"
sleep 1.5
import -window root "$OUT/d1-palette.png"
xdotool key Return
sleep 3
import -window root "$OUT/d2-application.png"

# Pilih node Cookies (y~360) lalu perluas.
xdotool mousemove 955 360 click 1
sleep 1.5
import -window root "$OUT/d3-selected.png"
xdotool key Right
sleep 2
import -window root "$OUT/d4-expanded.png"

# Turun ke origin.
xdotool key Down
sleep 2
import -window root "$OUT/d5-origin.png"

# Turun ke baris cookie pertama.
xdotool key Down
sleep 2
import -window root "$OUT/d6-cookie-row.png"
echo "captured devtools"
