# Correspondence guidance pages

The following routes retain their existing IDs and links:

- `/cornea-recipients/`: **Write to a Donor Family**, native page template `cornea-recipients`.
- `/donor-families/`: **Write to a Transplant Recipient**, native page template `donor-families`.

Each has an editable Post Content template, a matching reusable pattern, and a `-layout` backup template that renders the pattern independently of saved page content. Text, headings, lists, photos and links are native blocks. Shared 1050px content width, pixel font sizes and blue headings match the other migrated landing pages.

Source combines the legacy page body with both `wpcf-*-left-column-sec-2` and `wpcf-*-left-column-sec-3` fields. All source guidance, ordered/unordered lists, postal address and contact link are preserved. Heading levels are updated to H1/H2. Legacy templates used an independent two-thirds text column and one-third photo column; the new layout preserves those desktop proportions and interleaves photos with content on mobile. The second sidebar photo, previously omitted by the old mobile template, is also available on mobile.

All six unique original JPEGs are bundled under `assets/letter-pages/`, with source paths and SHA-256 hashes in `manifest.json`. Media Library and Toolset rendering are not required for the new page content. Original custom fields and builder metadata remain available in the database; migrations change only content, template assignment and builder enable/disable flags.

## Deployment

Install matching theme and Site Migrations plugin. Under **Tools → Site Migrations**, preview and run:

- **032 — Editable Write to a Donor Family page**
- **033 — Editable Write to a Transplant Recipient page**

Each replaces the existing page or creates it if missing. Fresh previews, checksums, before-images, actor/capability checks and locks protect the operation. Repeated completed runs preserve subsequent editorial changes. On a site with different source copy, review the preview because the supplied layout contains the approved local source text.

## Verification

All original visible guidance was compared against the converted block text and preserved exactly, apart from the updated page heading. Native WordPress validation passed for 44 donor-family blocks and 36 recipient blocks. The source lists retain 14 ordered items and 11 unordered items respectively. Both pages were checked at 1440px and 390px: four images load from theme assets per page, column/stack layouts work, the recipient contact email remains intact, and neither layout has horizontal overflow after responsive header transitions settle. All photos have descriptive alternative text. Migrations completed locally; repeated runs preserve edits and original legacy fields remain unchanged.
