# Custom Popup App CW

A lightweight, administrator-managed image pop-up plugin. Uses private WordPress posts and the native Media Library, without custom tables, remote dependencies, tracking, or a build step.

## Installation and everyday use

Copy `custom-popup-app-cw` into `wp-content/plugins/` and activate **Custom Popup App CW** in Plugins. Requires WordPress 6.6+ and PHP 7.4+. Open **Popup → Add New**, immediately below the site's **Team** menu. If Team is absent, the menu appears near the bottom.

1. Enter an internal name and choose/upload an image.
2. Review the accessible description. It initially uses the image's Media Library alt text when the description is empty. Describe the important information in the graphic and the destination/purpose of its link.
3. Optionally supply a complete HTTP/HTTPS link and select a new tab.
4. Choose Homepage Only or search for Selected Pages.
5. Preview unsaved changes, then choose Published and Save/Update Pop-up.

Preview does **not** save or publish. New entries start as Draft; incomplete drafts can be saved. Publishing requires a name, accessible description, usable raster image, valid settings, and public published targets when selecting pages. Invalid edits display an error and retain entered values; the previously saved configuration remains unchanged.

All Pop-ups provides native search, pagination, status filters, Edit and Trash. Quick Edit and bulk editing are intentionally unavailable. Change Published to Draft to unpublish. Only administrators with `manage_options` can manage records, preview, or search pages.

## Fields and defaults

| Field | Default / behavior |
| --- | --- |
| Name | Internal only, required to publish |
| Image | Attachment ID; required to publish |
| Accessible description | Image alt text initially; independently editable |
| Link | Empty: plain unlinked graphic; HTTP/HTTPS only |
| New tab | Off; uses `noopener noreferrer` and an accessible indication |
| Target | Homepage Only |
| Width | 650 pixels; choose 600, 650, 700 or 800 pixels for each pop-up |
| Delay | 1 second after DOM and image readiness; whole seconds 0–60 |
| Frequency | Once per tab session |
| Priority | 10; whole numbers 0–100 |
| Status | Draft |

Homepage Only follows **Settings → Reading**, including a static front page or the latest-posts front page. A separate posts index is excluded. Selected Pages matches only the exact selected IDs, not their children; it can include the static homepage. Selections are retained when switching back to Homepage Only but are ignored in that mode.

Page search searches titles, starts after two characters, and returns 20 results per batch with Load More. Labels include the title, ID, and page path. Selections can be removed using their buttons. Unavailable saved targets show warnings; private, password-protected, unpublished, deleted, and trashed pages never display a pop-up. Restore the target or remove it before publishing that configuration again.

Posts, other post types, archives, searches, feeds, embeds, errors, editor previews, and customizer previews are excluded. No public pop-up URLs, archives, navigation items, or REST records are registered.

## Frequency and overlapping pop-ups

Frequency is applied in the browser even when the HTML is cached, separately per site and pop-up ID:

- **Once per tab session:** `sessionStorage`; not a guarantee across tabs. Browsers can copy session storage when duplicating a tab.
- **Every eligible page load:** once on each matching load.
- **Once every 24 hours:** timestamp in `localStorage`, shared by tabs for that browser/origin; expires after 24 elapsed hours.

Clearing storage or using another browser can display the pop-up again. Blocked storage degrades to once per eligible page load. No cookies, personal identifiers, or cross-device tracking are used.

Candidates sort by priority descending, then modification time descending, then ID descending. Frequency-suppressed candidates are not downloaded. Missing or failed images are skipped before opening. After one graphic displays, dismissal never advances to another candidate on that page load. Impressions are recorded only on display. Preview bypasses targeting, delay, frequency, and storage.

## Images, layout, and accessibility

Use a readable graphic with generous internal text spacing and an accurate accessible description. Choose a normal WordPress-supported raster image from the Media Library; SVG support is not added. The original file and usable attachment dimension metadata must be available. Remote-only media offloading without a local attachment file is not supported by this version.

Each pop-up has width radio buttons for **600, 650, 700 or 800 pixels**, applying equally to portrait and landscape images. Existing pop-ups without a saved width default to **650 pixels**. Smaller screens reduce the image to the available width while preserving its aspect ratio. If the graphic is taller than the window, the full-window overlay scrolls at the browser edge; there is no separate scrollbar beside the image. The underlying website stays inactive and returns to its previous scroll position when the pop-up closes.

The top-right close control has a 22-pixel visible circle (with a 44-pixel invisible click target), hover enlargement, and a “Close” tooltip. Use images at least as wide as the chosen display width, preferably larger for high-density screens. Responsive sources use WordPress-generated image sizes where available.

The native modal dialog supports Escape, close-button and backdrop dismissal, keyboard focus containment, inert background content, and restoration of focus, scroll and changed inline styles. Reduced motion disables its short fade. Without JavaScript or native dialog support, it does not open and the page remains usable. Modern Chrome, Firefox, Safari and Edge support native dialog; real-device testing is recommended for any narrower browser support policy.

## Caching and maintenance

After publishing, unpublishing, changing targets, or replacing an image, **purge the site's page cache** if one is enabled. This plugin cannot invalidate unknown page caches or CDNs. Browser-side frequency checks still apply to cached configuration, but cached HTML can contain an old publication/targeting state.

Deactivation stops new rendering/enqueues and preserves records and media. Purge cached HTML after deactivation as well. Uninstall intentionally preserves records; there is no automatic data deletion.

To explicitly remove saved plugin data, activate the plugin, select all entries in All Pop-ups (repeat through pagination), move them to Trash, then permanently delete them from Trash. This removes the private posts and their post meta. Deactivate and delete the plugin afterward. **Media Library attachments are never deleted by this plugin**, since other content may use them. Temporary validation-error transients expire after ten minutes. Browser frequency keys are small site/ID-scoped entries and may be removed through browser site-data settings if desired.

## Developer notes and checks

- Namespace: `CW\CustomPopUpApp`; CPT: `cw_popup`; single registered meta object: `cw_popup_settings`.
- `includes/storage.php`: defaults, validation, private post type, publication guard.
- `includes/admin.php`: native list integration, form, nonce/capability checks and AJAX.
- `includes/frontend.php`: matching, ordering, responsive attachment configuration and conditional assets.
- `assets/frontend.*`: shared public/preview dialog; `assets/admin.*`: Media Library and page selection.
- The Team slug `cw-team` was verified against this installation's Custom Team App CW plugin.

Run the focused integration suite on a local installation with WP-CLI:

```sh
wp eval-file wp-content/plugins/custom-popup-app-cw/tests/integration.php
```

The suite creates temporary test posts and deletes them in `finally`. Use a development site. See `VERIFICATION.md` for the actual implementation checks and remaining coverage limits.
