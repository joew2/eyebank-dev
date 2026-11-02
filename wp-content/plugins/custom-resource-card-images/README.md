# Custom Resource Card Images CWS

Version 1.0.0 · Creative Way Solutions · GPL-3.0-or-later · WordPress 6.6+ / PHP 8.1+.

## Install and process

Install this as a new plugin and activate **Custom Resource Card Images CWS**. Open **Tools → Resource Card Images CWS**, check the backup agreement and click **Process all updates**. The four automatic steps import desktop and mobile images, remove the previous framing CSS/install the new presentation, and update only the recipients/families Resources card on the selected static homepage.

Both supplied JPEGs are included in `assets/`; there is no separate upload step or remote image download. The WordPress media APIs import them into the destination's Media Library/uploads and generate normal attachment metadata. Source SHA-256, JPEG MIME and 670 × 840 dimensions are checked. Imports are marked by hash and reused on retry, without overwriting old images. The previous **Custom Resource Photo Framing CWS** plugin can be deactivated; do not run its older framing workflow again.

After completion, clear site/CDN caches. This new plugin can then be deactivated: image URLs point to WordPress uploads, the page contains its own responsive markup, and presentation is saved in Additional CSS. No theme ZIP update is needed.

## Photo behavior

Desktop uses the supplied `transplant_recipients_donor_families_desktop-v2.jpg`; mobile (781px and below, matching the stacked Resources layout) uses `transplant_recipients_donor_families_-mobile-v2.jpg`. Bundled filenames are normalized, but the original JPEG bytes are unchanged; `assets/images.json` records their hashes.

Removes the marked **CWS RESOURCE PHOTO FRAMING** CSS block, including the earlier 5% enlargement and horizontal/vertical offsets. The replacement uses the supplied 670:840 proportions and does not add zoom, offsets or hover cropping. The mobile card therefore follows the provided portrait image's height instead of cropping it into the older short rectangle. The existing label/link and other homepage sections are retained.

The target cover is replaced with a native Group containing a responsive Custom HTML `picture` block plus its existing native heading/link. The browser selects one image source for its viewport; a separate hidden second image is not loaded. The heading/link remain normally editable; the picture's two Media Library URLs are visible in its HTML block if later source replacement is required.

## Scope and recovery

The plugin uses the current static homepage from Settings → Reading and requires editing/upload/unfiltered-HTML permissions. It expects exactly one recipients/families resource card and stops rather than modifying an ambiguous target. Other page sections, header, navigation, footer, page template and unrelated metadata/styles remain intact.

Independent `cwsrcimg_job_v1`, `cwsrcimg_history` and `cwsrcimg_assets_v1` options store progress, before-images and imported IDs. Imported attachments have `_cwsrcimg_sha256` / `_cwsrcimg_variant` markers. Existing page content and Additional CSS are saved before writes, with WordPress revisions. The original photograph remains in place for recovery. Failed dependent steps wait; the same button retries unfinished work and preserves completed edits. This is not an automatic all-or-nothing rollback; keep the acknowledged recoverable database/files backup.

Requests require an authenticated administrator, editing capabilities, nonce and agreement. Leases coordinate media/page/CSS writes with the other migration tools. Activation alone performs no imports or page changes.

## Verification

Tests cover source hashes/dimensions, reused attachments, responsive markup, removal of previous framing CSS, unchanged other homepage sections/pages/templates/styles, original before-images, idempotent replacement and completed-edit preservation. The migration was applied locally using real Media Library imports. The actual local homepage was checked at desktop and phone widths: correct uploaded source, full supplied proportions, no transform and no old size-container rules. Browser fixtures also checked widths 320–1440px and hover behavior. No online migration was run while packaging.
