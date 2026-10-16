# Shared page styles

`assets/css/shared.css` owns reusable typography variables and linked-photo card behavior for Home, About Us, and future pages. It loads after the base/page styles on the public site and in the block editor.

- `--ed-color-heading` follows the theme's WordPress blue palette value.
- `--ed-font-heading` gives section headings the homepage's responsive scale.
- `--ed-font-body` uses 18px on desktop and phones.
- `--ed-font-subheading` provides responsive supporting headings.
- `--ed-font-card` provides 20px white photo-card labels.
- `--ed-content-width` provides the shared 1050px readable content width.

Use `ed-section` for a standard content section, or apply these variables in a page-specific layout. Use `ed-resource-card` on a native Cover block with a linked H3 for shared card padding, bottom gradient, bright white labels, full-card link area, hover zoom/shading, focus treatment, and reduced-motion support. Keep the Cover inner container static so the stretched link covers the entire card. Its z-index override deliberately beats WordPress core Cover layering so the gradient cannot darken labels.

Page-specific geometry remains in `about.css`: responsive hero images, mission circles, values grid, and the 1200px intro panel. At desktop widths the About hero is 70px shorter and its image framing crops an extra 70px from the top; the intro moves approximately 80px upward at a 1275px viewport. Mobile retains a stacked hero/introduction layout.

These presentation rules automatically apply to existing edited pages, reusable patterns, saved layout templates, and pages installed by the existing migrations. No content fields or completed migration payloads were changed for this style update.

Home content sections and all About Us sections now have a 1050px inner content maximum. Section wrappers account for their 5% side padding; the Who We Are white panel includes its own 40px side padding outside that readable width. Responsive gutters remain on smaller viewports.
