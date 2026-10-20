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

Signup panel spacing refinement: reduced desktop width from 350px to 300px and mobile width from 280px to 266px. Cleared inherited link block margins so the arrow is centered within the entire card. Verified fixed scrolling, full-card click target and no mobile overflow.

Header alignment: utility button centered against logo with inherited block margins cleared. Search SVG reduced 40% (24px to 14.4px) while retaining 44px click target; visible icon gap matches 48px menu gaps. Dropdown indicators doubled from 0.6em to 1.2em. Chrome verified alignment, sizing, search keyboard operation and responsive overflow.

Complete header is sticky: the white branding section and blue navigation bar share one sticky template-part wrapper. Desktop/mobile WordPress toolbar offsets are handled. Magnifier increased 30% from 14.4px to 18.72px while preserving the 48px visible menu gap and 44px click target. Chrome checked scroll positions and search dialog operation on desktop/mobile.

Compact sticky header: after scrolling beyond 100px, branding animates to a 150px logo with 10px vertical padding; navigation uses 87% blue opacity. Header returns to full size at page top. Separate enter/exit thresholds prevent size-change scroll jitter, events are passive and frame-throttled, and existing reduced-motion styles suppress transitions. Desktop/mobile scroll and search controls verified.

Compact utility button: animates padding to 5px on all sides and border to 1px; normal state keeps its regular padding and 2px border. Verified at page top and after scrolling with the compact logo/header.

Homepage effects: smooth vertical submenu-arrow flip, top-level hover/focus background, strong blue/white submenu hover/focus highlight, and 2% donor-panel enlargement anchored at the right edge. Reduced-motion mode disables enlargement. History picture overrides constrained block max-width/margins to span the viewport while retaining readable panel width. Browser verification covers desktop, laptop and mobile widths. These are theme presentation changes; no content migration is required.

