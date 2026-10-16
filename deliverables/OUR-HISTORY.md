# Our History

The existing `/our-history/` page uses native editable blocks and the **EyeDonation Our History** template. All 24 original paragraphs from the page body and Toolset fields have been incorporated into page content. Legacy fields are retained, but the new page does not depend on Toolset, Genesis, or Bootstrap for its layout.

The ten original photographs are bundled under `assets/our-history/` for portability. Captions and credits are editable. The contained layout uses the shared 1050px content width, blue headings, 18px body text, desktop text/photo columns, and a single stacked reading order on mobile.

Reuse the **EyeDonation Our History** pattern on an empty page. The **EyeDonation Saved Our History Layout** template is an independent backup layout. The normal template displays the page’s editable content.

Install the updated theme and migrations plugin on another site, then preview and run **020_install_our_history** in Tools → Site Migrations. Save a recoverable database backup before replacing existing content. The preview records before-images and requires explicit replacement acknowledgment; completed runs preserve later editorial changes. The bundled snapshot is the original History copy, including its historical statistics.

## Revised spacing and photo placement

Migration **024_reflow_our_history** updates the original converted layout into independent text/photo columns, using the current page’s blocks rather than replacing its copy. The heading sits above the opening text within the left column, the portrait/surgeon photos stack to its right, and the pilot photo returns to the second photo column above the plane banner. Desktop copy flows continuously; mobile uses an interleaved reading order. All typography and colors remain shared with the newer site pages. The reusable pattern and saved layout use this revised structure.

For an older site, apply migration 020 first, then preview and run 024. A completed 024 run preserves later editorial changes.
