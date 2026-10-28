# Consistent news previews

Homepage and About Donation news previews reserve four lines at desktop widths and three lines at phone widths (600px and below), including the Read more link. Client-side fitting measures actual wrapping and trims at a word boundary, with an ellipsis and the link left visible. It recalculates after resizing and for newly appended stories. This avoids relying on a fixed character count across different card widths.

The source is the authored excerpt when present, otherwise readable body paragraphs. Scripts, styles, media, headings and shortcodes are excluded; no shortcodes execute to build previews. Stored post content is unchanged. Very short source material is not invented or padded; the reserved preview height remains consistent. Without JavaScript, line clamping reserves a final line for Read more.

Only About Donation article photos lose their hover tint. Existing homepage photo effects remain. Load More status remains an accessible live region but is visually hidden, removing loaded-count copy beneath the button.

Verification passed for the homepage and 18 archive stories, including an AJAX batch: four rendered lines at 1440px, three at 390px and 320px, visible Read more links, and correct reflow when resizing back to desktop. The About Donation hover overlay is disabled and the load status is visually clipped while retaining aria-live. PHP lint, JavaScript syntax and whitespace checks passed.
