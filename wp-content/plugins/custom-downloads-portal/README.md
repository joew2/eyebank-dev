# Custom Downloads Portal SWS

Standalone replacement for the old file-manager shortcode. It reads the existing `wp-content/BOD_files` and `wp-content/MAB_files` trees and does not depend on Advanced File Manager.

## Install and cut over

1. Back up the database and both document roots.
2. Install and activate this plugin. Activation adds a bounded deny-all block to each root's `.htaccess`; it never replaces unrelated rules.
3. If Nginx or a reverse proxy serves static files, add `server/nginx-protected-libraries.conf` to the effective virtual host, reload it, and purge every server/CDN cache for both roots.
4. In WordPress, open **Tools → Downloads Portal Security** and run verification. The portal stays locked until logged-out HTTP requests to a private test file in both roots are denied.
5. Replace `[filemanager foldername="*"]` on the existing User Downloads page with `[custom_downloads_portal]`. Do not create another page or duplicate its heading.
6. Test each approved role, controlled downloads, uploads and deletion with disposable files. Only after that disable the old plugin and confirm its old endpoints no longer respond.

Rollback application code by restoring the old shortcode and reactivating the old plugin. Leave the new deny-all server protection in place; raw document URLs must not become public.

## Verified role mapping

The policy uses existing keys and verifies their stored labels on every request:

- `administrator` / Administrator: manage BOD and MAB
- `eb_staff` / EB Staff: manage BOD and MAB
- `eb_staff_bod_only` / EB Staff BOD ONLY: manage BOD; no MAB access
- `board_of_director` / Board of Director: read BOD
- `medical_advisory_board` / Medical Advisory Board: read MAB

Multiple roles receive the union. No generic WordPress capability grants portal access. The plugin never creates roles or changes user assignments.

## Environment

- WordPress 6.2+ and PHP 7.4+; production target supplied by the owner: WordPress 7.1.4, PHP 8.2.
- `ZipArchive` is required for safe DOCX/XLSX/PPTX validation.
- Set PHP `upload_max_filesize` above 50 MB and `post_max_size` above 51 MB (multipart overhead). Nginx/Apache/proxy request limits must also permit it.
- Files are limited to 50 × 1024 × 1024 bytes and: PDF, JPEG, XLS/XLSX, PPT/PPTX, DOC/DOCX, ZIP.
- Existing unsupported/control files remain untouched but are hidden and inaccessible through the portal. New uploads are staged in a mode-0700 directory outside the WordPress web root.
- The plugin records up to 500 mutation events for 90 days in a non-autoloaded private WordPress option. It records metadata only, never file contents, absolute paths, cookies or nonces.
- Type/signature checks are not malware scanning. ClamAV was reported as available on the live host, but command-line execution is intentionally not used by this plugin. Connect a trusted synchronous scanner through a future reviewed integration if required.

The existing page wrapper may constrain width. The current EyeDonation `ed-main-full` User Downloads template is the local full-width setting; the plugin itself uses 100% of the available content width and does not override global theme containers.

### Local HTTPS verification

Version 1.0.1 permits self-signed HTTPS certificates only when WordPress reports a `local`/`development` environment or the site uses an unmistakably local hostname such as `eyebank`, `localhost`, `*.test`, or `*.local`. The verifier still requires both protected test URLs to return an HTTP denial and never relaxes TLS checks for the live `eyedonation.org` site.

Version 1.0.2 uses a compact portal type scale: document names, folder names, dates and supporting text are 16px; primary controls are 18px with tighter padding. File dates use the abbreviated `Nov 10, 2026` format.

Version 1.0.3 removes the separate folder Open button. Clicking or tapping the folder icon/name opens it, the Size and Modified metadata sit together on the right, and the flexible Name column receives the available width. File Download controls remain separate.

Version 1.0.4 displays main-list folder icons in dark text by default and changes them to blue only on hover or keyboard focus. Sidebar tree icons are unchanged.

Version 1.0.5 removes duplicate folder-tree requests. Desktop and mobile navigation now share one bounded folder cache, only the expanded path is loaded, stale responses are ignored, and the document list becomes interactive without waiting for the sidebar tree to finish.

Version 1.0.6 reduces document names, metadata, counts and utility controls to 14px with proportionally smaller icons. On desktop, Up one folder shares a compact row with the right-aligned Upload, New Folder and Delete controls; mobile keeps the controls stacked with accessible tap heights.

Version 1.0.7 grants EB Staff BOD ONLY full management access within BOD_files—browse, download, upload, create folders and delete—while continuing to deny that role all MAB_files access.

Version 1.0.8 preserves a successful direct-access verification when the plugin is replaced or reactivated on the same URL/filesystem environment. The Security screen records separate GET and HEAD diagnostics for each library, including HTTP responses or connection errors; verification still locks if an actual re-test fails.

