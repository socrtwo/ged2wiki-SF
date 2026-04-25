# gedcom2wiki

<!--PAGES_LINK_BANNER-->
> 🌐 **Live page:** [https://socrtwo.github.io/ged2wiki-SF/](https://socrtwo.github.io/ged2wiki-SF/)  
> 📦 **Releases:** [github.com/socrtwo/ged2wiki-SF/releases](https://github.com/socrtwo/ged2wiki-SF/releases)
<!--/PAGES_LINK_BANNER-->

[![Release](https://img.shields.io/github/v/release/socrtwo/ged2wiki-SF)](https://github.com/socrtwo/ged2wiki-SF/releases/latest)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Build](https://github.com/socrtwo/ged2wiki-SF/actions/workflows/build.yml/badge.svg)](https://github.com/socrtwo/ged2wiki-SF/actions/workflows/build.yml)

Convert standard GEDCOM genealogy files into Wikipedia/Wikimedia
[`{{familytree}}`](https://en.wikipedia.org/wiki/Template:Family_tree)
template markup &mdash; entirely in your browser. No server, no upload, no
tracking.

**Language:** JavaScript (browser) &nbsp;|&nbsp; **License:** MIT

## Features

- Reads standard GEDCOM 5.5 genealogy files
- Outputs Wikipedia `{{familytree}}` template markup
- Handles multi-generation structures, marriages, and siblings
- 100% client-side &mdash; works offline after the first load
- Installable as a Progressive Web App (PWA) on Android, iOS, and desktop
- Distributed as unsigned downloads for Windows, macOS, Linux, Android,
  iOS, and Web

## Downloads

Grab the latest unsigned build for your platform from
[**Releases**](https://github.com/socrtwo/ged2wiki-SF/releases/latest), or use
the direct links below:

| Platform | Download | Launcher |
|----------|----------|----------|
| Web      | [`gedcom2wiki-web-v2.0.zip`](https://github.com/socrtwo/ged2wiki-SF/releases/download/v2.0/gedcom2wiki-web-v2.0.zip)           | open `index.html`           |
| Windows  | [`gedcom2wiki-windows-v2.0.zip`](https://github.com/socrtwo/ged2wiki-SF/releases/download/v2.0/gedcom2wiki-windows-v2.0.zip)   | `launch.bat`                |
| macOS    | [`gedcom2wiki-macos-v2.0.zip`](https://github.com/socrtwo/ged2wiki-SF/releases/download/v2.0/gedcom2wiki-macos-v2.0.zip)       | `launch.command`            |
| Linux    | [`gedcom2wiki-linux-v2.0.tar.gz`](https://github.com/socrtwo/ged2wiki-SF/releases/download/v2.0/gedcom2wiki-linux-v2.0.tar.gz) | `launch.sh` + `.desktop`    |
| Android  | [`gedcom2wiki-android-v2.0.zip`](https://github.com/socrtwo/ged2wiki-SF/releases/download/v2.0/gedcom2wiki-android-v2.0.zip)   | PWA (Add to Home Screen)    |
| iOS      | [`gedcom2wiki-ios-v2.0.zip`](https://github.com/socrtwo/ged2wiki-SF/releases/download/v2.0/gedcom2wiki-ios-v2.0.zip)           | PWA (Add to Home Screen)    |

Checksums: [`SHA256SUMS.txt`](https://github.com/socrtwo/ged2wiki-SF/releases/download/v2.0/SHA256SUMS.txt)

## Quick start

### Use it online

Open [`index.html`](index.html) in any modern browser and pick a `.ged`
file &mdash; that's it.

### Install as a desktop app (Windows / macOS / Linux)

1. Download the archive for your platform from the
   [latest release](https://github.com/socrtwo/ged2wiki-SF/releases/latest).
2. Extract it.
3. Double-click the launcher:
   - **Windows:** `launch.bat`
   - **macOS:** `launch.command` &mdash; first run only: right-click
     &rarr; Open &rarr; Open (Gatekeeper blocks unsigned apps by default).
   - **Linux:** `./launch.sh` (or open `index.html` directly).

The launcher opens `index.html` in your default browser.

### Install on Android / iOS

1. Download the Android or iOS archive and extract it.
2. Host the folder on any HTTPS static host &mdash;
   [GitHub Pages](https://pages.github.com/), Netlify, Cloudflare Pages,
   Vercel, S3, or any web server. Locally: `python3 -m http.server 8080`
   works for testing.
3. Open the URL on your phone.
4. Use the browser's **"Add to Home Screen"** option to install as a PWA.

## Usage

1. Click **Load sample** or select a `.ged` file.
2. The converted wiki markup appears in the output panel.
3. Click **Copy output** or **Download .txt** and paste the result into
   any Wikimedia-compatible wiki article.

## Programmatic use

The converter is a UMD module &mdash; it works in both browsers and Node.

```js
// Node
const fs = require('fs');
const gedcom2wiki = require('./gedcom2wiki.js');
const text = fs.readFileSync('family.ged', 'utf8');
const { output, warning, individualCount, familyCount } = gedcom2wiki(text);
console.log(output);
```

```html
<!-- Browser -->
<script src="gedcom2wiki.js"></script>
<script>
  const { output } = gedcom2wiki(gedcomText);
</script>
```

## Project layout

| File                      | Purpose                                  |
| ------------------------- | ---------------------------------------- |
| `index.html`              | Main app UI                              |
| `gedcom2wiki.js`          | GEDCOM &rarr; wiki conversion engine     |
| `manifest.webmanifest`    | PWA manifest                             |
| `sw.js`                   | Service worker for offline use           |
| `icon.svg` / `icon-*.png` | App icons                                |
| `sample.ged`              | Example GEDCOM input                     |
| `build-releases.sh`       | Build all six platform distributions     |
| `releases/`               | Prebuilt unsigned distribution archives  |

## Build distributions locally

```bash
./build-releases.sh
```

Produces all six platform bundles in `releases/`:

- `gedcom2wiki-web-v2.0.zip`
- `gedcom2wiki-windows-v2.0.zip`
- `gedcom2wiki-macos-v2.0.zip`
- `gedcom2wiki-linux-v2.0.tar.gz`
- `gedcom2wiki-android-v2.0.zip`
- `gedcom2wiki-ios-v2.0.zip`

All archives are **unsigned**. On macOS and Windows you may need to
allow the launcher explicitly on first run.

## Publish a new release

1. Bump the version in `build-releases.sh` (or pass `VERSION=x.y` at
   runtime) and commit.
2. Go to **Actions &rarr; Release gedcom2wiki &rarr; Run workflow**.
3. Enter the tag (e.g. `v2.1`) and click **Run workflow**.

The workflow smoke-tests the converter, builds all six archives,
computes SHA-256 checksums, generates release notes, and publishes a
GitHub Release with the archives attached.

## History

- Originally hosted on [SourceForge](https://sourceforge.net/projects/ged2wiki/)
  as a PHP web service that required a server.
- **v2.0** (2026): rewritten in client-side JavaScript so the same code
  runs as a static web app, an offline-capable PWA, and as unsigned
  launchers for every major platform. Output is byte-for-byte identical
  to the original PHP on the bundled `sample.ged`.

## License

MIT License &mdash; see [LICENSE](LICENSE).