# Selected page conversions — migrations 046–056

Only the following eleven pages are replaced. Each has an **Editable Page** template, a **Backup Layout** template, and a reusable `eyedonation/{slug}` pattern. Edit normal copy, headings, lists, images and links in the page editor. Shortcode blocks retain the existing plugin functionality; edit form fields and winner data in their owning plugins.

| Migration | Page | Preserved functionality |
|---|---|---|
| 046 | `/responsibilities-and-benefits/` | Complete board responsibilities, qualifications and benefits copy |
| 047 | `/privacy-policy/` | Existing policy copy and original theme-bundled banner |
| 048 | `/young-ambassador-scholarship-criteria/` | Criteria, application/resource links and live `scholarship-winners` Toolset View |
| 049 | `/stem-scholarship/` | Criteria, application link and live `stem-scholarship-winners` Toolset View |
| 050 | `/medal/` | Gift of Life Donor Medal copy, banner and Gravity Form 35 |
| 051 | `/registration-tissue-request/` | Toolset `request-tissue-registration` user form |
| 052 | `/member-profile/` | Toolset `user-profile` edit form for signed-in users; login form for guests |
| 053 | `/enter-new-password/` | Toolset reset-password form, with local success/failure destinations; logged-in explanatory text |
| 054 | `/user-downloads/` | Existing `[filemanager foldername="*"]` for signed-in users; login form for guests |
| 055 | `/transplant-surgery/` | Main article, original banner, cornea illustration and complete custom-field sidebar |
| 056 | `/thank-you-your-submission-has-been-sent/` | Request-sent confirmation without duplicate headings |

## Shared layout and media

The layouts use the site's content width, Arial typography and blue palette through `assets/css/page-conversions.css`. Account forms have a narrower centered container. The Transplant Surgery sidebar sits beside the main text on desktop and stacks below it on mobile. Scoped CSS replaces the Bootstrap column rules used inside the two retained scholarship Views; no Bootstrap dependency is added.

Static banners, the cornea illustration, existing winner portraits and scholarship promotional graphics are bundled in `assets/page-conversions/`. Winner Views stay dynamic: existing portraits resolve to the theme using `media.json`, while future portrait uploads continue to work through the media library. Internal page links are relative; shortcode redirect URLs resolve using the destination site's `home_url()`.

All existing policy, medical, scholarship and confirmation copy is preserved. Scholarship deadlines are preserved as supplied; this migration does not revise eligibility, deadlines or medical/policy wording. The live winner Views restore the current winner records instead of the older snapshots in builder-generated page content.

## Member and form behavior

Keep the `ed-converted-guests` and `ed-converted-members` classes on their Group blocks. Both groups are editable in the editor; the server renders only the appropriate audience on the public website. The existing Toolset and file-manager plugins retain responsibility for permissions and form processing. No replacement authentication or storage system is introduced.

Registration, profile, password-reset and downloads pages send no-cache headers and set `DONOTCACHEPAGE`. Exclude these paths from any separate host/CDN full-page cache as well. The reset shortcode continues to validate reset keys through Toolset; this conversion does not bypass that validation. Success returns to `/for-physicians/`; failure returns to `/enter-new-password/`.

## Migration and recovery

Upload the matching theme and EyeDonation Migrations plugin ZIPs. Under **Tools → EyeDonation Migrations**, use the individual **046–056** preview/install panels. Each panel previews and changes only its named page. Existing IDs and URLs are retained; missing pages can be created. Existing builder data and custom-field data remain stored, with the builder disabled only on these eleven pages.

Dependencies are checked before each installation: Toolset Views and the named winner Views; Toolset Forms and the two named user forms; Gravity Forms and form 35; and the existing `filemanager` shortcode. Migrations do not recreate plugin form definitions, View queries, people records, file-manager documents or form submissions. Preserve those through the site's normal database/plugin migration process.

The runner requires a fresh preview from the current administrator, rejects edits made after preview, records before/after fields and saves page revisions and per-page backup options (`edm_selected_{slug}_backup`). Interrupted runs resume using the saved plan, and completed runs preserve later editorial changes. Frozen payload/template/media checksums detect changes to the approved migration payloads.

Use a full recoverable database backup before production installation. For page-only recovery, the backup option/history records the original `post_content`, `_wp_page_template`, `tcb_editor_enabled` and `tcb_editor_disabled` values. Original surgery custom fields remain untouched. No other page content, menu or template assignment is part of these migrations.

## Verification

- WordPress's editor parser accepted all eleven payloads with no invalid blocks.
- Public layouts checked in Chrome at 1440px, 390px and 320px: one main H1, no raw plugin shortcodes, banners loaded, no horizontal overflow, and forms/winner Views rendered.
- Signed-in profile, downloads and password-reset behavior checked at 1440px and 390px; account responses verified to send no-cache headers.
- Main responsibilities, privacy and surgery article copy checked against the source; surgery sidebar restored from the original custom fields.
- Isolated migration tests passed preview conflicts, backup capture, interrupted-run resume, repeat-run preservation, dependency checks and lease cleanup.
- All migrations passed repeat-run checks. A before/after hash comparison confirmed that unselected pages' content and template assignments were unchanged.
- No real registrations, profile edits, medal submissions or password-reset requests were submitted. Email delivery and submission processing were not exercised.

A repeatable public-page browser check is in `tests/selected-pages.cjs`. Run with `ED_PLAYWRIGHT_MODULE` set to a Playwright module location and, if needed, `ED_CHROME_PATH` and `ED_SITE_URL`.
