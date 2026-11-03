# Eye-Bank development sources

Custom WordPress work for The Eye-Bank of New York.

## Included

- `wp-content/themes/eyedonation`: standalone block theme, editable layouts and patterns.
- `wp-content/plugins/eyedonation-search`: permanent enhanced search plugin.
- `wp-content/plugins/eyedonation-migrations`: explicit, versioned deployment migrations.
- Other locally authored site plugins listed below, including both single-file and directory plugins.
- `Spec`: supplied design specification, mockups, branding and original homepage photographs.
- `deliverables`: deployment/editing guides, migration inventory, compatibility and test documentation, and isolated rehearsal tooling.

WordPress core, the EyeBank/Genesis and American Friends themes, third-party plugins, uploads, credentials, database dumps, caches/logs and generated ZIPs/screenshots are excluded. An allowlist prevents newly installed plugins or local files from being accidentally committed.

## Set up another development environment

1. Install WordPress separately and create a local database and untracked `wp-config.php` with that environment's credentials and unique salts.
2. Clone this repository into a separate directory and copy its custom theme/plugins into the WordPress installation, or clone into an otherwise empty WordPress project directory before installing core. Do not replace an existing `wp-content` wholesale.
3. Restore the database and upload library from a separate private site backup when recreating an existing site. Git contains source/design assets, not the site's users, settings, submissions or editorial database. Preserve required third-party plugins separately.
4. For a fresh homepage setup, import the supplied `Spec/Homepage` images with the upload-relative paths expected by the theme's media manifests. Select the static homepage in Settings → Reading and preview/apply the newest full-homepage installer in Tools → Site Migrations. Existing sites use scoped follow-up migrations to preserve editorial changes.
5. Follow [HANDOFF](deliverables/HANDOFF.md), [DEPLOYMENT](deliverables/DEPLOYMENT.md) and [MIGRATIONS](deliverables/MIGRATIONS.md) for current behavior and checks. Migration activation alone never changes homepage content.

Requires WordPress 6.6+ and PHP 8.1+; recorded local testing uses WordPress 7.1.3 and PHP 8.4.17. This repository does not authorize production deployment.

## Daily Git workflow

```sh
git status
git diff
git add wp-content/themes/eyedonation wp-content/plugins Spec deliverables README.md .gitignore .gitattributes
git diff --cached --stat
git commit -m "Describe the change"
git push origin main
```

Review staged changes before committing. Store database/upload backups privately outside this repository. Generated installable ZIPs are local build artifacts and can be recreated from the package directories.

## Other custom plugins

The following local plugins are backed up as source, with their existing names and behavior preserved:

- `custom-user-info-url.php`
- `custom-disable-layout-metaboxes`
- `custom-disable-menu-links-for-creators`
- `custom-donor-family-survey`
- `custom-es-donor-family-survey`
- `custom-family-survey-colums`
- `custom-social-icons.php`
- `custom-site-tracking` — theme-independent GTM, PostHog and Facebook domain verification; see its README for setup.
- `custom-site-repairs` — separate one-button repair of header portals and Downloads/tissue-request pages.
- `custom-portal-navigation-fix` — targeted correction placing the portal links between Give Now and More in the active blue-bar menu.
- `custom-homepage-restore` — independent one-button restore of the current editable homepage and its display templates.
- `custom-toolset-mods` — general Toolset utility plugin; currently sorts the three selected user Views by last name.
- `custom-forgot-password-boards` — one-button migration of the legacy boards password page and editable template.
- `custom-mobile-donor-panel` — CSS-only mobile donor-card scale and hero-bottom placement adjustment.
- `custom-resource-photo-framing` — responsive framing of the homepage recipients/families Resources photograph.
- `custom-resource-card-images` — import supplied desktop/mobile photos and replace the prior framing adjustment.
- `custom-mobile-resource-card-height` — match the mobile recipients/families card to the neighboring Resources cards.
- `custom-top-links-english-site`
- `custom-translation-navigation-links`

`custom-post-type-permalinks` is excluded: its local metadata identifies Toro_Unit’s third-party Custom Post Type Permalinks plugin, not locally authored code. Other installed plugins inspected identify third-party authors; no additional custom plugins were identified. Adding a new custom plugin requires an explicit allowlist entry in `.gitignore`.

## Combined accessibility and translation widget

`wp-content/plugins/custom-accessibility-translation/` contains **Custom Accessibility Translation CWS (ATP)**. It replaces the two separate visitor widgets, copies legacy translation mappings on activation, and provides an independent settings/mapping screen. See its README for deployment and verification. Old plugin sources and records are preserved.

- `custom-mobile-resource-image` — replace the mobile Resources photo with v3, matching card heights and cropping only from the bottom.

- `custom-landing-no-header-footer` — install the content-only landing page template and assign Fall Benefit while preserving Thrive content.

- `custom-homepage-history` — replace the history desktop photo, narrow its panel and move About Us below.

- `custom-thrive-compatibility-fix` — REQUIRED while Thrive landing pages remain; corrects theme/domain stylesheet misclassification without editing Thrive.
