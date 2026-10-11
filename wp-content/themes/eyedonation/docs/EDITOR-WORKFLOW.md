# Everyday editing

Open Pages and edit the homepage's normal Gutenberg content. Reorder section Groups, change their text, replace images and edit buttons without PHP. Edit both desktop/mobile archival Image blocks in History. Do not put header/footer content into a page.

News cards draw from current public Posts. Set featured images, titles and body paragraphs there; homepage query settings remain editable. The first-paragraph block previews the current post context. Do not replace the live Query with copied cards.

Use Appearance → EyeDonation Site Management for the shared Header, Footer, saved Navigation, Templates and Global Styles. Selecting a Site Logo overrides the supplied portable logo fallback. Set approved social URLs in the Footer's native Social Icons blocks. Utility/contact text is visibly provisional until confirmed.

For existing accounts use the editor's Show template toggle to choose content-only view. New accounts on WordPress 7.1+ receive a default only when their preference is unset; existing preferences are preserved. Pages → Quick Edit → Template selects Default, Wide Page or Landing Page without changing shared parts.

A saved Site Editor template/part overrides its shipped file. Inspect that customization before resetting it. Managed navigation bindings are deliberate overrides; the theme warns when their shipped source changes. Resolve through a narrowly reviewed follow-up migration rather than blindly resetting every customization.

Approved primary navigation update: all 22 labels and supplied destinations are included, with Give Now and More as non-link dropdown headings. Fresh installations use editable inline core Navigation blocks in the shipped header without needing a plugin or database seeding. Existing Site Editor overrides remain authoritative. Migration 008 explicitly updates only the managed primary wp_navigation record; classic menus/items/assignments, footer menu and homepage remain unchanged. Completed 001–007 payloads and checksums are preserved. The migration plugin may be deactivated after applying the menu. Edit the new menu in Appearance → Editor → Navigation; classic menus remain available through the legacy menus screen while transitioning. On a new site, the base navigation migrations can introduce their historical placeholder menu; run 008 afterward to apply the approved menu. No destination pages are created or overwritten.

Homepage templates (current behavior): the live front-page.html and assignable homepage.html display the page’s native Post Content, so Edit Home Page opens editable text, images, buttons, and section blocks. The complete supplied layout was restored to the existing homepage. The old front-page editor override was preserved in Trash before reset.

The complete design is also saved independently as templates/homepage-layout.html (EyeDonation Saved Homepage Layout) and the EyeDonation Complete Homepage pattern. Neither needs the migration plugin at runtime. Use the pattern to restore an emptied page: Edit Home Page → block inserter → Patterns → search EyeDonation Complete Homepage → insert → Save. Keep the page on its default template or EyeDonation Homepage so page-editor adjustments are displayed. The Saved Homepage Layout template renders its own layout and is edited in the Site Editor; it should not be assigned to the live page when page-editor text editing is desired.

Future replacement pages can each have a dedicated templates/*.html file and a corresponding complete editable-content pattern. Current local restoration retained the page ID, primary menu, and Thrive editor settings.
