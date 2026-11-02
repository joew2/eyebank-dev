# Custom Portal Navigation Fix CWS

Version 1.0.0 · Creative Way Solutions · GPL-3.0-or-later · WordPress 6.6+ / PHP 8.1+.

Install as a new plugin and activate it. Open **Tools → Portal Navigation Fix CWS**, check the backup agreement, and click **Process all updates**. Updating/activating does not run the correction. Keep the tab open for completion. No manual previews or theme update are required. Earlier migration/repair plugins are not required and should remain inactive while processing this correction.

## Correction

Removes Transplant Portal and Research Portal from the upper utility buttons beside EyeCanSeeAgain.org. Removes the earlier repair's `cwsr-header` layout marker, restoring the theme's normal upper-header layout while retaining the other repair styles used by the member pages. Leaves EyeCanSeeAgain.org and unrelated utility content intact.

Finds the `ed-primary-navigation` Navigation block in the current Header. If it references a saved `wp_navigation` record, updates that exact record; if it has inline menu items, updates the Header's inline menu. It does not guess by Main/ED Primary menu names or switch menus. Inactive menus remain unchanged.

Removes duplicate top-level portal items and inserts:

**Give Now → Transplant Portal → Research Portal → More**

Destinations are the current site's `/transplant/` and `/researchers/`. Other entries and submenus retain their order and content. Give Now and More must be present in that order; an unexpected structure is reported before records are changed. Repeated transformations do not add duplicates. A scoped Additional CSS rule adjusts desktop blue-bar spacing for the seven links while retaining existing font sizes and mobile menu behavior. Unrelated Additional CSS is preserved.

## Storage and recovery

The two-step job uses independent `cwspn_job_v1` / `cwspn_history` options and record markers, so earlier completed repair jobs do not prevent this new correction. It saves original Header/menu content and CSS before writing, plus revisions, and verifies stored changes. A failed or interrupted job can be resumed with the same button. Completed corrections preserve subsequent edits. This is not an automatic transaction rollback; use the full database backup or recorded before-images for recovery.

Only authenticated administrators with editing capabilities, a valid nonce and explicit backup agreement can process the changes. Request and record execution leases prevent overlapping updates; record writes coordinate with the earlier migration runner's execution lease. No unrelated pages, forms, files, roles or menus are modified.

After success, clear site/CDN caches and verify the menu. The saved header/menu and CSS remain after plugin deactivation; you can deactivate it once finished. Do not subsequently run an unfinished older header/portal installer that would overwrite this correction.

## Verification

Offline integration tests use WordPress's real block/HTML parsers with an isolated WordPress fixture. They verify inline navigation and a referenced Main menu, inactive ED Primary preservation, exact order, utility removal, retained EyeCanSeeAgain/submenus, no duplicates, before-images, repeat-run edits and missing-anchor errors. Queue tests cover dependencies, interruptions and resume. Local browser fixtures using actual theme CSS and mobile scripts check desktop/phone widths and the one-button AJAX flow. Tests do not run changes against the online site.
