# Custom Site Repairs CWS

Version 1.1.0 · Creative Way Solutions · GPL-3.0-or-later. WordPress 6.6+, PHP 8.1+. Directory/entry point: `custom-site-repairs/custom-site-repairs.php`.

## Install and run

Install this as a **new plugin**, not as a replacement for EyeDonation Site Migrations. Activate it, open **Tools → Site Repairs CWS**, check the backup agreement and click **Process all updates**. Keep the tab open. There are no manual previews. Uploading, activating or visiting the screen performs no content migrations. The original migration plugin is not required and can remain deactivated.

The single click processes seven short authenticated requests:

1. Create a dedicated editable Post Content template for the repaired member/request pages.
2. Save scoped responsive header/page CSS while preserving unrelated Additional CSS.
3. Add **Transplant Portal → /transplant/** and **Research Portal → /researchers/** to the existing top utility buttons. Retain EyeCanSeeAgain.org and the existing primary navigation. Avoid duplicate portal buttons on retries.
4. Reinstall **User Downloads** with member/guest blocks, its original `[filemanager foldername="*"]`, and the existing Toolset login integration. Use a wider file-manager layout instead of the narrow account-page container.
5. Install **Request Transplant Tissue**, retaining the destination's existing Hipaatizer renderer and initialization settings.
6. Install **Request Research Tissue**, retaining the destination's existing Gravity Forms shortcode and form ID.

The repairs adjust the top utility row, including expanded/compact header spacing, consistent button sizes and wrapping at tablet/phone widths. They override a theme tablet rule that otherwise hides the utility buttons. They do not change the primary menu's font size or overwrite the primary menu.

7. Install **Sign Up on page 387956**, using the original theme's `ed_sign_up_content()` payload, the `sign-up` template and `[gravityform id="85" title="false"]`, exactly as the earlier signup installer did. The new task also restores the template's Post Content structure so the page remains editable. It is independent of earlier repairs and runs even when those are already complete.

## Sign Up target

Page 387956 must be an existing WordPress page with the `sign-up` slug, and Gravity Forms form 85 must exist. The plugin validates these before writing. It does not substitute an unrelated page or recreate a missing form definition. The existing page's title, URL, publication status and unrelated metadata are retained. Content/template and Thrive flag before-images are saved. Existing theme signup CSS/JavaScript continue to apply. No form submissions are made by the migration.

## Content and dependencies

The matching EyeDonation theme remains required. Downloads requires the existing Advanced File Manager and Toolset Views shortcodes and the site's existing folder permissions/documents. Research requests require Gravity Forms and the referenced request form. Transplant requests retain the existing Hipaatizer service; the plugin neither replaces its processing nor exports any submitted data.

If a request page has no form, use the verified original site integration: Hipaatizer form `39830e66-a85a-480b-a84b-def1bb893104`, or Gravity Form 67. A different recognizable destination configuration takes precedence. Unexpected alternative form embeds are left intact and reported instead of replaced. Gravity Forms definitions are validated before content is changed; missing definitions must be restored through that plugin. No form definitions, files, users or submissions are imported.

Existing page IDs, URLs and unrelated metadata/access rules are preserved. Missing pages can be created with their original slugs. New content is stored in the pages' `post_content`; a saved `wp_template` supplies the surrounding header/footer and Post Content. The repair disables each page's legacy Thrive content override and retains its stored builder data. Headings/layout are editable in Pages; form fields remain managed by Gravity Forms or Hipaatizer. The existing theme's Downloads-menu role restriction remains unchanged; this package does not broaden file-manager permissions.

## Progress, recovery and activation

Independent work continues after a prerequisite error. Correct the named issue, then click the same button to retry unfinished steps. Closing the browser does not discard checkpoints. Completed repairs preserve subsequent edits. Do not run the broader original migration job concurrently or later apply an unfinished full-header replacement over these repairs.

Options `cwsr_job_v1` and `cwsr_history` hold job progress, original content/metadata, actor information and per-field checkpoints. CSS before/after values are captured. Page revisions are also saved. All changes use WordPress APIs, capability checks, a nonce and explicit backup agreement. Request leases prevent overlapping processors; record writes coordinate with the existing migration execution lease. This is a resumable job, not an all-or-nothing transaction or automatic rollback.

After successful processing, clear caches and verify the three pages signed in/out and with the applicable roles. Preserve existing host/CDN exclusions for member/form pages. The repaired header, pages, saved template and Additional CSS remain after deactivation; you may deactivate the repair plugin once finished. No records are deleted on deactivation/uninstall. Recovery uses the database backup or retained before-images.

## Verification

- All seven real repair handlers tested against an isolated in-memory WordPress fixture: IDs, access metadata, form settings, CSS, backups, history, retries and missing-page creation.
- WordPress's actual block/HTML parsers used to verify header transformations, retained primary navigation and no duplicate links.
- Queue tests cover error reporting, independent continuation, dependency ordering, interruption/resume, concurrent edit conflicts and preservation of completed work.
- Local Chrome fixtures with the theme's real CSS/mobile scripts checked at 1440, 1200, 1024, 900, 768, 390 and 320px, expanded and compact: all three utility links visible, no clipped links or horizontal overflow.
- Browser tests with simulated AJAX check the agreement, automatic processing, visible errors and one-click resume.
- External Hipaatizer rendering, real form submissions and live file downloads were not exercised. No repairs were executed against the online site during development.
