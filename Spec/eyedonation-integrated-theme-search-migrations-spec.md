# eyedonation.org — Integrated Theme, Search, and Migration Specification

Merged implementation contract • 10 October 2026

## Build order and authority

Read this entire specification before implementation. Build the theme, permanent search plugin, and deployment migration plugin together from the first development round. Every database-dependent feature must have its migration payload and verification implemented alongside the feature. Do not defer migrations until the theme is finished.

This combined document supersedes the two separate source specifications. Preserve the visual, editorial, accessibility, search, and asset requirements below. The migration sections govern intentional database changes; ordinary installation, activation, reactivation, and updates must preserve existing content. The supplied theme specification and migration addendum were merged here; the reference image and American Friends source files must still be supplied or inspected during implementation.

### Theme names and existing themes folder

- **New theme display name: EyeDonation.** Set the WordPress theme header to `Theme Name: EyeDonation`; use `eyedonation` for the new theme directory, slug, and text domain. Preserve this spelling throughout code, documentation, and package names.
- **Current legacy theme: EyeBank.** This is the existing theme being replaced. Inspect its actual installed directory and theme metadata rather than guessing its folder name. Inventory any functionality, settings, or content dependencies that must survive the transition. Build EyeDonation as a separate standalone theme; do not overwrite or rename EyeBank or make EyeDonation its child theme.
- The **`american-friends`** reference theme is also present in the local WordPress themes folder. Inspect it for the architecture and behavior identified in this specification; it is a reference theme, not the theme being replaced. Preserve its files.
- **Themes-folder link/path: to be supplied by the owner.** Once provided, use that location to inspect EyeBank and `american-friends` and to place the new `eyedonation` theme in the intended local WordPress installation. Do not invent an absolute folder path.

### Three separately installable packages

| Package | Responsibility | Needed after deployment? |
| --- | --- | --- |
| `eyedonation` theme | Block templates, parts, patterns, styles, navigation presentation, search popup, and Appearance management links | Yes |
| `eyedonation-search` / EyeDonation Search | Public search indexing, search configuration, grouped results, and native-search fallback integration | Yes, for enhanced search |
| `eyedonation-migrations` / EyeDonation Site Migrations | Explicit, versioned database setup and deployment changes through Tools → Site Migrations | No; completed work must survive deactivation |

The migration plugin must not provide runtime blocks, frontend filters, navigation rendering, or search functionality. Give the two plugins separate namespaces, option keys, metadata prefixes, and ZIP packages. Suggested migration namespace: `EyeDonation\SiteMigrations`.

### Implementation phases

1. Inspect repository instructions, installed WordPress/PHP versions, current live-copy content, plugin dependencies, the supplied mockup, and available American Friends references. Inventory existing pages, navigation, Site Editor overrides, and relevant search taxonomy. Record unresolved owner inputs.
2. Build the migration runner, ownership/resolver helpers, preview/preflight screen, tracking, and security foundations alongside the theme shell. Establish versioned release compatibility for all three packages.
3. Implement header/footer and native navigation together with their explicit setup/binding migrations. Implement editable homepage patterns and the approved homepage-content migration together.
4. Implement search popup and the permanent search plugin together with any approved search-settings migration and subsequent index rebuild.
5. Rehearse the complete package set against a fresh live-site copy, including existing editor customizations and different destination IDs. Fix conflicts before packaging.
6. Deliver all three ZIPs, source, migration inventory, owner editing guide, asset checklist, test evidence, and the final cutover checklist.

Do not perform actual production deployment while implementing this specification.

## Brief for Codex

Build a new, standalone WordPress block theme from scratch for eyedonation.org. Use the American Friends of Soroka theme as an architectural reference: modular PHP, HTML block templates, shared editable template parts, a centralized `theme.json`, native Gutenberg navigation, and a convenient Appearance management screen. Do not clone its branding, menu taxonomy, client-specific content, integrations, or migration code. Build the project-specific migration plugin described here independently.

The supplied **Draft 3.png** Eye-Bank of New York mockup is the visual reference for the **header, footer, and homepage**. The project domain is eyedonation.org; the mockup displays Eye-Bank branding. Treat organization names, external URLs, contact details, and exact menu destinations as provisional until the owner confirms them. The owner will provide logos, accreditation graphics, and other assets during implementation. Do not extract final assets from the screenshot or reuse Soroka assets.

Build the homepage in the first implementation round, following the supplied mockup and the American Friends approach of storing editable Gutenberg blocks in page content. The owner will provide the individual images, logos, video details, and other assets to the implementing AI as implementation proceeds. Use the supplied homepage asset manifest below now; use clearly labeled temporary placeholders only for assets still missing.

## Core requirements

- Implement a genuine full site editing block theme with `templates/*.html`, `parts/*.html`, and `theme.json`.

- Build all future page content, starter layouts, and shared site elements with standard WordPress Gutenberg blocks wherever possible. Clients must be able to edit text, images, links, buttons, menus, and layouts through WordPress.

- Use core Group, Columns, Site Logo, Site Title where appropriate, Navigation, Navigation Submenu, Search, Social Icons, Image, Heading, Paragraph, Buttons, Post Content, Post Title, Query, and pagination blocks.

- Do not require Thrive, Elementor, another page builder, or a proprietary theme framework. Do not include either Thrive template, their registrations, styles, conditionals, or compatibility code.

- Keep business functionality outside the theme. Donation processing, donor registration, translation, accessibility services, and any future custom post types belong in separately approved plugins or services.

- Avoid hardcoded page content in PHP, large Custom HTML blocks, or manually edited PHP menu arrays. Use CSS and small JavaScript enhancements for presentation and interaction.

## Header: visual structure and editing

Reproduce the mockup's two horizontal zones:

1. A white branding band with generous vertical space, a centered organization logo, and an outlined **EyeCanSeeAgain.org** utility button toward the right on desktop. Use a Site Logo block linked to the site's home URL and an editable core Button for the utility link. Confirm the external destination before launch.

2. A full-width deep blue navigation band beneath it. Center the navigation within the site's maximum width and include a search control at the right. Use compact white labels and restrained dropdown indicators as shown in the reference.

Starter top-level labels from the mockup:

