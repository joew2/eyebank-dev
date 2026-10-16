# Volunteer

`/volunteer/` uses the **EyeDonation Volunteer** template with native editable Image, Heading, Paragraph, Columns, Group, and Shortcode blocks. Both original photographs are bundled with the theme, and the original introduction is preserved.

The form is a native Shortcode block containing exactly:

```
[gravityform id="1" title="false" description="false"]
```

Edit the form fields, notifications, confirmations, and integrations in Gravity Forms. The theme does not replace the form or submit entries itself. On another site, Gravity Forms must be active and the same form must exist as ID 1, or edit the shortcode to its imported ID. The page migration does not copy forms or entries.

The shared site footer provides current social links and phone information in place of the legacy template’s additional social-links-and-phone footer strip.

Reuse the **EyeDonation Volunteer** pattern on another page, or choose **EyeDonation Saved Volunteer Layout** for an independent backup layout. The normal template displays editable Post Content.

For deployment, install the theme and migrations plugin, then preview and run **023_install_volunteer** in Tools → Site Migrations after saving a recoverable database backup. The migration records before-images, retains legacy builder fields, and preserves subsequent edits after completion.

## Revised desktop columns

The current pattern and backup layout place the heading, introduction, and heart photo in the left column, with the preserved form in the right column. Mobile stacks the introduction/photo before the form. Migration **025_reflow_volunteer** updates an existing converted page without changing its text, images, heading, or Shortcode block. Apply 023 first for an older installation, then preview/run 025.
