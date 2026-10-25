# Frequently asked questions

The existing `/faqs/` page uses the **FAQs — Editable Page** template. All 14 questions and 20 answer paragraphs from the supplied HTML are native WordPress Details, Group and Paragraph blocks. Edit each question's Summary and its answer blocks in the page editor. The **EyeDonation FAQs** pattern and **FAQs — Backup Layout** template provide reusable defaults.

Native Details elements work without JavaScript; the shared `name` groups disclosures when the browser supports it. JavaScript enhancement adds a smooth single-open accordion, correct expanded state, non-focusable closed answers, rapid-toggle handling and reduced-motion support. No Bootstrap, copied inline scripts, repeated IDs or custom non-keyboard div triggers remain.

The existing FAQ header photo is bundled in `assets/faqs/faq-header.jpg`, with source path and hash in `manifest.json`. The page uses the shared 1050px content width, blue headings and pixel typography. Original medical/donation copy is preserved. Links are portable; the donor-family writing link targets `/cornea-recipients/`, research targets `/researchers/`, and registration links use the same approved English Donate Life destination as the donor page. Email, Facebook and surgery-information links remain available.

## Deployment

Install matching theme and Site Migrations plugin. Preview and run **037 — Editable FAQs page** under **Tools → Site Migrations**. This replaces `/faqs/` or creates it when missing, records previous content/template/builder flags and retains original builder metadata. Completed repeated runs preserve subsequent edits.

## Verification

All 53 native blocks validate. All 14 supplied question labels and 20 answer paragraphs are present. Desktop (1440px) and mobile (390px) checks passed for opening/closing animation, single-open behavior, Enter/Space controls, unique IDs and absence of horizontal overflow. Reduced-motion mode avoids answer animation, and a JavaScript-disabled browser successfully opens the native disclosure. Migration 037 completed locally and a repeat run preserved installed content. PHP lint, JavaScript syntax, ZIP integrity and whitespace checks passed.