- About The Eye-Bank

- Become a Donor

- Volunteer

- Give Now

- More

These labels are initial editable defaults, not a requirement to create corresponding pages automatically. Do not invent submenu labels or URLs: request the owner's sitemap during implementation. Use core Navigation Submenu blocks for normal dropdowns. Do not import American Friends' About/News/Projects mega menus. Add richer shared menu panels only if the owner explicitly requests them; their contents must remain Gutenberg-editable.

Use a real saved core Navigation entity so labels, order, nesting, and destinations are editable in the native editor. Resolve the menu reference through WordPress on the target installation; never ship environment-specific numeric navigation IDs. If initialization is needed, make it repeatable without creating duplicate menus or overwriting existing edits. Use the explicit, narrowly scoped binding strategy below. A managed header/footer database override is permitted only when required to bind saved navigation, and must have ownership tracking, conflict checks, and an update path when the underlying theme file changes. Do not create unrelated template-part copies.

Implement the same full-screen search popup and enhanced results experience as American Friends, adapted to EyeDonation/Eye-Bank branding and Arial. See the required search implementation below. Keep the native WordPress GET search form and a working fallback.

On mobile, stack or reposition the branding and utility link without overlap. Use a labeled menu toggle and readable touch targets. Prefer core Navigation's responsive overlay when it meets the design; any custom behavior must retain accessible submenu controls, focus handling, and keyboard operation. Do not assume hover is available. Sticky behavior is optional and requires owner approval during implementation.

## Footer: visual structure and editing

Create an editable shared Footer template part with a deep blue background and white text. Match the mockup's desktop arrangement using core Columns and Groups:

| Region | Initial contents |
| --- | --- |
| Brand and social | White/reversed logo; Instagram, LinkedIn, and Facebook core Social Icons. Owner supplies logo and URLs. |
| Contact | Organization name, street address, city/state/postcode, and clickable phone number. Owner confirms final text. |
| Footer navigation | About Us; Careers; Researchers; Surgeons; Transplant Recipients & Donor Families; Terms and Conditions; Privacy Policy. All links editable; owner confirms destinations. |
| Accreditation | Owner-supplied EBAA accreditation graphic, appropriate alternative text, and editable supporting caption if required. Confirm authorization and destination before use. |

Add a small copyright line along the bottom. If the year is dynamic, use a narrowly scoped rendering enhancement that keeps surrounding wording editable and works consistently in previews. Include the agency credit only if the owner confirms it.

The mockup also shows an accessibility symbol and an EN language control. Treat these as integration placeholders in the specification, not functional features supplied by the theme. Confirm whether a service or translation plugin is desired. Do not create a decorative control that pretends to work, or claim that an accessibility widget makes the site accessible.

Keep footer content readable despite its compact appearance in the screenshot. Stack columns logically on small screens, preserve adequate contrast, and use accessible link sizes.

## Homepage: first-round implementation

Build the homepage shown in Draft 3.png using editable Gutenberg page content, as in American Friends. Use core blocks and reusable unsynced section patterns; shared header/footer remain template parts. The owner will provide individual images, logos, final copy, video assets, and destinations to the AI during implementation. Do not flatten the screenshot into a background image or hardcode these sections in PHP.

### Supplied homepage media and deployment mapping

The owner has uploaded the following homepage reference/source assets to the local WordPress media folder and plans to upload the same files to the live site before deployment. The intended relative upload directory on both sites is `2026/10/`. The local URLs below identify the supplied files; they are development references only and must not be embedded as deployment URLs. Live upload completion and exact destination paths must be verified, not assumed.

| Stable asset key | Homepage use | Supplied local reference URL |
| --- | --- | --- |
| `homepage_hero` | Main hero photograph | `https://Eyebank:8890/wp-content/uploads/2026/10/main_hero_photo.jpg` |
| `donate_life_logo` | Donate Life logo in hero registration callout | `https://Eyebank:8890/wp-content/uploads/2026/10/donate_life_logo.png` |
| `resources_recipients_families` | Transplant Recipients & Donor Families card | `https://Eyebank:8890/wp-content/uploads/2026/10/transplant_recipients_donor_families.jpg` |
| `resources_research_portal` | Research Portal card | `https://Eyebank:8890/wp-content/uploads/2026/10/research_portal_photo.jpg` |
| `resources_transplant_portal` | Transplant Portal card | `https://Eyebank:8890/wp-content/uploads/2026/10/transplant_portal.jpg` |
| `history_desktop` | World’s first eye bank, desktop photograph | `https://Eyebank:8890/wp-content/uploads/2026/10/worlds_first_eye_bank_photo_desktop.jpg` |
| `history_mobile` | World’s first eye bank, mobile photograph | `https://Eyebank:8890/wp-content/uploads/2026/10/worlds_first_eye_bank_photo_mobile.jpg` |
| `eye_can_see_again` | Eye Can See Again section image/poster; inspect supplied image for placement | `https://Eyebank:8890/wp-content/uploads/2026/10/eye-can-see-again.jpg` |

**Implementation and migration requirements:**

- Maintain these stable keys in the asset manifest, with each expected upload-relative path beginning `2026/10/`. Use the actual supplied media rather than screenshot crops or replacements. This list does not supply every remaining asset, such as the Support section photograph or testimonial video URL; retain the existing missing-asset workflow for those.
- Before writing homepage content, resolve each asset to the destination Media Library attachment by its exact upload-relative original-file path and confirm a unique valid image attachment and an available file. Matching paths do not imply matching numeric attachment IDs. Derive image URLs, responsive variants, and block attachment IDs from the destination’s WordPress media APIs and configuration. Support WordPress-generated image variants without confusing them with separate assets.
- Preview Changes must show each asset key, expected path, resolved destination attachment ID/URL, and any missing or ambiguous match. If WordPress renamed an upload, added a suffix, used another date directory, or the file exists on disk without a Media Library attachment, require an explicit corrected mapping or approved media registration/import step. Do not silently guess by filename, create duplicate attachments, or overwrite existing files. Once mapped, reruns reuse the same destination attachment.
- Record source checksums during development where available and use them to flag unexpected file differences for review. The owner’s plan is to provide matching files on live; do not automatically replace a live image because it differs.
- Use the desktop and mobile history images responsively, with appropriate source selection and an accessible fallback. Keep their media selections editable in the supported homepage editing workflow; inspect dimensions before choosing breakpoints or crops.
- The theme ZIP and migration plugin must not rely on fetching `https://Eyebank:8890` during deployment. The migration uses destination media already uploaded and verified. No production content, block markup, CSS, image links, or stored settings may retain that local hostname or port.
- Test asset resolution on the fresh clone even when attachment IDs differ from the development copy. Confirm all eight assets resolve, the history image switches appropriately on mobile, and the deployed homepage uses destination URLs with no duplicate media imports. Missing required media mappings block the homepage migration before it mutates the page.

