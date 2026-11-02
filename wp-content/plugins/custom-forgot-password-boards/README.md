# Custom Forgot Password Boards CWS

Version 1.0.0 · Creative Way Solutions · GPL-3.0-or-later · WordPress 6.6+ / PHP 8.1+.

Install this standalone plugin on the live site, activate **Custom Forgot Password Boards CWS**, and open **Tools → Forgot Password Boards CWS**. Check the backup agreement and click **Process all updates**. No manual previews, other migration plugins or theme ZIP update are required. Activation itself does not change page content.

## Preserved legacy behavior

Source: `eyebank/page-forgot-password-boards.php`, the original Forgot Password Board template.

- Existing boards recovery page is `/password-boards/` (local ID 380388). It is located by path, with a fallback to a page using the original `page-forgot-password-boards.php` assignment. Existing IDs, titles, URLs, publication status and unrelated access metadata are preserved; if no matching page exists, the plugin can create it.
- Retains Toolset's `[wpv-forgot-password-form]`.
- Success and failure return to the destination's **Member** page, corresponding to old page 670. Fixes the legacy failure URL's `wwww` typo.
- Reset email links point to the destination's **Enter New Password** page, corresponding to old page 961.
- Signed-in visitors see **You are currently logged in.** Toolset's original forgot-password shortcode already suppresses its form for logged-in users. The notice uses Toolset's existing `wpv-conditional` / `wpv-current-user` shortcodes, so no additional custom runtime shortcode is needed.

Destination URLs are resolved from the destination's pages, not copied from development or tied to local IDs. Toolset Views and the published Member/Enter New Password pages must exist before installation; missing dependencies are reported before content writes. The Member page and reset-password page themselves are not migrated or edited.

## Editable template and storage

The plugin installs **Forgot Password Boards — Editable Page** as a saved `wp_template`, assigns it to the boards page, and writes the supplied heading, conditional notice and form as native Group/Heading/Shortcode blocks in `post_content`. Text/layout can be edited under Pages. The dedicated template displays Post Content between the existing Header and Footer. Those shared parts and Navigation records are not modified.

The page's legacy Thrive override is disabled while saved builder data remains stored. Centered, responsive styling is appended to WordPress Additional CSS, preserving unrelated CSS. Both template/content and styling are bundled in this one plugin. The source theme also contains the corresponding file template, reusable pattern, styling and helper for future theme packages; the live migration does not require that theme update.

## Recovery and caching

Independent options `cwsfpb_job_v1` and `cwsfpb_history` store progress and before-images for content, page template/Thrive flags and CSS. Page revisions are saved. Interrupted runs resume; repeated completed runs preserve later editorial changes. A full recoverable database backup remains the recovery baseline. The plugin does not provide automatic transaction rollback.

Keep the plugin active on a live installation using an earlier theme package: its small `template_redirect` hook sends no-cache headers and sets `DONOTCACHEPAGE` for this authentication page. The updated local theme contains the same cache protection. Also exclude `/password-boards/` from any external host/CDN page cache. The saved content/template/CSS remain after deactivation, but an older theme will then need equivalent host/runtime cache handling.

No reset requests, user password changes or emails are submitted by the migration. Existing Toolset validation and email/password-reset processing remain responsible for the form.

## Checks

Isolated WordPress-parser/record fixtures verify the existing page identity, correct shortcode/redirect mapping, missing dependency protection, editable template, retained unrelated pages/access metadata/CSS, original before-images, repeat-run preservation and single-button UI. Actual installed local Toolset is checked for guests and signed-in users through the full WordPress content pipeline, without submitting the form. The local migration completed on page 380388 with both page and styling steps verified. No live-site migration is performed while packaging this plugin.
