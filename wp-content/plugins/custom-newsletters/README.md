# Custom Newsletters CWS

Manage issues at **Newsletters**, a top-level admin sidebar item directly below Pages with its own document icon. The title is the newsletter name; Issue is a label such as Summer 2026. Upload/select a PDF with the WordPress Media Library. Set Order (lowest first) to control archive order, and publish to display. Draft/trash records are excluded. Removing a PDF from a record does not delete the media file.

Activation imports the 15 supplied eye to eye issues, Summer 2026 through Winter 2019. Import is idempotent and preserves later edits/deletions. Starter PDFs use upload-relative paths in `issues.json`; uploaded replacements use attachment IDs. Deactivation preserves records. No data is removed automatically.

On `/about-donation/`, the native editable button with class `ed-newsletter-issues` opens the archive. The mailing-list button is unchanged. The plugin supplies its own styles; no theme source or completed page migration is modified. It uses native dialog focus containment, Escape/backdrop/close controls, focus restoration, reduced-motion support, and animated opening/closing. Without JavaScript the button links to `/about-donation/?newsletters=1#cwsnl-dialog`, which renders the archive open.

PDFs use same-site links with `download`; browser PDF settings may open them instead. All 15 referenced files were verified present in the local uploads directory. To move to another site, install/activate this plugin and copy the corresponding uploads paths listed in `issues.json`. Existing WordPress content migrations should also include the `cws_newsletter` post type and its post metadata to preserve subsequent editorial changes; attachment IDs must be remapped by the migration tool. The plugin ZIP contains code and starter records, not the 65 MB of existing PDFs.

Security: standard WordPress page editing capabilities, nonce verification and autosave/revision checks protect metadata writes; inputs are sanitized, output escaped, and selected attachments must have PDF MIME type. Only published records render publicly.

## Verification

Local verification on October 21, 2026: imported 15 records; repeated import retained exactly 15. Every seed PDF exists locally. Playwright checks passed at 1440px and 390px: native modal opens with all 15 download links, fits viewport, receives focus, closes with Escape, and restores trigger focus. JavaScript-disabled archive fallback passed. Metadata checks passed for missing nonce, anonymous writes, issue sanitization and rejection of non-PDF attachment selection; the temporary draft was removed. PHP lint, JavaScript syntax checks and `git diff --check` passed.

Version 1.0.2 displays the established newsletter title as lowercase “eye to eye” in the popup, including existing records. Other publication titles retain their entered capitalization.