### Homepage sections

Implement these sections in order:

1. **Hero:** full-width family photograph with an editable donor-registration callout at the lower right. The mockup shows previous/next arrows: support owner-supplied slides through editable core Cover/Group blocks with a small progressive enhancement, not a required page-builder plugin. If only one slide is provided, hide arrows. Provide labeled controls, keyboard operation, reduced-motion support, and a usable first slide without JavaScript. Avoid autoplay by default.

2. **Resources:** bold blue heading and introductory copy, followed by three image-link cards for Transplant Recipients & Donor Families, Research Portal, and Transplant Portal. Use Columns/Groups, Images or Covers, headings, and editable links. Overlay captions must remain readable and accessible.

3. **History:** wide archival image with a tinted presentation and an inset white copy panel containing “The world’s first eye bank,” supporting text, and an About Us button. All text and media editable; confirm factual claims with final owner copy.

4. **Eye Can See Again:** two-column section with heading, descriptive copy, and outlined external button on the left; testimonial image/video poster, quotation, and teal video button on the right. Owner supplies video URL and assets. Use a normal link or approved core embed, rather than inventing a video integration.

5. **Support:** wide family photograph with a white inset panel, bold cyan heading, supporting copy, and a Give Now button. Owner supplies donation destination; no payment processing in the theme.

6. **Eye-Bank News:** a dynamic, three-column display of the Eye-Bank’s **latest three published WordPress posts**, styled to match the news section in Draft 3.png. This is live post content, not three manually maintained homepage cards.

### Eye-Bank News: required dynamic behavior

- Use an editable core Query Loop with a Post Template, an explicit independent homepage query, a limit of three posts, and publication date descending. Ignore sticky-post promotion so the newest posts always appear first. Use standard posts by default, with no category restriction unless the owner explicitly requests one. Exclude drafts, scheduled, private, and password-protected posts. If fewer than three eligible posts exist, show only those available; do not duplicate or fabricate records.
- Each card must show, in order: **a thumbnail-sized version of the post’s featured image, its linked post title, the first paragraph from the post body, and a “Read More” link to that post’s destination permalink**. The image should also link to the post. Resolve every image and permalink from the current site, with no development-domain URLs or hardcoded post IDs.
- Match Draft 3.png: three equal-width columns on desktop, landscape image thumbnails at a consistent aspect ratio, blue bold titles, compact dark introductory text, and the Read More link following the introduction. Use responsive WordPress image sizes and srcset suitable for the displayed card dimensions, rather than stretching a tiny thumbnail. Preserve meaningful image alt text and sensible cropping. Stack cards on small screens and allow text to grow without clipping or fixed card heights. If a post lacks a featured image, use a consistent neutral placeholder without inventing an image from the article body.
- **The introductory text must come from the first nonempty prose paragraph in the actual post content**, not a manually entered excerpt, a generic word-count excerpt, or text copied from the design. Preserve the complete paragraph by default; do not silently truncate it to match the screenshot. Skip leading headings, images, captions, embeds, scripts, styles, and shortcode syntax. Support paragraph blocks nested in ordinary content containers and legacy HTML paragraphs. Inspect the actual existing posts during implementation and explicitly accommodate their stored content format without executing arbitrary shortcodes or rendering the full post inside a card. Output safely sanitized text. If there is no usable paragraph, display the image/title and Read More without fabricated introductory copy.
- Keep the Query Loop, featured image, title, and Read More as native blocks where practical. Because the exact first-paragraph requirement differs from a normal excerpt, implement a narrowly scoped, theme-owned server-rendered first-paragraph block using the current Post Template post context. Register its renderer and editor preview with the theme. Do not globally change post excerpts or make this permanent homepage feature depend on the temporary migration plugin or search plugin.
- The design uses real existing posts and their real images/text. Use those existing records when validating the layout on the local clone. They are visual references, not a fixed selection: newer published posts must automatically replace older cards. Never pin the screenshot’s three posts, recreate them, or import their old development copies during deployment.
- The homepage migration installs the query/block structure and styling configuration only. It must not snapshot article text/images into homepage content, overwrite posts, change their dates, or migrate a static list of post IDs. On the fresh destination clone, the section immediately pulls that database’s latest eligible posts. Any caching must refresh when publication status, dates, body text, titles, or featured images change.
- Verify with real posts that all three images, titles, first paragraphs, and Read More destinations match their source records; that a newly published post enters the first slot and pushes the oldest out; and that editing a displayed post updates its card. Verify fewer-than-three posts, missing images/paragraphs, mobile stacking, and operation with both companion plugins disabled.


Match the screenshot's overall proportions, alternating full-width imagery and white content sections, deep blue headings, cyan accents, and mostly square edges. Use Arial throughout, with bold headings. On mobile, stack multi-column sections, keep overlay copy readable, and avoid fixed heights that crop text. Preserve image focal-point controls and meaningful alt text. Build a single clear H1 into homepage content and use H2 headings for sections.

## Search: required theme popup and companion plugin

Recreate the American Friends experience, rather than implementing a generic replacement. Deliver a separately installable **EyeDonation Search** plugin alongside the theme, with its own namespace, option keys, metadata prefix, and installable ZIP. The plugin is a required first-round deliverable; the theme must still function if it is disabled.

### Existing implementation references

When the American Friends workspace is available, inspect these sources before implementing:

- `wp-content/themes/american-friends/assets/js/header.js` — full-screen native dialog and core search-form enhancement.

