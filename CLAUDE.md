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

## Always update `README.md` with every change

Every change to this repository must include a corresponding
`README.md` update in the same pull request so the docs never drift
from the code. Specifically:

- Adding or renaming a file &rarr; update the **Project layout** table.
- Changing version, filenames, or launcher behavior &rarr; update the
  **Downloads** table, the bundle list under **Build distributions
  locally**, and any in-text version references.
- Adding or changing a workflow &rarr; update **Publish a new release**
  or the relevant section.
- Changing the public JS API of `gedcom2wiki.js` &rarr; update
  **Programmatic use**.
- Behavior, feature, or UX change in the app &rarr; update **Features**
  and/or **Usage**.

If a change truly has no user-visible effect (e.g. a typo fix in a
comment), note that explicitly in the PR description rather than
silently skipping the README update.

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
