# Custom Thrive Compatibility Fix CWS — REQUIRED

**Keep this plugin active. Do not deactivate or delete it while Thrive landing pages remain in use.** It can be removed after those pages have been replaced with blocks and the site has been verified without Thrive.

## Install on live

1. Upload the ZIP under Plugins → Add New → Upload Plugin.
2. Activate **Custom Thrive Compatibility Fix CWS — REQUIRED**. It starts working immediately; there is no migration screen or Process button.
3. Deactivate the temporary utility plugin that prints a forced accessibility CSS link, if it is still active. This avoids duplicate widget CSS once normal enqueueing works again.
4. Purge server/page/CDN caches, then check `/es/`, the accessibility toolbar, an ordinary page and the Thrive editor.

## What it fixes

Thrive matches the active theme name against the entire stylesheet URL when it removes theme dependencies from a landing page. The `eyedonation` theme name also matches the `eyedonation.org` domain, causing plugin and WordPress styles to be removed as well.

This plugin uses Thrive's `tcb_remove_theme_css` filter to compare the stylesheet URL **path** against the actual active parent/child theme directories. It preserves plugin/WordPress styles while allowing Thrive to exclude genuine theme styles. Query strings, domain names, sibling directory names and dot segments do not create false matches. An earlier decision to keep a style is respected. Unknown or unclassifiable URLs retain the original decision.

It changes no pages, templates, theme settings, database records or Thrive files. It outputs no extra stylesheet links, affects no script queues and adds no editor/admin notices. Ordinary pages are unaffected because the filter only runs inside Thrive's removal pass. If Thrive is inactive, the filter is unused. Generated or CDN assets outside the active theme directory paths are retained rather than classified by hostname.

This corrects CSS removal; unrelated editor JavaScript, optimization or cache issues may still need separate investigation. The matching hook exists in both the installed Architect and Thrive Leads embedded editor copies.

## Verification

From the project root:

```sh
php wp-content/plugins/custom-thrive-compatibility-fix/tests/theme-css.php
```

The test executes Thrive's actual removal function with the real WordPress filter system and this plugin, comparing local/live hosts and asserting real theme removal plus plugin/core preservation. Additional cases cover child themes, CDN hosts, subdirectory installs, protocol/root-relative URLs, query strings, encoded paths, dot segments, malformed input and existing opt-outs. No database or live-site changes are made by the test.
