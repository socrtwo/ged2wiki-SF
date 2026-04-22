#!/usr/bin/env bash
# Build unsigned release bundles for Windows, macOS, Linux, Android, iOS, Web.
# All targets wrap the same static PWA; desktop variants add a platform launcher.
set -euo pipefail

VERSION="${VERSION:-2.0}"
ROOT="$(cd "$(dirname "$0")" && pwd)"
REL="$ROOT/releases"
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

CORE_FILES=(
  index.html
  gedcom2wiki.js
  manifest.webmanifest
  sw.js
  icon.svg
  icon-192.png
  icon-512.png
  icon-180.png
  sample.ged
  LICENSE
)

mkdir -p "$REL"

stage_core() {
  local dest="$1"
  mkdir -p "$dest"
  for f in "${CORE_FILES[@]}"; do cp "$ROOT/$f" "$dest/"; done
}

echo "==> Building Web"
WEB="$STAGE/gedcom2wiki-web-v$VERSION"
stage_core "$WEB"
cat > "$WEB/README.txt" <<'EOF'
gedcom2wiki - Web distribution

Open index.html in any modern browser, or host this folder on any
static web server (GitHub Pages, Netlify, Cloudflare Pages, S3, etc.).

All processing runs locally in the browser. No server-side code.
EOF
(cd "$STAGE" && zip -qr "$REL/gedcom2wiki-web-v$VERSION.zip" "gedcom2wiki-web-v$VERSION")

echo "==> Building Windows"
WIN="$STAGE/gedcom2wiki-windows-v$VERSION"
stage_core "$WIN"
cat > "$WIN/launch.bat" <<'EOF'
@echo off
rem gedcom2wiki launcher for Windows
start "" "%~dp0index.html"
EOF
# Convert to CRLF line endings for Windows friendliness
sed -i 's/$/\r/' "$WIN/launch.bat"
cat > "$WIN/README.txt" <<'EOF'
gedcom2wiki - Windows distribution

Double-click launch.bat to open the app in your default browser.

This build is UNSIGNED. Windows SmartScreen may warn the first time you
run launch.bat; click "More info" -> "Run anyway" to proceed.
EOF
sed -i 's/$/\r/' "$WIN/README.txt"
(cd "$STAGE" && zip -qr "$REL/gedcom2wiki-windows-v$VERSION.zip" "gedcom2wiki-windows-v$VERSION")

echo "==> Building macOS"
MAC="$STAGE/gedcom2wiki-macos-v$VERSION"
stage_core "$MAC"
cat > "$MAC/launch.command" <<'EOF'
#!/usr/bin/env bash
# gedcom2wiki launcher for macOS
cd "$(dirname "$0")"
open "index.html"
EOF
chmod +x "$MAC/launch.command"
cat > "$MAC/README.txt" <<'EOF'
gedcom2wiki - macOS distribution

Double-click launch.command to open the app in your default browser.

This build is UNSIGNED. Gatekeeper will block launch.command the first
time: right-click (or Control-click) launch.command -> Open -> Open.
EOF
(cd "$STAGE" && zip -qr "$REL/gedcom2wiki-macos-v$VERSION.zip" "gedcom2wiki-macos-v$VERSION")

echo "==> Building Linux"
LIN="$STAGE/gedcom2wiki-linux-v$VERSION"
stage_core "$LIN"
cat > "$LIN/launch.sh" <<'EOF'
#!/usr/bin/env bash
# gedcom2wiki launcher for Linux
cd "$(dirname "$0")"
if   command -v xdg-open >/dev/null 2>&1; then xdg-open "index.html"
elif command -v gio      >/dev/null 2>&1; then gio open  "index.html"
elif command -v sensible-browser >/dev/null 2>&1; then sensible-browser "index.html"
else
  echo "No launcher found. Open index.html in your browser manually." >&2
  exit 1
fi
EOF
chmod +x "$LIN/launch.sh"
cat > "$LIN/gedcom2wiki.desktop" <<EOF
[Desktop Entry]
Type=Application
Name=gedcom2wiki
Comment=Convert GEDCOM genealogy files to wiki family tree markup
Exec=xdg-open %k/index.html
Icon=%k/icon.svg
Categories=Utility;Office;
Terminal=false
EOF
cat > "$LIN/README.txt" <<'EOF'
gedcom2wiki - Linux distribution

Run ./launch.sh to open the app in your default browser.

Alternatively, copy gedcom2wiki.desktop to ~/.local/share/applications
and edit the Exec/Icon paths to the absolute extracted location.
EOF
tar -C "$STAGE" -czf "$REL/gedcom2wiki-linux-v$VERSION.tar.gz" "gedcom2wiki-linux-v$VERSION"

echo "==> Building Android (PWA bundle)"
AND="$STAGE/gedcom2wiki-android-v$VERSION"
stage_core "$AND"
cat > "$AND/INSTALL-ANDROID.txt" <<'EOF'
gedcom2wiki - Android PWA bundle

This archive contains a Progressive Web App. No Play Store required,
no signing required, no APK.

To install:
  1. Upload this folder to any HTTPS static hosting:
     - GitHub Pages, Netlify, Cloudflare Pages, Vercel, or any web server.
     (A PWA requires HTTPS to be installable; localhost also works.)
  2. Open the hosted URL in Chrome on your Android phone.
  3. Tap the menu (3 dots) -> "Install app" (or "Add to Home screen").
  4. gedcom2wiki appears on your home screen as a standalone app.

To test locally on a desktop first:
  python3 -m http.server 8080
  then visit http://localhost:8080

Packaging as a signed APK is out of scope for this unsigned build.
Tools such as Bubblewrap (https://github.com/GoogleChromeLabs/bubblewrap)
can wrap this PWA into a Trusted Web Activity APK if desired.
EOF
(cd "$STAGE" && zip -qr "$REL/gedcom2wiki-android-v$VERSION.zip" "gedcom2wiki-android-v$VERSION")

echo "==> Building iOS (PWA bundle)"
IOS="$STAGE/gedcom2wiki-ios-v$VERSION"
stage_core "$IOS"
cat > "$IOS/INSTALL-IOS.txt" <<'EOF'
gedcom2wiki - iOS PWA bundle

This archive contains a Progressive Web App. No App Store required and
no Apple Developer signing required.

To install:
  1. Upload this folder to any HTTPS static hosting:
     - GitHub Pages, Netlify, Cloudflare Pages, Vercel, or any web server.
  2. Open the hosted URL in Safari on your iPhone or iPad.
  3. Tap the Share button -> "Add to Home Screen".
  4. gedcom2wiki appears on your home screen as a standalone app.

Native .ipa packaging requires an Apple Developer Program membership
and signing; this unsigned build ships as an installable PWA instead.
EOF
(cd "$STAGE" && zip -qr "$REL/gedcom2wiki-ios-v$VERSION.zip" "gedcom2wiki-ios-v$VERSION")

echo
echo "Release bundles created in $REL:"
ls -la "$REL"
