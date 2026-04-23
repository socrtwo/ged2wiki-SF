# Instructions for Claude

## Always merge changes to `main`

When you make any change to this repository:

1. Commit the change on the working branch (keep the assigned feature
   branch if one is in force, otherwise create a short descriptive
   branch).
2. Push the branch to GitHub.
3. Open a pull request targeting `main` using the GitHub MCP tools.
4. **Merge the pull request into `main`** before reporting the work
   as done. Do not leave changes unmerged on a feature branch unless
   the user explicitly asks you to stop before merging.

The merge can be a standard merge commit; use squash only if the user
asks for it.

## Project notes

- The converter is a client-side JavaScript app. The engine is
  `gedcom2wiki.js`; the UI is `index.html`. Both are shipped as-is in
  every distribution bundle.
- Distribution archives are built by `build-releases.sh`. Archive
  filenames include a `v` prefix (e.g. `gedcom2wiki-web-v2.0.zip`).
  When referencing them from workflows or docs, always include the
  `v`.
- New releases are published via the **Release gedcom2wiki** workflow
  (`.github/workflows/release.yml`), triggered manually from the
  Actions tab. The workflow handles tagging, building, checksums,
  release notes, and asset upload.
- `.github/workflows/build.yml` runs on every push to `main` and PR;
  it must continue to pass.
- If you update the version, bump the default `VERSION` in
  `build-releases.sh`, update `README.md` references, and bump the
  default tag in `release.yml`.
