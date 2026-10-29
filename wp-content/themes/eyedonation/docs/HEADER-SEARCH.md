# Header search dropdown

The native WordPress Search block remains in the Header template part. Its field and submit button use the existing WordPress search destination and query parameter; no new search service or database setting is required.

`assets/js/inline-search.js` wraps that form with a magnifier/chevron toggle. The panel is absolutely positioned below the toggle, so opening or closing it never changes navigation gaps, padding, font sizes, or item positions. It has a white background and a drop shadow, sits flush below the desktop blue navigation bar without a blue top border, and uses a light-gray outlined submit magnifier. The field stays at or below 170px; the outer panel is 220px including its submit button and padding.

Mouse hover opens the panel without moving focus. A 180ms leave delay allows moving into the panel, and entering it cancels the delayed close. Click/touch/keyboard activation focuses the field and keeps the panel open while interacting. Clicking after a hover-open focuses the field instead of immediately closing it. Escape, an outside click, or leaving the control closes it; Escape restores focus to the toggle. The closed form is inert and hidden from assistive technology. The chevron appears only when expanded, and transitions respect reduced-motion preferences.

The panel is clamped horizontally inside the viewport. It opens above the toggle only where there is insufficient vertical space below, such as inside a short mobile menu. The open header/control stacking order puts it above the logged-in member strip. The mobile menu leaves Escape handling to an open search before dismissing the menu itself.

Appearance → Site Editor → Header continues to provide the native Search block. Behavior/style sources are `assets/js/inline-search.js`, `assets/css/search.css`, and the Escape integration in `assets/js/mobile-menu.js`. The primary-menu spacing rules in `assets/css/shared.css` do not change on search expansion.
