# Change notes

## October 11, 2026 — Homepage presentation refinements

- Refined menu highlight color to #043b90, removed gaps and dropdown borders, widened submenus, and changed arrows to an animated vertical flip with 10% smaller and thinner chevrons. Search uses the same highlight.
- Reduced donor panel hover growth to 2% and made the history background span the browser width.
- Added photo zoom/shading to resource cards and news images; resource cards are clickable across the full photo and text.
- Centered Watch Adam’s Video over its editable photo, with bottom shading.
- Added button hover shadows, gentle lift, and color-specific pressed feedback. About Us, Watch Adam’s Video, and Give Now have 22px corners. Give Now uses #00b2e3 and the supplied white donation icon.
- Replaced footer social shapes with the supplied white SVGs and linked the approved Instagram, LinkedIn, and Facebook profiles. Removed social placeholder wording and colored service backgrounds.
- Aligned news photos, matched title blue to the section heading, and added inline teal Read more links. Previews use up to 24 whole words, preferring a custom post excerpt and falling back to body text.

Validation: PHP syntax checks and local Chrome checks for menu spacing, image/link hover effects, responsive testimonial layout, exact SVG paths and social destinations, button press feedback, and news layout. Installable theme package rebuilt locally.

Backup scope: theme, custom plugins, design sources, and documentation. Existing exclusions continue to keep WordPress core, third-party themes/plugins, uploads, database exports, credentials, and generated ZIPs out of Git. WordPress database edits remain in the local environment and require a separate database backup.

## October 12, 2026 — Approved footer

Replaced contact placeholders with The Eye-Bank for Sight Restoration, 120 Wall Street, New York, NY 10005, and (212) 742-9000. Installed the eight supplied navigation destinations, EBAA nonprofit caption, and 2026 copyright/administrator wording. Refined responsive footer columns and spacing. Added an explicit saved-footer migration with before-image and completed-run protection; earlier migrations remain unchanged.

## October 12, 2026 — Tablet/mobile header

Restored hamburger navigation at widths up to 1024px and matched the supplied white-header mockup: logo left, inline Donate Life signup and arrow, hamburger right. The header remains sticky, search is available in the menu overlay, and the floating donor panel is hidden on tablets/phones. Desktop navigation and donor panel return at wider widths. Shared rendering applies to existing saved headers and new theme defaults.

## October 13, 2026 — Consolidated backup

Added Custom Accessibility Translation CWS (ATP) 1.2.0: independent accessibility controls, copied legacy page mappings, unified administration, footer/screen placement, compact widget sizing, readable language choices, and optional bottom-right placement on tablets/phones. Included its integration/browser checks and documentation.

Included the final centered mobile footer with two dividers, 15px contact/navigation text, 13px caption/copyright text, automatic copyright year in the site timezone, and 50px desktop navigation bar. Backed up supplied mobile header/footer mockups and the new About Us design assets.

## October 13, 2026 — Editable About Us

Replaced `/about-the-eye-bank/` with the supplied Who We Are, Mission, Vision, Values, History, and Leadership layout. Added responsive hero photographs, circular mission images, inline SVG value icons, full-card links, a reusable pattern, and two About Us templates. Added migration 016 for explicit installation on existing/new sites, with before-images and completed-run protection.

## October 14, 2026 — Shared page styles

Unified Home/About heading and body typography and extracted reusable photo-card styles into shared.css. History/Leadership now match the homepage resource cards, including bright white labels above the gradient. Widened the About intro to a 1200px maximum, moved it approximately 80px upward on laptops, and trimmed 70px from the hero height/top image framing. Verified responsive layouts from 320px to 1440px.

## October 14, 2026 — Revised About hero photographs

Replaced desktop/mobile About hero photos with the supplied upload URLs and tightened desktop/mobile hero framing. Added scoped photo migration 017, updated fresh-page installer 018, and portable image fallbacks. The reusable pattern and saved backup layout render the revised photos.

## October 14, 2026 — Pixel-based typography

Converted theme rem values to pixels at 16px per rem, with the requested body-text adjustment to 18px and photo-card text to 20px. Updated theme font presets, search typography, and shared/editor styles.

## October 14, 2026 — Contained search popup

