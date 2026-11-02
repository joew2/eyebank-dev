# Responsive header search

The native WordPress Search block remains in the Header template part. Its field and submit button use the existing WordPress search destination and `s` query parameter; no new search service or database setting is required.

## Desktop (1025px and wider)

`assets/js/inline-search.js` wraps the form with a magnifier/chevron toggle. The panel is absolutely positioned below the toggle, so opening or closing it does not change navigation gaps, padding, font sizes or item positions. It has a white background and drop shadow, sits flush below the blue navigation bar without a blue top border, and uses a light-gray outlined submit magnifier. The field stays at or below 170px; the outer panel is 220px including its submit button and padding.

Mouse hover opens the panel without moving focus. A 180ms leave delay allows moving into the panel. Click/keyboard activation focuses the field and keeps the panel open while interacting. Clicking after a hover-open focuses the field instead of immediately closing it. The chevron appears only when expanded. The panel is clamped inside the viewport and can open upward when necessary. Its stacking order keeps it above the logged-in member strip.

## Mobile/tablet (1024px and narrower)

Inside the mobile menu, activating the magnifying glass expands a compact field immediately to its right, on the same row. The field/form is capped at 170px and the complete expanded control is 222px wide including the 44px toggle and 8px gap. The row remains centered in the menu. Width/fade/slide transitions reveal the field; no hanging panel, shadow or chevron is used. Mobile search is click/touch/keyboard driven rather than hover driven.

## Shared behavior

Escape, an outside click or leaving the focused control closes search; Escape returns focus to the toggle. The closed form is inert and hidden from assistive technology. The mobile menu leaves Escape handling to an open search before dismissing the menu. Closing the mobile menu also closes search. Crossing the desktop/mobile breakpoint resets the open state, and transitions respect reduced-motion preferences. Input text and WordPress form submission are preserved.

Appearance → Site Editor → Header continues to provide the native Search block. Sources are `assets/js/inline-search.js`, `assets/css/search.css`, and the Escape integration in `assets/js/mobile-menu.js`. Desktop primary-menu spacing in `assets/css/shared.css` remains unchanged on search expansion.
