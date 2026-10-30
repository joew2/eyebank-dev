# For Physicians

`/for-physicians/` uses the **For Physicians — Editable Page** template. Edit the page's headings, paragraphs, buttons and Shortcode block in the page editor. Both audience groups remain visible in the editor; only the correct group is rendered on the website. Keep the `ed-physicians-guests` and `ed-physicians-members` CSS classes on their enclosing Group blocks. These control server-side visibility, not authorization on the destination pages.

Visitors see the existing Toolset `[wpv-login-form allow_remember="true" remember_default="true"]` shortcode, password-reset and registration links. Logged-in users see the transplant and research request buttons. Toolset Views remains required for login functionality. All internal links are relative. No Bootstrap or media-library assets are required. Existing destination access controls remain responsible for protecting tissue-request pages.

The **For Physicians — Backup Layout** template renders the reusable `eyedonation/for-physicians` pattern. Insert that pattern into another page to start with editable blocks. The audience classes also work on inserted copies. Page caching is disabled for the physician slug and either physician template; configure any host/CDN page cache to bypass `/for-physicians/` as well.

Migration **044** in Tools → EyeDonation Migrations previews the existing content and template, requires the Toolset shortcode, and replaces only this page. It retains builder data, records before/after fields, checks for edits since preview, and preserves later editorial changes on repeat runs. Use a recoverable backup before installation. Upload the matching theme and migrations packages together. Local conversion also saves `ed_for_physicians_legacy_backup`.