- `wp-content/themes/american-friends/assets/css/search.css` — popup presentation.

- `wp-content/themes/american-friends/templates/search.html` and `assets/css/search-results.css` — enhanced results integration and styles.

- `wp-content/themes/american-friends/docs/SEARCH.md`.

- `wp-content/plugins/soroka-search/README.md`, `soroka-search.php`, `includes/settings.php`, `includes/documents.php`, `includes/query.php`, `includes/render.php`, and `tests/integration.php`.

Port the reusable behavior under the new project identity. Do not copy Soroka taxonomy IDs, page IDs, labels, fonts, or legacy Thrive/team/events/redirect adapters unless the new installation actually needs an approved equivalent. If the source files are unavailable to the implementing AI, request them from the owner and use the behavioral requirements below as the implementation contract.

### Header search popup

- Desktop and mobile search icons open the same full-screen white native `dialog`, with prominent Arial Bold title, brief editable/localized brand-appropriate description, large input, blue submit control, and upper-right Close control.

- Enhance the existing core Search form in the shared header. Retain its WordPress action, GET method, `s` input, label, and submit behavior; do not replace it with a remote service or AJAX-only search.

- Focus the input on opening; contain keyboard focus; support Escape and explicit close; restore focus to the visible opener and restore previous scroll styles. Expose dialog labels and trigger expanded state. Coordinate with mobile navigation so overlapping overlays do not remain open.

- Without JavaScript or native dialog support, preserve a visible, usable ordinary search form. Respect reduced motion and test logged-in/admin-bar layouts.

- Search submission goes to the separate search results page, not an inline popup results list. Keep popup functionality independent of plugin activation.

### Results page and filters

Reproduce American Friends' results composition with the new branding: shared header/footer, a Search results heading, submitted query text, large search-again form, optional category/group filter links, real result range/total, text-focused result list, and numbered Previous/Next pagination. Each result has a group/type label, linked title with directional affordance, and a clean contextual excerpt. Highlight matching terms safely when enabled. Use Arial and the new palette; preserve the interaction and information hierarchy.

Keep an All results filter and configurable Eye-Bank groups. Proposed labels for owner review are **News**, **Donor & Recipient Resources**, **Research & Professional Resources**, **Events**, and **Pages**. These are search group labels, not instructions to fabricate WordPress categories. Map them to actual registered post types, real taxonomy terms, or explicitly selected ancestor pages after inspecting the target content. Resolve IDs locally. Hide unavailable/irrelevant groups; generic page mappings must not override more specific term or ancestor mappings. Support changing group labels, order, enabled state, and mappings in admin.

Retain the source behavior for empty queries, no matches, empty selected groups, unknown groups, and out-of-range pagination. Filter changes reset pagination; pagination preserves query/group; new searches reset the group. Compute totals from eligible results, never from mockup numbers. Forms, filters, and pagination work without JavaScript.

### Plugin administration and indexing

Provide a top-level **Search** admin screen, with capability checks, nonces, validation, and clear feedback. Include independent toggles for enhanced search, filters, and highlighting; results per page (default 6, bounded 1–50); excluded post types; excluded post IDs; group mapping controls; and **Rebuild public search text**. Preserve saved settings on reactivation. Missing IDs/types/terms should produce clear feedback without silently changing owner choices.

Use clean derived search documents for approved public content, rebuilt on activation, relevant saves, taxonomy changes, and explicit rebuilds. Index title, excerpt, and readable Gutenberg body text; remove scripts, styles, block delimiters, markup artifacts, and shortcode syntax. Do not execute arbitrary shortcodes or content filters during indexing or search all post metadata. Add third-party field/block adapters only after inspecting actual content and approving public searchable fields. Batch rebuilds; invalidate affected query caches.

Preserve the source's main WordPress query ownership of eligibility, totals, sorting, and SQL pagination. Search only published, non-password-protected, publicly searchable records with genuine public destinations, for logged-in users and visitors alike. Exclusions apply before totals/pagination. Keep source-style normalized bounded queries, multi-term AND behavior, quoted terms and minus-term exclusion, title relevance, stable tie-breaking, and contextual snippets of approximately 45 words. Escape output before inserting controlled highlight markup. Do not add query logging or an external search service.

### Theme integration, fallback, and verification

Keep `search.html` as an editable block template containing a complete native search/Query-loop fallback inside a uniquely prefixed search-experience main wrapper. When enabled, a scoped plugin rendering hook replaces that wrapper before its fallback Query executes. Preserve the shared header/footer and a single main landmark. Load enhanced result CSS only when needed; avoid a custom-block dependency that leaves missing-block errors when deactivated.

Deliver installation and rollback instructions for theme plus plugin. Detect/document saved Search template overrides; do not silently delete them. Disabling enhancement or deactivating the plugin returns to native search without deleting content or settings.

Adapt the source integration tests to verify eligibility/password protection, exclusions, mappings/precedence, real totals across pagination, Unicode/multi-term queries, clean indexing, safe highlights, query-cache updates, admin permissions/nonces, and fallback. Do not carry source test counts over as evidence. Browser-check popup/focus/close, mobile layout, no-JavaScript operation, filters, pagination, blank/no-match queries, and plugin deactivation. Record actual test results.

## Templates

Use a small, clearly named starter set:

| File | Purpose |
| --- | --- |
| `index.html` | Required general fallback with header, main content/query as appropriate, and footer. |
| `front-page.html` | Homepage shell: header, a main landmark containing Post Content, and footer. No visible template-supplied page title; homepage sections belong in editable page content. |
| `page.html` | Default page: header, one visible page title, constrained Post Content, footer. |
| `page-wide.html` | Custom Wide Page: header, page title, wider content area, footer. |
| `page-landing.html` | Custom Landing Page: header, Post Content, footer; no template-supplied title, allowing the owner to build the opening section. |
| `single.html` | Standard post with title, appropriate editable post metadata, content, and footer. |
| `home.html` | Posts index using a core Query loop and pagination. |
| `archive.html` | Archive title, core Query loop, and pagination. |
| `search.html` | Search context, search form, results Query loop, pagination, and a useful no-results state. |
| `404.html` | Simple missing-page message, home link, and native search. |

