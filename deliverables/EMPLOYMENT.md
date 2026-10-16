# Employment

The existing `/employment/` page uses native editable Image, Heading, Paragraph, Columns, List, and List Item blocks with the **EyeDonation Employment** template. The legacy template’s introduction, five departments, ten list items, and career/internship instructions are preserved. Both HR links use `mailto:HR@ebsr.org`.

The layout shares Home/About/Leadership typography, 1050px content width, and blue headings. Department columns stack on phones. The original header image is bundled under `assets/employment/`. Legacy custom fields remain intact; rendering no longer depends on Genesis, Bootstrap, or Toolset.

Reuse the **EyeDonation Employment** pattern on an empty page. The **EyeDonation Saved Employment Layout** template is an independent backup layout; the normal template displays editable page content.

On another site, install the updated theme and migrations plugin, then preview and run **022_install_employment** under Tools → Site Migrations. Save a recoverable database backup before replacing content. The migration records before-images, requires explicit replacement acknowledgment, and preserves later edits after completion.
