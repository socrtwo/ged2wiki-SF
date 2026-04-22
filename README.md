# gedcom2wiki

Convert standard GEDCOM genealogy files into Wikipedia/Wikimedia
[`{{familytree}}`](https://en.wikipedia.org/wiki/Template:Family_tree) template
markup.

**Language:** JavaScript (browser) &nbsp;|&nbsp; **License:** MIT

The converter is a self-contained web app — everything runs locally in your
browser, no server is needed, and no data ever leaves your device.

## Features

- Reads standard GEDCOM 5.5 genealogy files
- Outputs Wikipedia `{{familytree}}` template markup
- Handles multi-generation structures, marriages, and siblings
- 100% client-side (HTML + JS) — works offline after first load
- Installable as a Progressive Web App (PWA) on Android, iOS, and desktop
- Ships as unsigned downloads for Windows, macOS, Linux, Android, iOS, and Web

## Quick start

### Use it online

Open `index.html` in any modern browser. That's it.

### Install as a desktop app

1. Download the Windows / macOS / Linux archive from the
   [Releases](https://github.com/socrtwo/ged2wiki-sf/releases) or the
   [`releases/`](releases/) folder.
2. Extract the archive.
3. Run the included launcher:
   - **Windows:** double-click `launch.bat`
   - **macOS:** double-click `launch.command` (right-click &rarr; Open the first
     time, since the app is unsigned)
   - **Linux:** run `./launch.sh` in a terminal

The launcher opens `index.html` in your default browser.

### Install on Android / iOS

1. Download the Android or iOS archive and extract it.
2. Host the extracted folder on any static web server (for example,
   `python3 -m http.server` from the folder, or upload to GitHub Pages /
   Netlify / Cloudflare Pages).
3. Open the hosted URL on your phone.
4. Use the browser's **"Add to Home screen"** option to install it as a PWA.

## Usage

1. Click **Load sample** or select a `.ged` file.
2. The converted wiki markup appears in the output panel.
3. Click **Copy output** or **Download .txt**, then paste into any
   Wikimedia-compatible wiki article.

## Programmatic use

```js
// Node or browser
const gedcom2wiki = require('./gedcom2wiki.js');
const text = fs.readFileSync('family.ged', 'utf8');
const { output, warning, individualCount, familyCount } = gedcom2wiki(text);
console.log(output);
```

## Files

| File                      | Purpose                                  |
| ------------------------- | ---------------------------------------- |
| `index.html`              | Main app UI                              |
| `gedcom2wiki.js`          | GEDCOM &rarr; wiki conversion engine     |
| `manifest.webmanifest`    | PWA manifest                             |
| `sw.js`                   | Service worker for offline use           |
| `icon.svg` / `icon-*.png` | App icons                                |
| `sample.ged`              | Example GEDCOM input                     |
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

All archives are **unsigned**. On macOS and Windows you may need to allow the
launcher explicitly on first run.

## History

- Originally hosted on [SourceForge](https://sourceforge.net/projects/ged2wiki/)
  as a PHP web service.
- Migrated to GitHub and rewritten in client-side JavaScript so it runs as a
  static web app, PWA, and offline desktop app.

## License

MIT License — see [LICENSE](LICENSE).