Register only Wide Page and Landing Page as custom page templates in `theme.json`, with clear client-facing names. Do not register standard hierarchy templates as additional custom choices. No Thrive templates, client-specific About/Giving/Signup templates, or unnecessary blank variants.

Every applicable template must share the same header/footer parts. Main landmarks and skip-link targets must be consistent. Keep page body content in Post Content, never embed the header/footer or navigation inside individual pages. Avoid duplicate visible H1 headings.

## Client editing and Appearance management

Add a lightweight **Appearance → EyeDonation Site Management** screen for users with the appropriate WordPress theme-editing capability. It should link directly to native editors for:

- Header and utility link.

- Primary navigation.

- Footer and footer navigation.

- Templates and global styles.

- Any additional shared menu parts explicitly approved later.

This is a convenient directory of native editing tools, not a parallel custom settings system. Use capability checks and escaped output; verify links against the installed WordPress version.

For everyday page/post editing, default to the native content-only editing view where supported. The American Friends refinement separates page content editing from shared header/footer editing; preserve that experience. Users must retain the option to enable Show template. Do not forcibly reset preferences on every load or hide the entire site shell with editor CSS. Confirm the preference mechanism against the target WordPress version. Document setup for existing accounts and initialize new-account defaults only when safe and compatible.

Keep template selection simple through the small template set and clear names. Document Pages → Quick Edit → Template as a compact selection workflow if available on the target installation. Do not patch WordPress core to remove preview cards. A custom names-only picker is optional future work, not required for this theme.

Use minimal structural locks only where they protect the shared shell. Keep meaningful content editable, and explain how authorized users can change structure. New reusable layouts must use editable core-block patterns. Provide editable homepage section patterns and assemble them into the homepage on the owner-authorized initial setup; subsequent activation must preserve page edits.

## Implementation organization

Suggested theme structure (the two plugins are separate sibling packages under wp-content/plugins):

```text

eyedonation/
  style.css
  theme.json
  functions.php
  screenshot.png
  readme.txt
  inc/
    setup.php
    assets.php
    navigation.php
    editor.php
    block-styles.php
  templates/
  parts/
    header.html
    footer.html
  patterns/                 # Editable homepage sections and approved generic patterns
  assets/
    css/
      base.css
      header.css
      footer.css
      search.css
    js/                     # Only necessary enhancements
    images/
  docs/
    HANDOFF.md
    EDITOR-WORKFLOW.md
    DEPLOYMENT.md
    VERIFICATION.md
```

Use a short, unique project prefix and matching text domain. Keep `functions.php` as a small module loader. Enqueue assets through WordPress hooks, with file-based versioning where suitable. Load editor styling so content typography and spacing resemble the public site without bringing unwanted frontend interactions into the editor.

Centralize palette, typography, content/wide widths, spacing, and supported controls in `theme.json`. Start with the reference's deep blue, white, and teal/cyan accents; confirm exact brand values with the supplied assets. Keep a concise set of understandable presets. **Use Arial throughout**, with `Arial, Helvetica, sans-serif` as the fallback stack. Apply it to page copy, headings, menus, buttons, forms, footer, search popup/results, and editor content. Headings use Arial Bold (`font-weight: 700`); body copy generally uses 400. Do not carry over Lato or load web fonts.

Resolve theme asset and site URLs through WordPress APIs. No localhost URLs, production-domain assumptions, Soroka names, copied menu IDs, or imported database records. Use clearly identified temporary asset placeholders during development; list missing assets in the handoff and replace them before launch.

## Database behavior and deployment

Theme files define portable initial templates, parts, patterns, and design defaults. Page content, saved navigation, Site Editor overrides, and selected settings require database operations. Explain this distinction in the owner handoff.

- Installing, activating, reactivating, or updating the theme or migration plugin must not seed or overwrite content. An administrator initiates changes through **Preview Changes → Process Migrations**.
- Build initial homepage content from the editable section patterns. On a clean installation, create the owner-approved page only through explicit setup. On an existing-site clone, identify the exact owner-approved homepage and preview a scoped replacement of its content against an expected prior fingerprint. Existing content replacement is allowed only when explicitly authorized for that page. Preserve its ID, unrelated metadata, comments, and other pages. If its content has changed since the approved baseline, stop for review.
- Set `show_on_front`, `page_on_front`, `page_for_posts`, and template assignments only when explicitly included in the approved migration. Do not change them automatically on activation.
- Menus and mappings use resolved destination IDs. No broad content imports, automatic migration of legacy menus, or repeated seeding.
- Keep business functionality and transaction data outside migration payloads. Do not import registrations, user records, or production submissions with the design.
- Package clean ZIPs without secrets, caches, source-site database dumps, or unrelated files.
- Document deliberate removal of obsolete files during deployment. For template removal, inspect page assignments and saved overrides before removing its file, customTemplates registration, and related code.
- File updates preserve owner edits. Any intended replacement of a saved override must be an explicitly previewed, narrowly scoped migration.

### Project-specific migration plan

These are proposed migration responsibilities. Final payloads depend on actual owner-approved content and destination inventory; do not seed sample content as production content.

| Suggested migration | Required behavior |
| --- | --- |
| `001_create_approved_navigation` | Create/adopt primary and footer navigation only from confirmed labels, hierarchy, and destinations. Resolve destination pages and external links; do not create pages from labels. |
| `002_bind_shared_navigation` | Bind saved navigation to the exact header/footer Navigation blocks and verify editor/frontend consistency. |
| `003_apply_homepage_content` | Assemble approved unsynced section patterns into the identified homepage using core blocks and resolved assets. Preserve the live page identity; block unapproved replacements. |
| `004_apply_approved_page_settings` | Set approved homepage/posts-page selections, page-template assignments, and supplied logo/media settings. |
| `005_configure_search` | Apply only approved EyeDonation Search options and group mappings through its public settings service; resolve terms, post types, and ancestor pages on the destination. |
| `006_verify_and_rebuild_search` | Request the search plugin's supported batched rebuild, wait for verified completion, and report status before marking complete. Derive the index from current destination content. |

Split or reorder these migrations if actual dependencies require it. Store logical dependencies explicitly rather than relying only on filenames. Add later numbered migrations for later released changes.

