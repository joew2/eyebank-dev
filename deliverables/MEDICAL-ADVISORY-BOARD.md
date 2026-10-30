# Medical Advisory Board

`/medical-advisory-board/` uses **Medical Advisory Board — Editable Page**. The banner, heading, five Shortcode blocks, and back-to-top link are editable in the page editor. The banner is bundled in the theme, so it does not rely on a media-library attachment.

The original Toolset Views are retained in order: `mab-chair`, `mab-secretary`, `mab-medical-director`, `mab-associate-medical-director`, and `mab-main-list`. Edit people and their assignments through the existing Toolset content and Views. The new page shares the Board of Directors typography, colors and spacing. The old social-links-and-phone shortcode is replaced by the shared site footer, which already supplies those details.

**Medical Advisory Board — Backup Layout** renders the reusable `eyedonation/medical-advisory-board` pattern. Insert that pattern into another page to start with editable blocks.

Migration **045** under Tools → EyeDonation Migrations previews and replaces the page, assigns the template, and disables the old page builder for this page while retaining builder data. Preview and back up before installing. Repeat runs preserve later edits. Upload the matching theme and migrations ZIPs together. Toolset Views must be active; the existing five view definitions and their people/media records must already exist on the destination (they are not recreated by this layout migration). Local installation saves the previous page content and template in `ed_medical_advisory_board_legacy_backup`.
