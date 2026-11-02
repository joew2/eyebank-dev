# Custom Site Tracking CWS

Version 1.0.0. Author: Creative Way Solutions. Directory and entry point: `custom-site-tracking/custom-site-tracking.php`. PHP identifiers/options use `cwstr_`. License: GPL-3.0-or-later. Requires WordPress 6.2+ and PHP 7.4+.

## Inventory from the legacy site

The `eyebank` theme's PHP/JS files contain no direct tracking snippets. Genesis stores these in the database's `genesis-settings` option:

- `header_scripts`: Google Tag Manager container `GTM-NP6FHDK`, PostHog public project key (bundled in plugin defaults), and Facebook domain verification token.
- `footer_scripts`: the same GTM container's noscript iframe.
- No direct Facebook Pixel, standalone GA/GA4, or other tracker was found in the old theme sources or those settings. No `_genesis_scripts` page overrides were found.

The Google Material Symbols stylesheet in the same header setting is presentation code and is left alone. The plugin does not alter Analytify or other third-party plugin settings. Tags managed inside GTM require container account access to inspect; they are not embedded in the theme. In particular, Facebook verification is not a Facebook Pixel, and no Pixel ID has been invented.

## Install and manage

Upload the directory or install the ZIP and activate **Custom Site Tracking CWS**. Open **Settings → Site Tracking CWS**. The discovered IDs are supplied as defaults. Clear an individual ID to disable that integration; the checkbox disables both tracking scripts. Verification remains independent. Settings use the WordPress Settings API, administrator capability checks, nonces, strict ID validation and context-appropriate escaping. There is no arbitrary script editor.

GTM is inserted early in `wp_head`; its noscript iframe uses `wp_body_open`, with a once-only footer fallback for older themes. PostHog uses the existing asynchronous browser bootstrap, US host, `defaults: '2025-05-24'` and `person_profiles: 'identified_only'`. No WordPress user names/emails or new identification calls are added. The loader continues to fetch the vendor SDK as it did previously.

Tracking scripts run only when `wp_get_environment_type()` is `production`; set `WP_ENVIRONMENT_TYPE` to `local`, `development`, or `staging` on nonproduction installs. WordPress defaults to production if unset. Admin pages, feeds and previews do not emit tracking. The `cwstr_tracking_allowed` boolean filter can be connected to an existing consent integration; by default it preserves the legacy loading behavior and does not create a consent UI.

While this plugin is active, Genesis output filters suppress only the three known legacy snippets (including GTM's footer fallback), preserving the font link and unrelated content. Suppression also applies when tracking is disabled here, so the old theme cannot bypass the plugin's switch. Original database fields are retained unchanged for rollback. New or differently formatted snippets in Genesis or other plugins are not automatically removed. Keep tracking managed here going forward.

Deactivation stops this plugin's output and keeps settings. Switching back to Genesis with the plugin deactivated restores its original stored snippets. Deleting the plugin does not erase saved settings. Theme files do not need modification.

## Verification

`php tests/render.php` checks render output without running vendor code or sending analytics events. PHP lint and `node --check assets/posthog-bootstrap.js` check syntax. Legacy suppression was also checked against the actual local Genesis settings: all three migrated integrations removed, Material Symbols preserved. These tests do not confirm receipt of events by external accounts. After production activation and cache clearing, use Tag Assistant and PostHog's event tools to confirm delivery and review any tags configured inside GTM.

Implementation references: [WordPress Settings API](https://developer.wordpress.org/apis/settings/), [wp_body_open](https://developer.wordpress.org/reference/hooks/wp_body_open/), [Google Tag Manager installation](https://support.google.com/tagmanager/answer/14847097), [PostHog browser integration](https://posthog.com/docs/libraries/js).