### Navigation binding decision

Use real saved core Navigation entities. Mark intended header/footer Navigation blocks with stable project-specific identity, such as a documented class/metadata marker that survives block parsing. Never replace every Navigation block indiscriminately.

For this project, the default binding method is a **managed database template-part override** derived from the shipped header/footer file, with only the intended Navigation reference bound to the destination ID. This is a deliberate exception to avoiding unnecessary overrides. Use supported WordPress block/template APIs and the proper theme association for the installed version. It must appear correctly in both native editors and the frontend with the migration plugin disabled.

If an existing override is present, do not overwrite it blindly. Adopt/patch only the exact approved block after preview and expected-state checks; preserve unrelated blocks. If ambiguous, block and request an explicit resolution. Track the source-file fingerprint and the last applied managed fields. A later theme-file change must trigger an override warning and, where needed, an explicit migration; never silently hide new theme changes behind a stale override.

The theme's Appearance screen resolves saved menus and native editor links independently of the migration plugin. On a brand-new installation before migrations are run, preserve usable native editing and avoid displaying an arbitrary fallback menu as though it were configured navigation.

### Search plugin boundary

The existing requirement to rebuild derived public search text on search-plugin activation remains valid: it is internal index maintenance, not permission to seed menus, change theme settings, or overwrite saved search configuration. Preserve existing search settings on reactivation. Provide a supported settings/rebuild service in the search plugin for both its admin screen and the migration plugin to call. Migrations must not edit private search implementation tables directly or copy the development site's derived index.

Activation rebuilds, migration-triggered rebuilds, and manual rebuilds must coordinate rather than run competing rebuild jobs. Report completion/errors and invalidate appropriate caches. The migration plugin must block a search-dependent step if the required search plugin/version is unavailable.

### Owner inputs, placeholders, and readiness

Missing assets may use visibly labeled development placeholders so the shell can be built. Missing required menu destinations, donation links, or page mappings block their corresponding migration instead of inserting guessed links. Clearly distinguish an intentionally incomplete local preview from a release-ready build. Launch verification must reject unresolved required placeholders or mappings.

User-specific editor preferences remain opt-in/version-compatible setup in the Client editing section; do not bulk overwrite user metadata through the migration plugin. Existing account preferences are preserved.

## Validation and acceptance criteria

Implement against the target site's current supported WordPress/PHP versions and record the versions actually tested. Verify using a clean installation and an installation with saved editor changes.

- Theme activates without PHP errors or block validation warnings.

- Header and footer resemble the supplied reference at desktop, tablet, and mobile widths; homepage sections match the reference using owner-supplied assets.

- Owner can edit logo, utility button, menu labels/URLs/order/submenus, social links, contact information, and footer links through native Gutenberg tools.

- Navigation changes appear consistently in the editor and frontend; reloads or theme reactivation do not duplicate or overwrite menus.

- A test page built from core blocks remains editable, and the generic templates render it correctly without duplicate titles or site parts.

- Homepage sections are implemented in editable page content between shared parts; changing text, images, links, and section order requires no PHP edits.

- Native search, archives, pagination, no-results state, and 404 work.

- Keyboard navigation, visible focus, mobile menu operation, search focus behavior, skip links, contrast, and reduced-motion behavior are checked. Validate accessibility in the underlying implementation.

- No horizontal overflow, broken asset URLs, console errors, unresolved launch placeholders, Thrive dependencies, or American Friends branding remain.

- Existing site edits survive a theme update. Document how to inspect/reset a saved template override and how obsolete files are removed.

- Deliver theme, permanent search plugin, and companion migration plugin source, three separate installable ZIPs, screenshots of the implemented homepage, header/footer, search popup, and results at representative widths, editing instructions, asset checklist, and a concise verification report.

## Owner inputs during implementation

The owner will provide logos and other visual assets. Request final brand name, brand colors, primary sitemap and submenu destinations, utility URL, footer contact details, social URLs, approved accreditation assets, homepage photos and copy, testimonial video/poster, donor registration and donation destinations, and decisions on language/accessibility integrations. Begin the theme shell with explicit temporary placeholders where these are missing. Do not infer a final site identity or factual contact details from the mockup.

## Migration plugin: detailed implementation contract

The project-specific decisions above specialize the following requirements. Implement this contract alongside every relevant theme/search feature.

### Scope boundaries

Supported migration targets, when explicitly required by the main specification:

- Block Navigation menus and submenu hierarchy.
- Connections between navigation and header/footer template parts.
- Specific page content, page-template assignments, and homepage/posts-page selections.
- Specific options and plugin settings through supported APIs.
- Synced patterns and referenced media needed by the design.
- Explicitly scoped cleanup of conflicting Site Editor overrides.

Do not copy full database tables or implement a general database synchronization engine. Do not bundle production SQL dumps, user accounts, registrations, submissions, passwords, credentials, or unrelated content. Do not replace whole wp_options, wp_posts, or wp_postmeta tables. Do not perform database-wide URL replacement. Use the actual table prefix, never assume `wp_`.

Keep templates, template parts, global design defaults, CSS, and JavaScript in theme files whenever possible. If local Site Editor work is used, save/export the intended changes into those files before packaging. A theme ZIP alone does not capture all database-resident content.

### Admin interface

Plugin activation may initialize its own tracking storage only. Never execute content migrations on activation, updates, frontend requests, or ordinary admin visits. An administrator must explicitly initiate execution.

Add **Tools → Site Migrations**. Keep the interface clear and usable with keyboard navigation and accessible status announcements.

Show:

- Current site URL and WordPress environment type, target project, active theme/version, plugin release, and compatibility status.
- Each migration's ID, plain-English purpose, prerequisites, status, and completion timestamp.
- Statuses: pending, running, completed, failed, blocked; interruption state must be distinguishable from an actively running request.
- Actions: **Preview Changes**, **Process Migrations**, **Resume / Retry**, **Download Report**.
- Progress by migration and operation, plus a concise final summary.

Preview must list creates, updates, already-satisfied operations, conflicts, and blocked operations. Show exact fields/settings affected and meaningful before/after summaries without exposing unrelated private data. Preview must not create/update target records or mark migrations completed; storing plugin-owned preview metadata is allowed.

