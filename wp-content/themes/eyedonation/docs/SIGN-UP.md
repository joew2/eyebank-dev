# Mailing list signup

`/sign-up/` uses the **Mailing List Signup — Editable Page** template. Intro heading, supporting text, card heading, unsubscribe note and the Gravity Forms shortcode are native editable blocks. The **EyeDonation Mailing List Signup** pattern restores the content in an empty page. **Mailing List Signup — Backup Layout** renders the pattern independently of saved page content.

Form shortcode: `[gravityform id="85" title="false"]`. Gravity Forms continues to handle submission, field validation, conditional visibility, notifications, feeds and confirmations. No form configuration or entry data is changed. The existing State field remains a text field (the supplied screenshot shows a dropdown); manage field types and required flags in Gravity Forms if desired. The existing address section conditional logic governs fields 5–11. An optional presentation script measures visible fields to paint a continuous pale background without moving fields or overriding their visibility.

Styles are scoped to `.ed-sign-up` and form 85; site pixel-based typography, blue headings and stationary buttons are reused. Form fields stack on mobile. Form labels and validation messages remain visible. The required-fields legend remains available to screen readers. No form submissions are made during layout verification.

The About Donation **Join the mailing list** button now resolves to `/sign-up/`; its editable copy and styling are preserved. The newsletter archive button remains managed by Custom Newsletters CWS.

## Deployment

Install the matching theme and Site Migrations plugin. Import Gravity Form 85 first and preserve that form ID. In **Tools → Site Migrations**, preview and run **029 — Editable Signup page**, which replaces `/sign-up/` or creates it when missing. The migration records previous content/template/builder flags, verifies a fresh preview and checksums, and preserves subsequent edits on repeat runs. Builder data is retained. The migration does not configure mailing integrations; existing Gravity Forms feeds remain responsible for subscriptions.

## Verification

Desktop (1440px) and mobile (390px) visual checks passed in both collapsed and expanded postal-address states. Name fields share a desktop row and stack on mobile; all five address inputs show/hide through the existing Gravity Forms logic, with no horizontal overflow. About Donation signup destination verified. WordPress native block parser validates all 10 blocks. Migration 029 completed locally and a repeat run preserved the installed content; Gravity Form 85 configuration hash remained unchanged. PHP lint, JavaScript syntax and whitespace checks passed. No form was submitted.
