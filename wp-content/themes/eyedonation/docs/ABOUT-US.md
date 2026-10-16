# About Us page

The published `/about-the-eye-bank/` page uses editable native blocks and the **EyeDonation About Us** template, which renders Post Content between the shared header and footer. Edit copy, headings, mission photographs, and linked image cards under Pages → About The Eye-Bank. Both desktop/mobile hero photographs are separate editable Image blocks. The value artwork is inline SVG inside Custom HTML blocks; no SVG file links are used. SVG class/clip identifiers are namespaced to prevent interference with other icons.

## Reusable backup

- Pattern: **EyeDonation Complete About Us** (`eyedonation/about`). Insert this into a page to restore the complete editable layout.
- Template: **EyeDonation Saved About Us Layout** (`about-layout`). This standalone backup renders the complete pattern with the shared header/footer, independently of page content.
- Assigned page template: **EyeDonation About Us** (`about`). Use this template for normal page-content editing.

## Deployment

Install the updated theme and Site Migrations plugin. Under Tools → Site Migrations, preview **016 — Editable About Us page**, review the before/after fields, then acknowledge a recoverable backup and the content replacement to apply. Existing `/about-the-eye-bank/` content is replaced; if the page is missing, the migration creates it. The native template is assigned before updating content to accommodate legacy PHP template metadata. Thrive rendering is disabled only for this page; stored builder data remains intact.

The migration records content/template/builder before-images, checks preview ownership and freshness, guards against changed destination fields, and checkpoints each applied field. Completed retries leave later editorial changes intact. It does not change the homepage, shared header/footer, or old migrations. Supplied photographs are bundled with the theme, so content can resolve on a new site without environment-specific upload URLs.

Keep completed migration 016 and its referenced payload/helper files unchanged. Future layout/content releases should use a new numbered migration and payload, while manual Page edits do not require migrations.

## Validation

Text was extracted from both supplied PDFs using local PDFKit and compared with the mockups. WordPress's native JavaScript block parser reported no invalid blocks for the live page, pattern, content template, or backup template. Chrome checks passed at 1440/1024/390/320px, with no horizontal overflow, four/two value columns, responsive hero selections, exact supplied SVG paths, and full-card links. Existing-page replacement, fresh-page creation, preview-actor rejection, completed-retry preservation, and historical migration checksum preservation were verified. Temporary test pages were removed.

## Updated hero photos

The reusable pattern and backup layout now use `ed_latest_about_content()`, which applies the approved desktop/mobile photo replacements to the original content. The live page uses the supplied October 2026 upload URLs. Matching bundled copies provide a portable fallback if those uploads are absent on a new site. Migration 017 updates only the existing page’s two hero Image blocks; migration 018 is the latest complete About Us installer for a fresh deployment. Completed migration 016 remains unchanged. The desktop hero now targets 620–820px while expanding if the editable text requires room; the phone photo area uses 48vw.
