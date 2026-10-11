# WordPress Plugin Project Plan: Custom Translation Navigation Links Plugin

## Project Goal

Create a custom WordPress plugin named **Custom Translation Navigation Links Plugin**.

The plugin will allow site administrators to manage English-to-Spanish page URL mappings from the WordPress admin area.

The plugin should provide shortcodes that can be manually placed on English and Spanish pages. These shortcodes should display a link to the corresponding translated page only when a matching URL mapping exists.

This plugin should not automatically inject links into templates, page headers, footers, hooks, or page content. Display should be controlled only by shortcode placement.

---

## Plugin Name

The plugin name used in the main plugin file header should be:

**Custom Translation Navigation Links Plugin**

Suggested plugin folder/slug:

`custom-translation-navigation-links`

---

## WordPress Admin Menu Name

Create a dedicated sidebar navigation item in the WordPress admin dashboard for easy access.

The admin sidebar menu label should be:

**Translation**

This sidebar item should open the plugin’s mapping management screen.

Suggested page title:

**Custom Translation Navigation Links**

---

## Core Use Case

The website has English pages and Spanish pages. Spanish pages may use their own URL structures and should not be assumed to live under one specific subdirectory pattern.

Examples of possible Spanish URLs:

- `/es/`
- `/sea-donante/`
- `/cirugia-de-trasplante/`

The administrator should be able to manually define the relationship between an English page URL and its corresponding Spanish page URL.

Example:

- English page: `/become-a-donor/`
- Spanish page: `/sea-donante/`

Another example:

- English page: `/transplant-surgery/`
- Spanish page: `/cirugia-de-trasplante/`

The plugin should not assume that Spanish pages always begin with `/es/`.

---

## Important Display Requirement

The shortcode must never show a blank, broken, placeholder, or inactive link.

If the current page has no associated translation mapping, the shortcode should return an empty string and display nothing on the front end.

---

## Admin Interface Requirements

Create a WordPress admin sidebar menu item labeled:

**Translation**

The Translation admin page should allow the administrator to:

1. Add a new English/Spanish URL pair.
2. Edit an existing URL pair.
3. Delete a URL pair.
4. View all existing mappings in a table.
5. Enable or disable individual mappings.
6. Save changes securely.
7. Prevent obvious duplicate or invalid entries.

---

## Mapping Table Fields

Each translation mapping should include the following fields:

### Required Fields

| Field | Description |
|---|---|
| English URL Path | The English page path or URL |
| Spanish URL Path | The Spanish page path or URL |

### Optional Fields

| Field | Description |
|---|---|
| English Link Label | Text shown on Spanish pages linking back to English |
| Spanish Link Label | Text shown on English pages linking to Spanish |
| Enabled | Whether this mapping is active |

---

## Default Link Labels

If custom labels are not entered, use these defaults:

### On English Pages

Display link text:

`Ver esta página en Español`

This links to the Spanish page.

### On Spanish Pages

Display link text:

`View this page in English`

This links to the English page.

---

## Shortcode Requirements

The plugin should provide two shortcodes.

---

## Shortcode 1: English Page to Spanish Page

Shortcode:

`[spanish_page_link]`

Purpose:

This shortcode is placed on English pages.

Expected behavior:

1. Detect the current page URL or path.
2. Look up the current page in the English URL column.
3. If a matching enabled mapping exists, output a link to the Spanish URL.
4. If no mapping exists, output nothing.

Default output example:

`<a href="/sea-donante/" class="ctnl-translation-link ctnl-spanish-link">Ver esta página en Español</a>`

---

## Shortcode 2: Spanish Page to English Page

Shortcode:

`[english_page_link]`

Purpose:

This shortcode is placed on Spanish pages.

Expected behavior:

1. Detect the current page URL or path.
2. Look up the current page in the Spanish URL column.
3. If a matching enabled mapping exists, output a link to the English URL.
4. If no mapping exists, output nothing.

