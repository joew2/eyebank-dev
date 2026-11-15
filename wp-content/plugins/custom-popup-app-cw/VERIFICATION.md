# Implementation verification

Verified in the local MAMP Pro site on October 8, 2026: WordPress 7.1.3, PHP 8.4.17, active American Friends block theme. No production deployment.

## Delivered files

- `custom-popup-app-cw.php`: plugin registration and hooks.
- `includes/storage.php`: private post type, capabilities, defaults and validation.
- `includes/admin.php`: menu/list integration, custom editing form, save, page-search and preview endpoints.
- `includes/frontend.php`: matching, ordering and conditional asset/configuration output.
- `assets/admin.css`, `assets/admin.js`: native Media Library and page selection interface.
- `assets/frontend.css`, `assets/frontend.js`: shared responsive public/preview dialog.
- `tests/integration.php`: repeatable focused WP-CLI suite.
- `README.md`, this verification report.

The repository `.gitignore` also allows this plugin directory to be tracked. Existing changes in other plugins were preserved; WordPress core, the theme and unrelated plugin code were not changed.

## Passed checks

**38 PHP integration assertions:** private CPT/public REST configuration, administrator-only primitive capabilities, mapped per-record permissions, Team-menu ordering and absent-Team fallback, defaults, incomplete drafts, invalid publication, URL/enumeration/range/page-ID validation, deduplication, retained invalid values, exact page targeting, child exclusion, static and latest-posts front-page query semantics, separate posts-index exclusion, 404/feed/preview suppression, password/private page exclusion, publication guard and scheduling rejection.

**68 local Chrome browser/lifecycle assertions:**

- Higher priority, modification time and ID ordering; client-side eligibility skips suppressed candidates; dismissal never opens the next candidate.
- Portrait sizing at desktop (1600 × 1200), short laptop (1366 × 600), narrow phone (390 × 844), and mobile landscape (844 × 390) viewport sizes. Tall, landscape and small images remain bounded and small images are not upscaled. Screenshots were inspected.
- Close-button focus, keyboard focus containment with linked and unlinked graphics, Escape, actual backdrop dismissal, protected new-tab links, same-tab navigation, and image clicks not acting as backdrop clicks.
- Delay, tab-session suppression, 24-hour suppression and expiration, blocked storage fallback, and no image request for a frequency-suppressed candidate.
- Broken-image fallback, all images failing without an empty overlay or locked page, and JavaScript-disabled usability.
- Reduced motion, resizing an open dialog, background wheel-scroll locking, and restoration of previous scroll, focus and inline styles. Checked with the active sticky header and its scrollbar gutter.
- Real static-homepage targeting and exclusion of other pages; exact selected-page targeting and child exclusion; assets absent on nonmatching pages.
- Actual menu position below Team, Media Library opening and image selection/alt-text population, creation/publication, unpublishing, native Trash and Restore.
- Unsaved preview content, no implicit save, unchanged visitor storage, and close controls clear of the admin bar.
- Page search limited to 20 results, Load More, duplicate-title context, keyboard selection, duplicate exclusion, removal, empty results and server-error feedback.
- Invalid nonce rejection, invalid save rejected with the entered value preserved, anonymous search denial, and editor-role denial for list access, page search, preview and saves.
- Deactivation removes rendered configuration/assets while preserving saved records and attachments; reactivation succeeds.

PHP syntax checks, JavaScript syntax checks, and the PHP **WordPress-Core** coding-standard check passed. The final PHP integration run passed after formatting. Browser checks reported no plugin JavaScript exceptions.

## Coverage limits

- Browser checks used local desktop Chrome with resized viewports. Physical iOS/Android devices, touch gestures, screen-reader software, Safari, Firefox and Edge were not directly tested.
- Latest-posts homepage and separate posts-index semantics were exercised with controlled WordPress query/Reading-option filters, without changing the site's saved Reading configuration. The actual current static homepage was also checked in the browser.
- The missing-Team fallback was integration-tested without disabling the site's Team plugin. AJAX stale-response protection was reviewed in code; an artificial out-of-order-response race was not separately automated.
- Real third-party page caches/CDNs, media offloading, multisite installations, older supported PHP/WordPress versions, and production hosting were not exercised. Cache purging may be required, as documented in the README.
- Uninstall was not executed; there is intentionally no uninstall deletion hook. Deactivation preservation was tested directly.

## Final local state

The plugin is active. All temporary QA posts, pages, images, the editor-role test account, and the dedicated admin test session were removed. There are no saved or published pop-ups. Administrators can create the first real graphic through **Custom Popup App CW → Add New**.

## Version 1.0.1 design update

Changed the desktop sizing to a 750-pixel minimum where space permits, allowing internal vertical scrolling for tall images. Small screens continue fitting the complete graphic. Replaced the separate square close-button row with a circular top-right corner control, hover enlargement, and native “Close” tooltip. This supersedes the original desktop fit-without-scrolling/no-upscaling behavior documented in the initial checks above.

Verified against the existing published local pop-up in Chrome at 1280 × 720 and 390 × 844: desktop image width is 750 pixels; vertical scrolling exposes the remaining graphic; there is no horizontal cropping; the close control is circular, stays in view when enlarged, and has the tooltip; resizing to phone dimensions fits the full graphic; Escape dismisses it. No saved pop-up settings or content were changed.

## Version 1.0.2 sizing and scrolling update

Added validated per-pop-up width radios for 600, 650, 700 and 800 pixels, with 650 as the backward-compatible default. Both image orientations use the selected width, limited by available screen width. The full-window dialog now scrolls instead of the image frame. The visible close circle is 22 pixels (50% smaller), retaining a 44-pixel hit area, hover enlargement and tooltip.

Verified the existing published pop-up defaults to 650px; all four widths render correctly through unsaved preview; the image frame has no internal scrolling; scrolling outside the graphic moves the full-window overlay; the background stays inactive; mobile width fits; Escape and backdrop dismissal work. Server validation and rendered admin radio choices were checked, with width cases added to the integration suite. No saved user content was changed.

## Version 1.0.3 naming update

Renamed the plugin to **Custom Popup App CW**, its directory/main file to `custom-popup-app-cw/custom-popup-app-cw.php`, and its text domain to `custom-popup-app-cw`. The admin menu now reads **Popup**, with consistent Popup/Popups editor labels and the native `dashicons-format-chat` speech-bubble icon. Updated WordPress’s active-plugin reference, preserving its position and the existing saved popup. The post type, metadata keys, namespace and settings remain unchanged. Verified the renamed plugin is active and the saved record is retained.
