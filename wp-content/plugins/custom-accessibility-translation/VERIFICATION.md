# Verification — October 12, 2026

- PHP syntax checks passed for the plugin entry point, mapping module, and widget module. JavaScript syntax checks passed.
- 19 WordPress integration assertions passed: URL normalization, unsafe/malformed input rejection, shortcode compatibility, import idempotency, both language directions, disabled/unmapped behavior, fixture rollback, settings sanitization, admin nonces, unified forms, and capability restriction.
- Local activation copied all 20 existing mapping records. Original records were preserved. The old custom translation and Pojo accessibility plugins were deactivated locally to prevent duplicate interfaces; installation does not silently deactivate plugins elsewhere.
- Chrome checks verified text enlargement, all toggles, saved preferences after reload, reset, Escape/focus restoration, desktop/mobile panel containment, and no JavaScript errors.
- Actual homepage switching between `/` and `/es/` passed; an unmapped route showed the accessibility launcher without language links. Browser storage disabled by policy did not break controls.
- Desktop and mobile screenshots were visually reviewed. The widget is fixed to the chosen screen edge, with the language dropdown directly below the wheelchair launcher.

Local environment still emits existing Genesis Dambuster and All-in-One Video Gallery warnings during CLI bootstrap; these are outside ATP.

## Footer placement — version 1.1.0

ATP now defaults to Footer — scrolls with the page. The existing Screen edge — stays visible while scrolling option remains available. Browser checks at 1440px and 390px verified document-relative movement while scrolling, footer anchoring, upward panel/dropdown placement inside the viewport, and no horizontal overflow. The alternate screen mode remained stationary when scrolling.

## Widget sizing — version 1.1.1

The complete widget renders at 60% of its prior size, including wheelchair, language control, language choices, and accessibility panel. Its edge anchor remains unchanged. Browser checks confirmed exact desktop/mobile scaling and working dropdown, toggle, reset, and Escape controls.

## Widget sizing — version 1.1.2

Widget scale is now 72% of original size, a 20% increase from the prior 60% revision. Expanded language links compensate for scaling to display at the original 16px text size. Their menu width also increases to accommodate the full language names. Desktop/mobile sizing, edge alignment and interactions were verified.

## Responsive placement — version 1.2.0

A configurable tablet/phone override fixes the widget 5px from the viewport bottom/right at widths up to 1024px. Chrome verified exact 5px gaps at 1024/800/390px, stable placement during scrolling, contained panel/language dropdowns, restoration of desktop placement after resizing, and the disabled-option behavior. PHP and JavaScript syntax checks passed.