Require a successful preflight and a backup acknowledgment before processing. Label this honestly as an administrator acknowledgment, not verification that a backup exists. Do not add a generic force-overwrite or reset-all-history button.

### Versioned migration registry

Register bundled migration classes using an explicit allowlist. Never execute arbitrary uploaded PHP, eval, or code specified in a request.

Each migration declares:

- Unique immutable ID, e.g. `001_create_primary_navigation`.
- Description, dependencies, compatible theme slug/version range, and any required plugin/version.
- Packaged payload and checksum covering both migration code and payload.
- Target logical keys, managed fields, preconditions, planned operations, and postconditions.

Suggested structure:

    eyedonation-migrations/
      eyedonation-migrations.php
      src/Admin/
      src/Runner/
      src/Storage/
      src/Resolvers/
      src/Operations/
      migrations/
      payloads/
      tests/
      README.md
      CHANGELOG.md

Use numbered migrations in dependency order. Once included in a released deployment, do not rewrite completed migrations to implement new changes. Add a new migration. If a completed migration's packaged checksum changes, block execution and explain the mismatch rather than silently rerunning it.

Persist per-migration and per-operation status, checksum, timestamps, actor ID, run ID, checkpoints, destination ID mappings, and summarized results. Plugin-owned storage must not autoload large payloads or logs. Use small options or dedicated tables as appropriate; justify the choice and keep it maintainable. Preserve history on deactivation and uninstall by default.

### Repeatability and conflict policy

Idempotent means that repeating or retrying an operation does not create duplicates or damage existing content.

- Give managed objects stable project-specific logical keys, e.g. `header.primary-navigation`.
- Locate existing objects by a saved mapping plus validated ownership metadata. Where no mapping exists, use an explicitly specified unique identity such as full page path plus post type.
- Never match solely by display title. Stop when resolution is missing or ambiguous unless the approved migration explicitly creates that missing object.
- Do not copy source numeric IDs. Resolve pages, menus, media, terms, and patterns on the destination.
- Derive internal links from destination permalinks and URL APIs. Preserve intentional external URLs.
- Modify only declared fields. A menu update must not overwrite unrelated page content or plugin settings.
- When adopting an existing object, require a declared mapping and show that adoption in preview; do not silently claim ownership.
- Compare managed fields with expected prior values or the last successfully applied fingerprint. If an editor changed them independently, block that operation and explain the conflict.
- Revalidate immediately before each write. If the state differs from the preview, stop and require a fresh preview.
- An already-satisfied operation is a verified no-op, not an unconditional success.

Checkpoint each verified operation. Account for a crash between creating an object and storing the checkpoint: retry must reconcile using stable identity and ownership, not create a second object. Completed migrations are skipped; missing or drifted results are reported for repair by an explicit new migration, never automatically overwritten.

### WordPress implementation rules

Use WordPress APIs for posts, metadata, terms, options, media, and block parsing/serialization. Use `WP_Error` handling and verify results. An API returning false can mean no change, so confirm intended state rather than treating every false return as a write failure.

Use prepared SQL only where necessary for plugin-owned tracking/locking or a specifically justified schema operation. Raw SQL is not the default for moving WordPress content. Do not edit serialized data with plain string replacement.

Provide shared helpers for resolving identities, applying scoped updates, comparing fingerprints, recording checkpoints, and generating reports. Preview and execution must use the same operation definitions so they cannot silently diverge.

### Block themes, navigation, and Site Editor overrides

Block menus use `wp_navigation` content. Classic menu-location assignment APIs alone do not connect a block theme's Navigation block.

For each required menu:

1. Resolve destination pages and intentional custom URLs.
2. Build valid Navigation and Navigation Submenu block markup using WordPress block APIs.
3. Create/update the managed navigation post and record its destination ID.
4. Connect the intended Navigation block to that destination ID.
5. Verify the correct menu renders, including nested items, in the actual theme.

Do not ship local navigation `ref` IDs in portable theme templates. Define and implement an explicit binding strategy. The selected project strategy is to derive a narrowly scoped database template-part override from the shipped header/footer file and inject the resolved navigation reference into a uniquely identified block. For this approach, record ownership and template-file fingerprint, and provide explicit later migrations when the underlying file changes. Do not rewrite every Navigation block or replace an entire user-edited header without checking conflicts.

Database template/template-part/global-style customizations can override shipped theme files. Preflight must identify relevant overrides for the target theme. Preserve unrelated overrides. If a migration intentionally replaces one, preview it, record its prior value, validate its expected state, and scope the change to that exact override. Do not globally delete Site Editor customizations.

Remap references in synced patterns, page blocks, image blocks, and template parts where required. Check for local domains, stale IDs, missing blocks, and unintended fallback menus. Preserve the main theme specification's responsive and accessible navigation behavior.

### Payloads and assets

Keep payloads human-readable and reviewable in version control, using PHP arrays, JSON, or block HTML as appropriate. Include only the necessary content and assets. Declare required existing pages and plugins; do not invent replacements when a prerequisite is missing.

For new media, use a stable asset key and checksum to avoid duplicate attachments. Preserve original live media unless explicitly targeted. Package needed design assets or specify a controlled import source; do not depend on an inaccessible local development URL. Do not put secrets or private form uploads in deployment ZIPs.

### Execution, security, and recovery

- Restrict the screen and every execution/report endpoint to authorized administrators using capabilities (at least `manage_options`, plus relevant target capabilities).
- Protect mutations with verified nonces and POST requests. Nonces are not authorization; check capabilities separately on every request.
- Sanitize inputs, escape output, and restrict migration IDs to the registry.
- Use a single-run lock with atomic acquisition, owner token, expiry/heartbeat, and ownership-checked release. A second browser tab must not start another writer. Reject stale workers after lease ownership changes.
- Run bounded batches using authenticated admin AJAX or REST requests. Persist progress so closing the browser or a timeout can be recovered from. Do not rely on one long PHP request or require shell access in cPanel.
- Stop on the first failed operation and block dependent migrations. Do not mark the migration completed until all postconditions pass.
- On interruption, resume from verified state and reconcile partially completed operations.
- Record small before-images for managed fields and created-object identities, with administrator-only access. Do not log credentials or unrelated personal content.
- Do not promise automatic transactional rollback: WordPress hooks, files, DDL, and external side effects are not one atomic transaction. Version 1 may provide documented recovery and full-backup restoration instead of an Undo button.
- Do not send emails, trigger outbound production integrations, or recreate business transactions as migration work. Document environment isolation for local/staging and inspect relevant hooks before changing plugin-managed content.

