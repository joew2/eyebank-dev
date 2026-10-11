# Verification — 0.1.0 unreleased preview

Tested WordPress 7.1.3, PHP 8.4.17, local MAMP database, local Chrome. Tests used an isolated single-site fixture (`edrehearsal_` tables) with synthetic public content, different destination IDs/domain and the nine original supplied assets. Existing Eye-Bank tables were not modified. HTTP and mail were disabled in the fixture. This is **not a restored fresh live backup**, and does not satisfy the final live-copy acceptance gate.

- **44 passing integration assertions:** public/nonpassword eligibility, drafts/private/future exclusions, Unicode, AND/quoted/minus terms, safe highlights, actual SQL totals/disjoint pagination, ID exclusions, taxonomy precedence/group parity, save-triggered index updates, complete first-paragraph extraction, no fabricated intro, all nine exact-path/checksum media mappings, read-only target preview, saved submenu entities, destination navigation binding, bounded checkpoints, forced second-operation failure/retry, preserved homepage ID and type, destination URLs, approved settings, coordinated batched rebuild completion, idempotence, concurrent writer rejection, unknown migration rejection, unauthorized execution, editor conflicts and completed checksum drift.
- **Installed WordPress block-parser validation:** all shipped HTML templates, native parts, rendered PHP patterns and migrated fixture homepage reported no invalid blocks.
- **Browser checks:** one main landmark, three news cards, no horizontal overflow at 1366/390px, native mobile menu opens, full-screen search opens with input focus, Escape closes and restores trigger focus, zero frontend page errors, enhanced results/pagination rendering, native GET search visible with JavaScript disabled.
- **Registered WordPress AJAX handler checks:** guest capability rejection, missing/invalid nonce rejection, and valid-nonce unregistered-ID rejection passed through the actual action handler. Full HTTP admin/session rehearsal remains pending.
- **Additional fixture checks:** newly published post appears in the newest-three Query, post body edits update intro immediately, password-protected posts absent. Both plugins disabled: saved menu renders, three cards and paragraph blocks render, popup works, native fallback Query replaces enhanced results. Responsive History picture selects the mobile source at 390px.
- Existing latest three real public posts were inspected read-only: they use Gutenberg paragraph/image blocks and featured images, matching supported extraction. Their records were not migrated or overwritten.
- PHP source syntax checked; packages contain only project source/assets/docs/tests. No credentials, database dumps, registration data or unrelated uploads are included.

Screenshots are fixture screenshots with pending footer/utility/contact inputs and the supplied Support photograph. They are implementation evidence, not launch-ready screenshots. See home-desktop.png, home-mobile-top.png, history-mobile.png, footer-mobile.png, search-popup.png and search-results-{desktop,mobile}.png.

## Still required before acceptance

Owner-approved payload/assets/copy; restored fresh live-copy rehearsal; full native Site Editor interaction/editor preview QA on existing customized accounts; multisession HTTP endpoint concurrency; live legacy forms/registration/login and plugin-hook review; existing-editor-override adoption/update rehearsal; complete keyboard/contrast audit; translation/accessibility decisions; no-placeholder final screenshots; production URL/configuration checks during authorized cutover. Do not infer these from synthetic assertion counts.

The block-parser harness loads editor packages outside their full editor context and emits one unhandled context error with no validation failures. Public-page browser checks emitted zero page errors; full native editor QA remains open.

Owner-input update verified: existing homepage baseline unchanged, Support attachment uniquely resolved (local ID 387861), all nine originals mapped in the destination fixture, editable Support Cover passes native block validation, desktop/mobile screenshots inspected, panel contrast and mobile overflow checked. No existing-site migration was executed.

## Applied local site

Owner authorized using placeholder destinations and implementing the local design. A private full database backup was taken first. EyeDonation and both plugins are active; all six migrations completed through the runner against homepage 386743. An interrupted homepage write reconciled successfully on retry without duplicate records. The existing plugin-heavy copy required a higher CLI memory limit for deployment. Current-site media and post records were used; no synthetic news was imported. Local-only application does not imply production readiness or full legacy business-flow acceptance.

The explicit follow-up migration 007 completed locally. It enables the approved native homepage by disabling its old Thrive content override, while preserving the stored builder content.

Final applied-site Chrome verification: homepage has one main landmark, expected navigation and actual latest three published news posts; search dialog opens and Escape closes it; 390px mobile viewport has no horizontal overflow; placeholder route displays Destination pending; cornea search returns six records on its first page. No browser page errors after pre-rendering placeholder template parts before wp_head. Public search metadata was refreshed for 307 published records after the legacy homepage override was disabled. An existing Thrive popup was dismissed for control testing. PHP syntax passed for all 27 package PHP files.

008 applied after a fresh private database backup. Idempotent second run passed. Classic-menu database record and assignment fingerprints and footer-menu content were unchanged. Chrome verified all 22 supplied labels and destinations, no link on Give Now/More, desktop submenu controls, mobile expanded submenu visibility, no horizontal overflow at 390px and no JavaScript page errors.

Homepage recovery verified: restored editable native page content matches the saved complete pattern; pattern is registered in WordPress after refreshing theme discovery cache; current menu and legacy-editor flags/content unchanged; recovery is idempotent. Live front-page and assignable homepage templates display Post Content for page-editor text adjustments. The separate saved layout template includes the complete theme pattern. Complete layout resolves supplied media without migration runtime code.

010 local integration: 13 assertions passed, covering destination resolution, read-only preview, theme pattern payload, rejection of changed actor/expired preview/changed target, editable content/template application, stored Thrive and primary-menu preservation, retry without rewrite, completion and before-image retention.

Header search control now uses the supplied SVG path in white with a 24px icon and 44px accessible hit target. Removed outlined button styling and inherited navigation child margins; desktop gap is 16px. Chrome verified dialog operation, Escape focus restoration, search results and mobile overflow. JavaScript syntax passed. Presentation-only update requires no content migration.

Hero v2 verification: migration 011 applied and completed retry skipped. Serialized non-hero sections remained identical; recorded base 001–006 and 010 checksums were unchanged. Chrome confirmed revised image source and heights: 1200/1275px widths → 500px; 1276px → approximately 791px; 1440px → 840px; 390px → 362px. No horizontal overflow at those widths. A 1200px screenshot confirms Resources appears below the hero within a 900px viewport.

Header refinement: logo 310px wide; brand vertical padding 30px; primary links and submenu text 16px; top-level container gap increased from measured 24px to 48px. Scoped CSS preserves footer branding. Chrome checks confirm sizing, search keyboard behavior and responsive overflow.

Donor panel update: migration 013 applied, idempotent retry skipped, and target content matched the scoped donor-panel transformation. Existing migration checksums remained intact. JavaScript syntax passed. The page editor keeps the panel in normal flow with link overlay pointer events disabled for editing.
