# Donor, board and contact pages

The existing routes and page IDs remain in use:

- `/become-a-donor/`: Become a Donor. English and Spanish copy is moved from the page body and eight legacy custom fields into editable blocks, with the original English/Spanish Donate Life registration destinations preserved.
- `/board-of-directors/`: Board of Directors. Six original Toolset Views remain in native shortcode blocks: `bod-president`, `bod-vice-president`, `bod-treasurer`, `bod-secretary`, `bod-main-list` and `bod-honorary-directors`. Director records and View definitions remain managed in Toolset.
- `/contact-us/`: Contact Us. Uses the existing `[gravityform id="7" title="false" description="false"]` shortcode, with the original introductory copy and contact details. The physician login link is now portable `/for-physicians/`. Form validation, feeds, notifications and confirmations remain controlled by Gravity Forms.

Each page has an editable Post Content template, a reusable pattern and a `-layout` backup template. Header JPEGs are bundled in `assets/information-pages/` with source paths and hashes in `manifest.json`. Layouts use the shared 1050px content width, pixel fonts and blue headings. The donor page retains two language columns on desktop and stacks them on mobile; Contact Us retains a larger form column beside the contact information and stacks on mobile. Existing site footer provides social links and phone information, replacing the old template's duplicated social-links footer shortcode.

## Deployment

Install the matching theme and Site Migrations plugin, and preserve/import existing Toolset Views and Gravity Form 7. Preview and apply migrations **034**, **035** and **036** under Tools → Site Migrations. These preserve prior page content/template values in their migration records and retain old custom fields and builder data. Completed repeat runs preserve editorial changes. Board and Contact migrations require their shortcode provider/form to exist. Form entries and integration settings are not changed.

## Verification

Native WordPress validation passed: 20 blocks for Become a Donor, 18 for Board of Directors and 13 for Contact Us. All saved donor/contact copy and donor custom-field text were compared against the converted content and preserved. Desktop (1440px) and mobile (390px) checks passed without horizontal overflow. The donor registration links retain English/Spanish destinations; header images load from the theme. All six board Views render the same 19 existing names and roles in the original order. Contact Us renders one Gravity Form 7 with 13 visible input/textarea/select controls; no form submission was performed, and the stored form configuration hash is unchanged. Migrations 034–036 completed and repeat runs preserved installed content. PHP lint, ZIP integrity and whitespace checks passed.