Replaced the fullscreen search presentation with a centered responsive panel, drop shadow, animated opening/closing, and a circular upper-left edge close button. Updated the description to the supplied donation/family/physician copy. Retained modal keyboard and reduced-motion behavior.

## October 14, 2026 — Recipients and donor families page

Created the editable Transplant Recipients & Donor Families page from the supplied desktop/mobile designs, with bundled photos, shared typography/content width, resource links, a reusable pattern, and an independent backup template. Added explicit migration 019 for installation or reviewed replacement on other sites.

## October 15, 2026 — Approved homepage links

Replaced homepage and saved header placeholder destinations with the supplied resource, About, video, EyeCanSeeAgain.org, and donation URLs. Updated the default header and reusable homepage pattern; unresolved original placeholders in older layouts resolve to the approved URLs without overriding custom links. Verified rendered homepage destinations and both EyeCanSeeAgain.org buttons in Chrome.

## October 15, 2026 — Editable Our History

Migrated /our-history/ from the legacy Bootstrap/Genesis template and Toolset fields into native blocks. Preserved all 24 paragraphs, ten photos, captions, and credits. Added a contained responsive layout using shared typography, a reusable History pattern, normal and independent backup templates, portable images, and explicit migration 020. Legacy fields remain intact.

## October 15, 2026 — Editable leadership page

Replaced /who-leads-the-eye-bank/ with the supplied header image and copy in editable native blocks. Reused the shared 1050px content width and typography; added a reusable Leadership pattern, independent backup template, portable photograph, and explicit migration 021.

## October 15, 2026 — Editable Employment page

Rebuilt /employment/ from the legacy Bootstrap/Genesis template and its custom field with native editable blocks, the original header photograph, five departments and ten list items, and career/internship instructions. Added shared page typography, responsive columns, a reusable pattern, independent backup template, and migration 022.

## October 15, 2026 — Editable Volunteer page

Rebuilt /volunteer/ using native blocks with both original images, the original introduction, and Gravity Form 1 preserved in an exact Shortcode block. Added shared typography, responsive columns, form field/button styling, a reusable pattern, independent backup template, and migration 023. The shared footer supplies social/contact links.

## October 15, 2026 — History layout refinement

Replaced shared grid rows with independently flowing text/photo columns to match the original page more closely. Moved the heading into the opening text column and returned the pilot photograph above the plane banner. Preserved all paragraph/image blocks, current colors/type, mobile interleaving, and lab-banner separation. Updated the pattern/backup defaults and added migration 024 without changing completed migration payloads.

## October 15, 2026 — Volunteer form placement

Reorganized the editable Volunteer page to match the original desktop layout: heading, introduction, and heart photo in the left column; existing Gravity Form 1 in the right. Phones stack text, photo, then form. Preserved current site styling and every content/shortcode block; updated pattern/backup defaults and added migration 025.

## October 15, 2026 — Hide publication dates

Removed the date block from the single-post template and suppressed core/post-date rendering throughout the theme, including modified-date variants, listings, and saved Site Editor templates. WordPress dates remain stored for ordering and administration.

## October 16, 2026 — Dynamic About Donation landing page

Replaced /about-donation/ with an editable newsletter hero and a nine-story dynamic news grid. Bundled the supplied newsletter thumbnail; retained placeholders for both newsletter buttons at the user’s request. Reused native homepage image/title/summary blocks and teal Read more styling. Added nine-at-a-time AJAX loading, entry animation, accessible status/error/exhaustion handling, reduced-motion support, stable date/ID pagination, and a no-JavaScript fallback. Added pattern, backup template, and migration 026. Corrected selected-post title context when rendering native cards outside the main query loop.

## October 16, 2026 — AJAX news titles

Fixed missing titles in Load More responses: REST requests have no global page context, so the native title renderer could return empty HTML. The selected-post title handler now creates the correct linked heading when needed, preserving the same card classes and styling.

## October 20, 2026 — Mobile navigation accordion

Collapsed top-level submenus by default at widths up to 1024px. Tapping a parent label or its reused desktop chevron expands/collapses the submenu with the existing vertical-flip transition. Added accessible button state/control relationships, Enter/Space support, submenu Escape handling, and collapsed state on menu reopening. Desktop native navigation is preserved.