Main-menu hover refinement: square dark blue background (#043b90), 12px horizontal padding, transparent submenu toggle background to prevent double tint, and desktop margin compensation to preserve link spacing. Existing smooth arrow transition and submenu highlighting remain.

Latest homepage refinements: flush desktop dropdowns without borders, adaptive submenu widths, chevrons reduced 10% in size and stroke, and matching search highlights. Resource cards use full-card native links with a 3.5% photo zoom and shaded gradient; news photos share the effect. Adam’s editable image/button render as one panel with an overlaid button and bottom shading. Native buttons have hover shadows and pressed colors; the three filled homepage calls to action use 22px corners. Footer social links use the exact supplied white SVG paths with approved URLs and transparent service backgrounds. Browser checks verified menu geometry, card links/effects, testimonial placement, social paths/URLs, button feedback, and news image alignment at desktop/mobile sizes.

News previews use up to 24 words from a manually supplied excerpt, falling back to the first readable body paragraph. Read more is inline, teal, and inherits the summary’s font size. Post bodies are preserved. PHP syntax checks passed; live layout and article destinations were checked before the final two-word reduction and excerpt preference update.

Approved footer — October 12, 2026: contact address and phone, eight supplied destinations, EBAA nonprofit caption, and the supplied 2026 copyright wording replace all placeholders. The theme provides four desktop columns, two tablet columns, and one mobile column. Native navigation and paragraphs remain editable. Chrome checks verified every supplied link and content string, centered copyright, and no horizontal overflow at 1440/800/390px. Migration 015 explicitly updates saved footer overrides, records a before-image, and skips completed retries to preserve later editor changes. Its frozen payload sits under migrations/payloads, outside the original migration checksum bundle.

Dynamic footer copyright: the approved footer renders its year with wp_date("Y") using the WordPress site timezone. A scoped group render filter supports both existing saved footers and new theme defaults without replacing editable wording or changing frozen migration payloads. A simulated 2027 rollover verified the year update and that unrelated dates/content remain untouched.

Tablet/mobile header — October 12, 2026: at widths up to 1024px the sticky header uses one white row containing a left-aligned logo, Donate Life signup link, and blue three-line native hamburger. The floating donor panel and desktop blue bar/utility button are hidden at these widths. Native primary navigation moves into the row, and search remains available inside its overlay. The donor link uses the same registration destination as the desktop floating panel, with a bundled logo asset. A scoped rendering module covers both saved header overrides and theme file defaults without rewriting existing navigation content. Chrome checks at 1024/768/390/320px passed for menu/submenu access, search, Escape, sticky positioning, donor visibility/link, no horizontal overflow, and restoration of desktop controls on resize.

Mobile footer mockup: up to 1024px, footer content forms a centered stack with social icons before the logo, centered contact information and eight navigation links, and thin white dividers above/below the navigation. The accreditation/copyright stay centered, with prior 15px/13px font sizes preserved. Browser checks at 390/768/1024/1440px confirmed order, borders, links, no overflow, and the retained desktop columns.

About Us — October 13, 2026: replaced page ID 30 with the supplied desktop/mobile design and PDF copy as valid native blocks. Added an editable Post Content template, a complete reusable pattern, and a standalone saved-layout template. Inline SVG paths match the four supplied icons, with namespaced classes/clip IDs. Migration 016 supports both existing-page replacement and new-page creation, captures before-images, sets the native template before writing content, preserves builder data, and skips completed retries. Browser tests at 1440/1024/390/320px and native block-parser checks passed; fixture creation, preview-actor guard, retry preservation, and historical checksums were verified.

Shared page styles — October 14, 2026: Home/About now share typography variables and a single linked-photo card component. Matching heading/body sizes, blue heading color, white 24px card labels, 16px padding, hover effects, and correct overlay stacking were checked at 1440/1275/768/390/320px without overflow. At 1275px, the About intro is 1200px wide and approximately 79px higher than its baseline; hero height is reduced exactly 70px. Both public and editor styles load the shared stylesheet. Page content and frozen migration files remain unchanged.

About hero replacement — October 14, 2026: supplied desktop/mobile upload URLs are installed through scoped migration 017. Original migration 016 checksum/history is preserved. Updated fresh installer 018 preview resolves the new photographs, and the pattern/backup template use the latest helper. Browser checks at 1275/1440/768/390/320px verified both URLs, loaded images, 1200px desktop intro width, no overflow, and shorter framing (approximately 742px hero at 1275px versus the previous 976px). Bundled approved-photo copies support deployments without existing uploads.

Pixel typography — October 14, 2026: converted all rem units in theme CSS/theme.json to pixels using a 16px base. The requested 1.35rem exception is now 18px and --ed-font-card is 20px. No rem units remain in theme code. Browser checks confirmed 18px body text, 20px photo-card labels, and no overflow on Home/About at 1440/390/320px.

Contained search popup — October 14, 2026: responsive white panel with drop shadow, dimmed backdrop, opening/closing animations, updated description, and circular X centered on its upper-left corner. Native modal semantics, keyboard focus trapping, Escape, focus return, scroll locking/restoration, and reduced-motion behavior are preserved. Chrome checks passed at 1440/768/390/320px, including exact close-control alignment and description text.

## October 14, 2026 — Recipients and donor families

Verified the published page at 1440, 1024, 390, and 320px: two columns on larger screens, stacked cards on phones, loaded photos, and no horizontal overflow. Checked resource destinations and native WordPress block validity (zero invalid blocks). Migration 019 applied successfully; its completed-run retry skipped without rewriting content. PHP lint passed.

## October 15, 2026 — Approved homepage links

Replaced homepage and saved header placeholder destinations with the supplied resource, About, video, EyeCanSeeAgain.org, and donation URLs. Updated the default header and reusable homepage pattern; unresolved original placeholders in older layouts resolve to the approved URLs without overriding custom links. Verified rendered homepage destinations and both EyeCanSeeAgain.org buttons in Chrome.

## October 15, 2026 — Our History verification

PHP lint and native WordPress block validation passed (zero invalid blocks). Browser checks at 1440, 1024, 390, and 320px confirmed all ten images load, all 24 paragraphs render, and no horizontal overflow occurs. Visually reviewed desktop/mobile screenshots. Migration 020 replaced only page 53, preserved prior migration history, and safely skipped a completed retry. A private before-image of original content and custom fields was saved before replacement.

## October 15, 2026 — Leadership verification

PHP lint and native WordPress block validation passed (zero invalid blocks). Checked all seven supplied links and all eight paragraphs. Browser checks at 1440, 1024, 390, and 320px confirmed image loading and no horizontal overflow; visually reviewed desktop/mobile screenshots. Migration 021 updated only existing page 204, retained previous migration history, and skipped its completed retry. Saved a private original-content/metadata before-image.

## October 15, 2026 — Employment verification

PHP lint and native block validation passed (zero invalid blocks). After migration, browser checks at 1440, 1024, 390, and 320px confirmed desktop columns/mobile stacking, all five departments and ten list items, four paragraphs, both HR email links, loaded image, and no overflow. Reviewed desktop/mobile screenshots and corrected department-heading specificity. Migration 022 updated page 186, preserved prior migration history, and skipped a completed retry. Original page and metadata were saved privately before replacement.

## October 15, 2026 — Volunteer verification

PHP lint and native block validation passed, including the Shortcode block (zero invalid blocks). Confirmed Gravity Forms and form ID 1 exist. Browser checks at 1440, 1024, 390, and 320px confirmed both images, responsive columns, no overflow, and the rendered #gform_1 with 24 form controls and no literal shortcode. Visually reviewed desktop/mobile screenshots. No form was submitted. Migration 023 updated page 73, retained prior migration history, and skipped a completed retry; a private original page/metadata before-image was saved.

## October 15, 2026 — History reflow verification

Confirmed exact preservation of all 24 paragraph and ten image blocks, plus idempotent transformation. Migration 024 completed and its retry skipped; prior migration history was unchanged. Native WordPress validation reports zero invalid blocks. Browser checks at 1440, 1024, 390, and 320px confirmed all images load, no overflow, 18px body text, pilot above the plane, and continuous desktop text with 24px paragraph separation. Desktop/mobile screenshots were reviewed against the supplied original-page reference.

## October 15, 2026 — Volunteer column refinement

Verified exact preservation of all heading, paragraph, image, and Shortcode blocks. Migration 025 completed with prior history unchanged and skipped its completed retry. Native block validation passed with zero invalid blocks. At 1440/1024px the form renders in the right column, with the photo under the left-column text. At 390/320px the form follows the photo. All 24 form controls render; no horizontal overflow. No form submission was performed.

## October 16, 2026 — About Donation verification

Native block validation passed with zero invalid blocks. PHP lint passed for the dynamic grid and migration. Migration 026 updated page 383413 and skipped its completed retry with earlier migration history unchanged. Browser checks at 1440, 1024, 768, 390, and 320px confirmed responsive 3/2/1-column layouts, correct story titles, loaded thumbnail, teal inline links, no date blocks, newsletter placeholders, and no overflow. Real AJAX clicks loaded 9 → 18 → 27 unique stories. Error/retry, exhausted state, invalid-cursor rejection, reduced motion, and no-JavaScript next-batch pagination passed. Reviewed desktop/mobile screenshots. Original page/metadata are saved privately before replacement.

## October 16, 2026 — AJAX title regression

After two real Load More clicks, verified every card at counts 9, 18, and 27 has a nonempty title, matching title/Read more destinations, matching accessible summary label, blue color, and 20px font size. Confirmed unique post IDs and no mobile overflow. PHP lint passed.

## October 20, 2026 — Mobile accordion checks

At 390, 768, and 1024px verified all four parent submenus start hidden, label/arrow taps toggle without navigation, chevrons animate a vertical flip, Enter/Space toggle, Escape collapses a submenu then closes the overlay, and reopening resets state. Confirmed native desktop hover navigation at 1440px. Reviewed a mobile screenshot.

## October 20, 2026 — Inline search verification

Verified at 1440, 1275, 1100, 1025, 1024, 768, 390, and 320px: field appears right of its icon, fits the viewport, receives focus, and closes using icon/Escape/outside click or menu close. No search dialog is created and no horizontal page overflow occurs. Confirmed Enter navigates to the native search results with the entered query, and reduced motion disables transitions. PHP/JavaScript syntax checks passed; desktop/mobile screenshots reviewed.

## October 20, 2026 — Bundled-media verification

All eight page previews and updated Home/About pattern defaults passed native WordPress block validation with zero invalid blocks and no static /uploads/ references. Bundled files are hash-checked by migration 027. The migration completed, preserved earlier migration history, and skipped its completed retry. Browser checks confirmed loaded theme-owned static photos on all eight pages. Hero v4 renders at 1440, 1275, 1024, 390, and 320px with cover fit, 50% 100% bottom alignment, and no page overflow; desktop/mobile screenshots reviewed. Original page content and saved template parts were backed up privately before replacement.
