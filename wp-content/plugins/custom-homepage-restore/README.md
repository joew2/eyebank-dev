# Custom Homepage Restore CWS

Version 1.0.0 · Creative Way Solutions · GPL-3.0-or-later · WordPress 6.6+ / PHP 8.1+.

## Restore

Install as a new plugin and activate **Custom Homepage Restore CWS**. Open **Tools → Homepage Restore CWS**. The screen identifies the homepage selected in Settings → Reading. Check the backup/replacement agreement and click **Restore homepage**. Keep the tab open until completion, then clear the site/CDN cache and review the homepage. No manual previews or other migration plugins are needed. Activation and screen visits do not restore content.

The target must be an existing static WordPress page selected under Settings → Reading. The plugin preserves its ID, title, URL, publication status and unrelated metadata. It does not create/publish a replacement page or change the Reading selection. If the target is missing or changes mid-run, it reports the issue without touching a different page.

## Restored design and scope

The plugin includes a frozen snapshot of the latest supplied homepage as native WordPress blocks: current hero image, donor signup panel, resources, history, Eye Can See Again section, current desktop/mobile support artwork and dynamic latest-news section. URLs resolve against the destination's home/theme URLs. Theme image files are checked before content is written; the original homepage Media Library uploads are not required. The matching EyeDonation theme and its bundled assets/runtime blocks are still required. News posts and their featured images remain the destination site's live records.

The snapshot was generated from the same portable homepage and current support-image composition used by the latest full migration. Content is saved in the existing page's `post_content`, its editable `homepage` template is assigned, and its legacy Thrive override is disabled while retained builder data stays stored.

Both `front-page` and `homepage` saved template overrides are restored to the packaged Header → Post Content → Footer structure. This ensures the restored page's native content actually displays. Header/footer template parts and their Navigation records are not modified: the current portal placement/menu edits remain intact. No other page, plugin setting, role, form, submission, newsletter record or search configuration is migrated. No Additional CSS is changed.

## Independent and repeatable

This plugin uses `cwshr_job_v1` and `cwshr_history`, independently of all earlier migration histories. Each explicit click starting a new restore after the previous one completed gets a new run ID and new before-images. It can therefore restore the page again after a later accidental edit without reinstalling or clearing old migration history.

If a request fails or the browser closes, use the same button to resume the unfinished run with its existing checkpoints and original before-images. The job will not silently retarget itself if the selected homepage changes while interrupted. Restore the original Reading selection to resume that run.

Before-images include current homepage content/template/Thrive flags and the saved template override content. WordPress page revisions are also saved. These records supplement the recoverable database backup acknowledged by the administrator; the plugin does not create a full database backup or automatic rollback. Keep other migration processors inactive while restoring. The applied content/templates and history persist after deactivation; the plugin may be deactivated once the restore is verified.

Requests require administrator/editing capabilities, a nonce and the backup agreement. Separate request and record leases prevent overlapping updates and coordinate with the existing migration execution lease.

## Checks

Tests use an isolated in-memory WordPress fixture and the real core block/HTML parsers. They verify current bundled images and links, selected page identity, broken display-template recovery, preservation of header/footer/other pages/metadata, before-images, older migration-history independence, repeated fresh restores, interrupted resume, changed-target and missing-asset errors, and the single-button screen. Local browser tests use simulated AJAX to verify agreement, completion and repeated explicit restores at desktop/mobile widths. No online restores, notifications or form submissions are performed by these tests.
