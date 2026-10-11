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
