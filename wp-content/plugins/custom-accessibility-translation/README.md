# Custom Accessibility Translation CWS (ATP)

One independent plugin combines a wheelchair launcher with a language dropdown beneath it. The front end uses local CSS and vanilla JavaScript, with no remote service or jQuery dependency.

## Administration

Open **ATP** in the WordPress admin menu. Under **Desktop position**, choose **Footer — scrolls with the page**, **Top of viewport — always visible**, or **Bottom of viewport — always visible**. The viewport means the visible browser window. Bottom placement remains visible while scrolling, uses the chosen left/right side, and opens both controls upward. Set its **Bottom gap** from 0–100px; the side gap is 5px. The **Top gap** applies only to top-of-viewport mode. Footer placement remains the default for new installations, preserving existing behavior until an administrator changes it.

The **Tablets and phones** override is independent: when enabled, widths of 1024px or less always use the bottom-right corner with 5px gaps. When disabled, the selected desktop placement applies at every width. Existing settings retain their meaning, including the legacy `screen` value for top-of-viewport placement. Updating the plugin does not overwrite site settings or translation pairs.

Manage English/Spanish page pairs in the same screen. Enabled mappings work in both directions and follow the site's home URL. Only mapped translated pages show the language switcher.

Activation copies compatible records from the old Custom Translation Navigation Links table once, including disabled records. The original table is preserved and subsequently operates independently. Existing `[spanish_page_link]` and `[english_page_link]` shortcodes remain supported. Disable the old translation and Pojo accessibility plugins when using ATP to avoid duplicate controls. Installation itself does not deactivate other plugins.

## Visitor controls

- Increase/decrease text in 10% steps, from the original size through 150%.
- High contrast, grayscale images, underline links, and readable font.
- Reset restores original font sizes and clears saved preferences.
- Settings persist on this browser when enabled; blocked or malformed storage does not prevent use.
- Buttons expose expanded/pressed states, closed panels are removed from keyboard navigation, and Escape restores focus to the launcher. Outside clicks or keyboard navigation away close panels.
- Widget labels follow the current English/Spanish mapping. Language links open the corresponding existing pages; this plugin does not machine-translate text.

Text enlargement applies to site text, preserves original inline font styles, recalculates at responsive resize, and also supports inserted content. The widget, admin toolbar, and dialogs are excluded from text enlargement. Grayscale targets media rather than filtering the whole page, preserving fixed header/widget positioning. The contrast and font modes are presentation preferences, not automatic content repairs or an accessibility certification.

## Data and licensing

Mapping CRUD uses administrator capability checks, nonces, escaping, and prepared database operations. Settings use the WordPress Settings API. There are no public write endpoints. Deactivation leaves mappings/settings intact. The mapping implementation is adapted from the local Creative Way Solutions translation plugin. The local GPL Pojo/Elementor legacy code was reviewed for essential toolbar behavior; its account, scan, telemetry, and subscription modules are not bundled. License: GPL-3.0-or-later.

## Checks

Run `php tests/integration.php /absolute/path/to/wp-load.php` against a development WordPress site where ATP is active. Temporary mapping fixtures run inside a transaction and are rolled back. Browser checks use Playwright: `ATP_URL=https://your-local-site/ ATP_PLAYWRIGHT=/path/to/playwright ATP_CHROME=/path/to/chrome node tests/browser.cjs`. `tests/languages.cjs` additionally requires an enabled homepage translation pair. It checks both directions and an unmapped route. The browser tests dismiss the preexisting local Thrive overlay if present; they do not change its configuration.

## Tablet and phone placement

ATP includes **Tablets and phones: fix widget to the bottom-right corner**, enabled by default. At viewport widths of 1024px or less it overrides the normal placement and anchors the widget 5px from the bottom and right edges. Menus open upward. Wider screens retain the configured footer/screen placement and side. Disable the checkbox to use the normal placement at every width. Changes in orientation/viewport width automatically update placement without reloading.
