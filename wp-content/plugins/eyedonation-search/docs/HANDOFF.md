# EyeDonation implementation handoff

This is an **unreleased 0.1.0 preview**, not a launch-approved release. Source is installed as separate directories under wp-content/themes/eyedonation and wp-content/plugins/eyedonation-{search,migrations}. Existing Eye-Bank and American Friends theme files were preserved. Following the owner’s explicit request, EyeDonation and both companion plugins are now active on the local site, and all six migrations completed against the existing homepage (ID 386743). A private pre-change database backup was taken outside the deliverable packages.

## Pending owner inputs

- Homepage target approved by the owner: replace existing `eye-bank-home` (386743), preserving its ID and unrelated metadata. Baseline SHA256 `cc3ab7578dd21b75c560a419ee4ccfbd47816d9164d613c4bfc3e05ac2f20976` was reverified unchanged. The packaged page mapping records this approval; remaining destination/content gates still apply.
- Replace the owner-authorized placeholder links with final primary/footer destinations. The mockup’s top-level labels are installed as saved native menus; no submenu labels were invented.
- Replace donor registration, donation, utility, testimonial video and resource-card placeholders with final destinations. Until then they show a clear “Destination pending” page.
- Support photograph supplied and verified at `2026/10/support_our_sight-saving_programs_photo.jpg` (local attachment 387861). Its editable Cover payload is implemented; donation destination and final copy approval remain pending.
- Approve organization identity, homepage factual copy, contact details, social URLs, accreditation caption/destination, copyright wording and reversed branding.
- Decide translation/accessibility integrations separately. No decorative fake controls or payment/registration processing were added.
- Complete legacy business-code review and fresh live-backup rehearsal before switching the live-copy theme.

## Editing

Appearance → Editor manages templates and global styles. Appearance → EyeDonation Site Management links to Header, Footer, Navigation, Templates and Styles. The header uses Site Logo, native saved Navigation and a core Search form. The supplied SVG is a portable display fallback until a Site Logo is selected. Header utility destination remains visibly pending.

Edit homepage sections in Pages, using normal Gutenberg blocks. Unsynced patterns are provided for Hero, Resources, History, Eye Can See Again, Support and News. The Support starter pattern resolves the supplied destination media read-only when uniquely available. Other starter patterns use explicit development image placeholders; the migration assembles the same sections with destination media. Choose actual media and approved links before using a pattern publicly. Edit both desktop and mobile History Image blocks; the theme produces a responsive picture on the public site, retaining native selections in the editor.

News is an independent Query Loop limited to three newest eligible standard posts. Featured image and title are native blocks; Post first paragraph is a theme-owned dynamic block with editor server preview; Read More is a native block. The introduction is the complete first nonempty prose paragraph, skipping captions, headings, media and shortcode syntax. Missing images use a neutral placeholder. Post updates and publication naturally update cards. No posts are bundled or migrated.

Existing users can turn off “Show template” in the post editor. Preferences are not reset. On WordPress 7.1+, newly registered accounts receive an initial content-only preference only where unset. Template selection remains native; use Pages → Quick Edit → Template for Wide Page or Landing Page.

Shared menus are saved wp_navigation entities bound by narrowly managed header/footer overrides. The migration plugin is unnecessary after setup. Inspect saved overrides in the Site Editor before resetting them. A managed override whose shipped source file changes produces a theme-owned administrator warning; use an explicit follow-up migration to update it.

## Search

The permanent plugin has a top-level Search screen: independent enhancement/filter/highlight toggles, 1–50 results per page, exclusions, ordered group mappings and Rebuild public search text. Advanced mappings use labeled JSON with type slugs, taxonomy term IDs and ancestor page IDs. No provisional groups were mapped to fabricated categories. Migration payloads can resolve term slugs and ancestor page paths on the destination.

Indexing reads public title/excerpt/body only, strips markup/shortcode artifacts and never executes shortcodes. No third-party fields or legacy adapters were approved. Rebuilds are coordinated, recoverable 30-record batches through WordPress cron or migration requests. Ensure cron works in production. Search keeps WordPress's main query totals, SQL pagination and public eligibility. Unknown groups fall back to All; out-of-range results redirect to the first page. Disabling enhancement or the plugin retains the native editable search template and working popup.

## Architecture and limitations

The supplied American Friends architecture, popup, results composition and Soroka search implementation were inspected. Client branding, fonts, taxonomy IDs and third-party adapters were not copied. All theme typography uses Arial. Business functionality is not moved into this theme.

The existing Eye-Bank functions.php contains Gravity Forms purchase-order matching, email fields/conditional validation, sender changes, inventory/limits, Toolset user ordering, member/login presentation and other site logic. Switching themes would remove those hooks. Inventory is provided in LOCAL-INVENTORY.json; migration of business behavior to a separately approved permanent plugin remains a launch blocker. Existing Thrive/Toolset-built inner pages must also be rehearsed. This theme does not require or register a Thrive template.

Preview screens, reports and source payloads are reviewable. The migration payload now records the owner’s approval for this local design with portable placeholder destinations. Activation still never seeds/replaces content; changes were explicitly previewed and processed through the runner. Initial screenshots illustrate the synthetic fixture. Updated local-home-desktop.png, local-home-mobile.png and local-search-mobile.png show the actual local site and real latest posts. This local application is not a separate fresh-live-backup rehearsal.