Default output example:

`<a href="/become-a-donor/" class="ctnl-translation-link ctnl-english-link">View this page in English</a>`

---

## URL Storage Recommendation

The plugin should support both full URLs and relative paths in the admin interface, but internally it should normalize and compare URL paths.

Preferred stored format:

`/become-a-donor/`

`/sea-donante/`

This is better than storing only full URLs because it allows the site to move between staging and production without breaking all mappings.

---

## Important URL Structure Note

Do not hard-code or assume any specific Spanish URL structure.

The Spanish URL may be:

- `/es/`
- `/sea-donante/`
- `/cirugia-de-trasplante/`
- Any other valid page path on the same WordPress site

The admin should manually define the exact Spanish URL for each mapping.

---

## URL Normalization Rules

When saving or comparing URLs, the plugin should normalize paths to avoid mismatches.

The plugin should account for:

1. Full URLs entered by the admin.
2. Relative paths entered by the admin.
3. Trailing slashes.
4. Query strings.
5. URL fragments.
6. Site domain changes between staging and production.

Examples that should be treated as equivalent:

`https://www.example.com/sea-donante/`

`/sea-donante/`

`/sea-donante`

The preferred normalized result should be:

`/sea-donante/`

---

## Front-End Matching Logic

When a shortcode runs, it should:

1. Get the current page URL path.
2. Normalize the current path.
3. Compare it against the saved mappings.
4. Return the correct link if a match exists.
5. Return nothing if no match exists.

Example:

Current page:

`/become-a-donor/`

Saved mapping:

- English URL: `/become-a-donor/`
- Spanish URL: `/sea-donante/`

Shortcode:

`[spanish_page_link]`

Output:

`Ver esta página en Español`

linked to:

`/sea-donante/`

---

## No Automatic Display

Do not add automatic insertion into page templates, hooks, headers, footers, or content filters.

The plugin should only render translation links where the administrator manually places one of the shortcodes.

This is intentional because the English and Spanish pages may use different templates, and the link needs to appear in specific locations on each page.

---

## Admin Table Behavior

The Translation admin screen should include:

1. English URL/path field.
2. Spanish URL/path field.
3. Optional English label field.
4. Optional Spanish label field.
5. Enabled/disabled checkbox.
6. Save button.
7. Delete option per row.
8. Add new row button.

Recommended behavior:

- Existing mappings appear in editable rows.
- The admin can add a blank row for a new mapping.
- Deleting a row should require confirmation.
- Saving should validate and sanitize all fields.
- Duplicate English paths or duplicate Spanish paths should be prevented.

---

## Data Storage Recommendation

Use a custom database table if the plugin is expected to manage many mappings.

Suggested custom table name:

`wp_ctnl_translation_mappings`

Suggested columns:

| Column | Type | Notes |
|---|---|---|
| id | BIGINT unsigned | Primary key |
| english_path | VARCHAR | Normalized English path |
| spanish_path | VARCHAR | Normalized Spanish path |
| english_label | VARCHAR | Optional custom label |
| spanish_label | VARCHAR | Optional custom label |
| enabled | TINYINT | 1 or 0 |
| created_at | DATETIME | Creation timestamp |
| updated_at | DATETIME | Last updated timestamp |

For a smaller/simple site, storing mappings as a serialized option could work, but a custom table is preferred for cleaner management and future expansion.

---

## Security Requirements

The plugin should follow standard WordPress security practices.

Admin-side requirements:

1. Only users with appropriate permissions should manage mappings.
2. Use `manage_options` capability for the admin page.
3. Use WordPress nonces for saving and deleting mappings.
4. Sanitize all submitted data.
5. Escape all output.
6. Validate URLs/paths before saving.
7. Prevent unauthorized direct access to plugin files.

Front-end requirements:

1. Escape link URLs.
2. Escape link labels.
3. Do not output raw unsanitized admin-entered data.
4. Return an empty string if no valid mapping exists.

