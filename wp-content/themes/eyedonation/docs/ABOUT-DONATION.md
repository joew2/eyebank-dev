# About Donation and News

Page: `/about-donation/`. The **EyeDonation About Donation** template displays editable Post Content. The newsletter heading, text, two buttons, and supplied thumbnail are native blocks. Both newsletter destinations intentionally remain development placeholders until final URLs are supplied.

The **Eye-Bank News grid** block dynamically displays nine latest published, password-free posts. It shares the homepage’s featured-image proportions, title styling, 24-word excerpt/body fallback, and teal inline Read more links. Desktop shows three columns, tablet two, and phones one. No publication-date blocks are included.

Load more fetches nine further stories with a fade/slide entry animation. The read-only public REST endpoint is `/wp-json/eyedonation/v1/news`, with a validated date/ID cursor for stable ordering and no repeated records. The button prevents concurrent clicks, announces status, preserves existing stories after errors, and disappears after the final batch. Reduced-motion preferences disable animation; a no-JavaScript link loads the next batch through a normal page request.

Edit individual news stories under Posts. Custom post excerpts take precedence over the first readable body paragraph. The dynamic grid’s presentation stays shared with the homepage instead of duplicating card content.

Reuse the **EyeDonation About Donation and News** pattern. **EyeDonation Saved About Donation Layout** supplies an independent backup template. Install the theme and migrations plugin elsewhere, then preview/run **026_install_about_donation** in Tools → Site Migrations after saving a recoverable database backup. It replaces or creates only this page, retains legacy builder data, and preserves later edits after a completed run.