Reports must state what changed, what was skipped, what failed, and the next action. Avoid exposing raw server paths or database errors to unauthenticated users.

### Development contract for every future change

Whenever a request introduces a database-dependent change:

1. Implement the feature in the correct theme or permanent functionality component.
2. Add/update an unreleased migration, or add a new migration if the prior one has shipped.
3. Include its payload, dependencies, conflict checks, preview, postconditions, and recovery notes.
4. Run it through the migration runner locally. Do not mark it complete merely because equivalent manual edits already exist.
5. Test against a separate fresh live-site copy, including destination IDs that differ from development.
6. Update the migration inventory and deployment checklist.

Maintain `MIGRATIONS.md` with migration ID, feature, affected records/settings, dependencies, and release. Manual Site Editor changes are not automatically captured. Explicitly export them to theme files or translate the relevant content/settings into migration payloads.

### Launch workflow

Rehearsal:

1. Preserve the development site and its packaged theme/plugin.
2. Restore a recent live backup into a separate local or protected staging site.
3. Prevent that copy from sending real notifications or contacting production integrations; do not carry local-only suppression settings back to production accidentally.
4. Install the matching theme and migration plugin release.
5. Preview, process, test, and document the elapsed time.

Final cutover using the owner's UpdraftPlus workflow:

1. Freeze live writes BEFORE taking the final backup. Cover editors, registrations, scheduled writers, and relevant integrations; a visitor maintenance page alone is insufficient.
2. Restore that final backup into the separate local destination.
3. Apply the tested theme and pending migrations, then verify the site.
4. Export with UpdraftPlus and migrate back to live, handling destination URLs through the migration tool.
5. Verify production configuration, registration/login, navigation, blog pages, media, and frontend behavior before reopening writes.

The migration history travels with a complete database migration. On live, completed migrations must remain completed and must not rerun due to the domain change. Rendered internal links must point to the destination after UpdraftPlus URL replacement. Do not bind completion solely to a hostname.

This plugin does not merge two active databases or make it safe to overwrite registrations received after the final backup. If deployment fails, restore from the known-good backup while the write freeze remains in effect. After writes resume, recovery must preserve new activity rather than blindly restoring an old database.

### Acceptance criteria

Provide meaningful automated/integration tests and a short manual rehearsal checklist demonstrating:

- Activation does not change site content or theme settings.
- Preview leaves target data unchanged.
- A fresh clone receives exactly the specified design/content changes.
- Different destination IDs and domains produce correct navigation and references.
- A second run creates no duplicates and overwrites no subsequent editor changes.
- Missing prerequisites, ambiguous matches, and conflicting edits block clearly.
- Interrupted execution resumes without duplicated or falsely completed operations.
- Two concurrent requests cannot run the same migration simultaneously.
- Unauthorized users, missing/invalid nonces, and unregistered migration IDs are rejected.
- Completed-migration checksum changes are detected.
- Relevant Site Editor overrides are handled without deleting unrelated customizations.
- Posts, users, registration data, comments, unrelated options, and existing uploads remain unchanged by these migrations.
- The completed site works with the migration plugin deactivated.
- Full-site migration preserves completion history and destination-correct links.

Use representative fixtures with different IDs and a forced mid-migration failure. Test actual behavior, not just that helper methods were called. Scope version 1 to single-site WordPress; detect multisite and block execution with an explanation unless the main specification explicitly requires and tests multisite support.

### Deliverables

- Updated main specification incorporating this addendum.
- Theme source and installable ZIP; permanent EyeDonation Search plugin source and its own installable ZIP.
- Companion migration plugin source and installable ZIP.
- All migrations needed by the implemented initial design, not just an empty runner.
- Compatibility manifest connecting theme release and migration release.
- MIGRATIONS.md, administrator instructions, deployment/recovery checklist, and validation results.

If exact menus or other payloads have not yet been specified, build the infrastructure and document those payloads as pending. Never ship illustrative sample content as if it were approved production content.

### Official references

Verify API behavior against the installed WordPress version during implementation:

- Post creation/update API: https://developer.wordpress.org/reference/functions/wp_insert_post/
- Block template hierarchy and database overrides: https://developer.wordpress.org/themes/templates/template-hierarchy/
- Nonces and their authorization limitations: https://developer.wordpress.org/apis/security/nonces/
- Block parsing: https://developer.wordpress.org/reference/functions/parse_blocks/
- Block serialization: https://developer.wordpress.org/reference/functions/serialize_blocks/

This is a project implementation contract for a custom companion plugin, not a claim that WordPress includes a native Laravel-style migration runner.

## Combined release acceptance gate

In addition to all preceding acceptance criteria, demonstrate these project-specific scenarios:

1. Three matching packages install on a fresh live-copy site; preview identifies only intended changes and current owner inputs.
2. Explicitly authorized homepage redesign updates the intended existing page, keeps its ID, and leaves unrelated posts, user registrations, and metadata untouched.
3. Primary/footer menus remain editable in native WordPress; the header/footer renders correctly after the migration plugin is deactivated.
4. Updating shipped header/footer files with a managed database override present produces an actionable warning or a specific follow-up migration, never an unnoticed stale header.
5. New blog posts created on live after the original development clone appear in the rehearsal clone's homepage Query loop and rebuilt search index, without importing development posts or an old index.
6. Search settings modified independently on the destination are preserved or produce a conflict. Missing category/page mappings block the specific affected operation.
7. Disabling only the migration plugin leaves the finished site intact; disabling EyeDonation Search separately restores native search while the theme popup still works.
8. The final deployment report names the three tested versions and identifies any missing assets, blocked operations, or remaining launch work. Do not claim completion while required migrations or inputs remain unresolved.