---

## Styling Requirements

The output should include simple class names so CSS can be handled by the theme or custom CSS.

Recommended classes:

General class:

`ctnl-translation-link`

Spanish link class:

`ctnl-spanish-link`

English link class:

`ctnl-english-link`

Example:

`<a class="ctnl-translation-link ctnl-spanish-link" href="/sea-donante/">Ver esta página en Español</a>`

The plugin does not need heavy front-end styling.

---

## Optional Shortcode Attributes

The shortcodes may optionally support attributes for flexibility.

Example:

`[spanish_page_link label="Leer en Español" class="custom-class"]`

Possible attributes:

| Attribute | Purpose |
|---|---|
| label | Override the link label for this specific shortcode instance |
| class | Add an additional CSS class |
| target | Optional link target, such as `_blank` |

Attribute behavior:

1. If `label` is provided in the shortcode, use that first.
2. If no shortcode label is provided, use the saved custom mapping label.
3. If no saved label exists, use the default label.

Priority order:

1. Shortcode attribute label.
2. Mapping-specific custom label.
3. Plugin default label.

---

## Expected Empty Output Behavior

If the shortcode does not find a valid mapping, it should return an empty string.

Do not output:

- Empty anchor tags.
- Placeholder text.
- HTML comments.
- Error messages.
- Blank wrappers.
- Disabled links.

Correct behavior:

No mapping found:

`''`

---

## Example Workflow

1. Admin goes to **Translation** in the WordPress sidebar.
2. Admin adds a mapping:

   English URL:

   `/become-a-donor/`

   Spanish URL:

   `/sea-donante/`

3. Admin places this shortcode on the English page:

   `[spanish_page_link]`

4. Admin places this shortcode on the Spanish page:

   `[english_page_link]`

5. On the English page, the plugin displays:

   `Ver esta página en Español`

6. On the Spanish page, the plugin displays:

   `View this page in English`

7. If the admin places either shortcode on a page that has no mapping, nothing displays.

---

## Future Expansion Ideas

These are not required for version 1, but the plugin structure should not prevent them later.

Possible future features:

1. Support for more than two languages.
2. Import/export mappings as CSV.
3. Bulk add mappings.
4. Auto-detect matching translated pages.
5. Dashboard search/filter for mappings.
6. Gutenberg block version of the shortcode.
7. Admin notice for duplicate or broken URLs.
8. Optional link icon or language flag.
9. Support for translated posts, not just pages.
10. REST API endpoint for external management.

---

## Version 1 Scope

The first version should focus only on the essentials:

1. Plugin name: **Custom Translation Navigation Links Plugin**.
2. WordPress admin sidebar item labeled **Translation**.
3. Admin page for managing URL mappings.
4. Two shortcodes:
   - `[spanish_page_link]`
   - `[english_page_link]`
5. URL normalization.
6. No assumption that Spanish pages use a specific subdirectory.
7. Hidden output when no mapping exists.
8. Secure saving/deleting of mappings.
9. Clean, escaped front-end output.
10. Simple CSS classes for styling.

Do not add automatic display logic in version 1.

Do not add translation automation.

Do not create or edit WordPress pages.

Do not integrate with external translation services.

---

## Success Criteria

The plugin is complete when:

1. Admin can access the plugin from a WordPress sidebar menu item called **Translation**.
2. Admin can add, edit, delete, enable, and disable translation mappings.
3. The English shortcode correctly links to the manually defined Spanish page.
4. The Spanish shortcode correctly links back to the manually defined English page.
5. No link displays if no mapping exists.
6. URLs are normalized so full URLs and relative paths can be matched reliably.
7. The plugin does not assume Spanish pages live under `/es/` or any other fixed path.
8. Output is secure, escaped, and frontend-friendly.
9. The plugin does not automatically insert links anywhere.
10. The plugin works with different templates because shortcode placement controls display location.
