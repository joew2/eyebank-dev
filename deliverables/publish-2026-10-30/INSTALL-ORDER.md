# EyeDonation installation packages — October 30, 2026

These ZIPs were freshly built from the current local source, including the Downloads role restriction and standalone tracking plugin. Use this dated set together rather than mixing it with older ZIPs in deliverables. This is an installation bundle for staging and rehearsal, not confirmation that the live site is ready to launch.

## Before uploading

1. Make a recoverable backup of the live database and files, including uploads.
2. Make a protected staging copy from a recent live backup. Perform this checklist there first. Use PHP 8.1+ and WordPress 6.6+ for the main theme/plugins.
3. Set staging's `WP_ENVIRONMENT_TYPE` to `staging` so the new tracking plugin does not send staging analytics. WordPress defaults to production if this is unset. Prevent staging notifications/payment activity through the site's existing staging controls.
4. Keep the existing live theme and functional plugins available. These ZIPs do not contain WordPress, a database export, existing users/submissions, or the complete uploads library.

## Upload in this order

| Step | ZIP | WordPress package name | Upload screen | Purpose and action |
|---|---|---|---|---|
| 1 | [01-eyedonation-2026-10-30.zip](01-eyedonation-2026-10-30.zip) | EyeDonation | Appearance → Themes → Add New → Upload Theme | New design, page templates, editable starter patterns, bundled design media, and member-menu role rule. Upload/update first. Leave the current theme active until the other packages are installed. |
| 2 | [02-eyedonation-search-2026-10-30.zip](02-eyedonation-search-2026-10-30.zip) | EyeDonation Search | Plugins → Add New → Upload Plugin | Enhanced search and its settings/index. Activate and keep active. Review search settings and rebuild public search text after page installation; indexing uses WordPress cron. |
| 3 | [03-custom-accessibility-translation-2026-10-30.zip](03-custom-accessibility-translation-2026-10-30.zip) | Custom Accessibility Translation CWS | Plugins → Add New → Upload Plugin | Combined accessibility toolbar and English/Spanish page switcher. On first activation imports compatible mappings from the old translation plugin. After confirming the mappings, deactivate the old Custom Translation Navigation Links and Pojo accessibility widget to avoid duplicate controls. Configure this plugin under ATP. |
| 4 | [04-custom-newsletters-2026-10-30.zip](04-custom-newsletters-2026-10-30.zip) | Custom Newsletters CWS | Plugins → Add New → Upload Plugin | Newsletter archive, editable issue records and About Donation popup. Activate and keep active. First activation seeds 15 starter issues; verify PDFs and later editorial changes under Newsletters. |
| 5 | [05-custom-site-tracking-2026-10-30.zip](05-custom-site-tracking-2026-10-30.zip) | Custom Site Tracking CWS | Plugins → Add New → Upload Plugin | GTM, PostHog and Facebook domain verification independent of the theme. Activate and review Settings → Site Tracking CWS. Suppresses the known old Genesis tracking snippets while active. Tracking runs only in production; verification is independent of the tracking checkbox. |
| 6 | [06-eyedonation-migrations-2026-10-30.zip](06-eyedonation-migrations-2026-10-30.zip) | EyeDonation Site Migrations | Plugins → Add New → Upload Plugin | Preview/install tools that write converted pages, menus and selected settings into the destination database. Activate, then activate the EyeDonation theme before previewing content changes. This plugin does not install page content on activation. |

If a package already exists, use WordPress's **Replace current with uploaded** update action. Match the directory/package name, not just the ZIP filename. The numbered archive names preserve the original internal directories and plugin entry points. Existing internal version numbers have been retained; this dated set identifies this source snapshot. Do not delete existing plugins as an update method: deletion can trigger their uninstall behavior.

## After all six ZIPs are installed

1. Activate **EyeDonation** under Appearance → Themes. Confirm Settings → Reading selects the intended existing static homepage.
2. Keep Gravity Forms and applicable add-ons, Toolset Views/Forms/Types/Access, the existing file manager, and other required runtime plugins with their data/settings.
3. Open **Tools → Site Migrations**. The current ZIP provides one agreement checkbox and **Process all updates**. Check the agreement and click the button. No manual preview actions or historical numbered steps are required. Keep the tab open while the current updates run in automatic batches.
4. Read the progress summary. If a form/plugin prerequisite is missing, correct the named issue and click the same button again. It retries unfinished tasks and retains completed tasks. See [PROCESS-ALL-UPDATES.md](PROCESS-ALL-UPDATES.md).
5. Review pages, menus, responsive layouts, login/reset flows, forms, downloads, newsletter PDFs and search. Clear caches and verify tracking on production. Existing saved EyeDonation header/footer overrides are replaced by the current designs during the agreed bulk update; before-images are retained.
6. After staging rehearsal, deploy the same packages and process the current pending updates against the current live database during the agreed publishing window. Preserve newer live users/submissions; do not copy the development database over live.
7. Site Migrations can be deactivated afterward. Keep needed runtime plugins active. Deactivation or switching themes does not undo content changes; recover from before-images or the database backup if necessary.

## Media and data that are not in these plugin ZIPs

- The theme contains its bundled static design images. Existing post images, uploaded documents, file-manager contents, forms, Views and people records still belong to the site's existing data/uploads.
- The newsletter plugin contains starter records and upload-relative paths, not the approximately 65 MB of PDF files. Verify the destination has the paths listed in the plugin's `issues.json`; transfer missing PDFs using the same uploads paths. Later local newsletter edits need an appropriate content/data transfer if they are to replace the starter records.
- ATP imports the old plugin's mappings on the destination; it does not carry local-only mapping/settings changes in its ZIP. Review/re-enter those as needed.
- No third-party plugin is repackaged here. Retain/update those through their normal licensed sources.

## Package verification

All packaged PHP files passed syntax checks. ZIP CRCs, single top-level directories and exact file-content matches against the source were verified. Development tests, caches and nested ZIPs were excluded. `PACKAGE-CHECKSUMS.json` records each package's SHA-256, file count and size. Packaging checks are not a substitute for the fresh live-copy rehearsal described above.
