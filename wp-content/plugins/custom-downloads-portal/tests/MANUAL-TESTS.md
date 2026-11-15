# Manual and browser acceptance checks

Use disposable fixtures only. Do not test recursive deletion against real board documents.

- Roles: guest, unapproved logged-in user, Board of Director, EB Staff BOD ONLY, Medical Advisory Board, EB Staff, Administrator, and a user with multiple approved roles.
- APIs: missing/forged nonce, guessed MAB request from BOD-only, guessed BOD request from MAB-only, mutation from read-only, invalid library, traversal and encoded traversal.
- HTTP protection: logged-out GET and HEAD for a known test file in both roots through every hostname and direct origin. Purge CDN/server caches and repeat after an authenticated portal visit.
- Upload: valid sample for all ten extensions; spoofed formats; macro Office formats; malformed Office ZIP; hidden/config name; exactly 52,428,800 bytes; one byte over; interrupted upload; same-name concurrency; overwrite cancel/stale/confirm.
- Navigation: root, nested folders, breadcrumb ancestors, Up one folder, sidebar lazy branches, mobile folder panel, tab switch, sort, selection reset, cPanel-added file refresh.
- Delete: cancel file; confirmed disposable file; cancel populated folder; confirmed disposable populated folder; linked/control file rejection; partial filesystem failure; root rejection.
- Responsive: capture the six specified states at about 1440px and 390px; verify 320px, 430px and tablet widths have no horizontal overflow. Verify download targets are at least 44px.
- Accessibility: keyboard-only tabs/tree/list/dialogs, visible focus, named icon buttons, status announcements, and screen-reader labels.
- Lifecycle: deactivation/uninstall preserves documents and deny-all rules. Old plugin endpoints are disabled only after successful cutover.

