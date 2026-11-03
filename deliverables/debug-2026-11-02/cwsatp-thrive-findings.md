# Spanish homepage: accessibility CSS and Thrive diagnosis

Investigated November 2, 2026. Read-only investigation; no plugin, theme, database, or live-site settings were changed. Primary evidence: local source, read-only local page metadata, a public live HTML request, and an isolated execution of Thrive’s actual CSS-removal function.

## Finding

**High-confidence root cause: Thrive’s theme-CSS remover matches the theme name against the entire stylesheet URL, including its hostname.** The active local theme is `eyedonation`; the live domain is `www.eyedonation.org`. Consequently, same-domain plugin CSS can be mistaken for theme CSS on landing pages that disable theme dependencies. The local hostname `Eyebank:8890` does not contain `eyedonation`, explaining why the same code can work locally.

The exact removal is in `wp-content/plugins/thrive-visual-editor/inc/functions.php:2108–2119`, specifically:

```php
if ( apply_filters( 'tcb_remove_theme_css',
    strpos( $src, $theme ) !== false || strpos( $src, $stylesheet_dir ) !== false,
    $src
) ) {
    wp_deregister_style( $handle );
}
```

At line 5636, Thrive attaches this function to `wp_print_styles` at `PHP_INT_MAX` when the page’s theme-dependency setting requests stripping theme CSS. WordPress fires that hook before printing the queued styles (`wp-includes/functions.wp-styles.php:57`). Raising enqueue priority to 99 therefore does not help: the stylesheet is still removed later, just before printing.

The embedded Architect copy in `wp-content/plugins/thrive-leads/tcb/inc/functions.php:2108` contains the same logic. Local Architect and Thrive Leads both identify themselves as version 11.2.0.1. Live plugin version and runtime configuration have not been authenticated or verified.

## A. Accessibility plugin

- Original enqueue: `wp-content/plugins/custom-accessibility-translation/includes/widget.php:71`, handle `cwsatp`, no dependencies, version from the CSS file modification time.
- Hook: `wp_enqueue_scripts` at default priority **10** (`:68–73`).
- Only condition is the plugin’s global `enabled` setting (`:69`). No language, homepage, page-ID, template, or Thrive condition.
- JavaScript is separately enqueued for the footer at `:72`; widget markup is output on `wp_footer` beginning at `:74`. This explains why markup and JS survive while CSS disappears.
- No self-dequeue/deregister or special landing-page registration was found in this plugin.

## B. Why `/es/` triggers it

Read-only local metadata for page **387262**:

| Setting | Value |
|---|---|
| Theme / stylesheet | `eyedonation` / `eyedonation` |
| Thrive landing page | `blank_v2` |
| `tve_disable_theme_dependency` | `1` |
| `tcb_editor_enabled` | `1` |
| `tcb_editor_disabled` | empty |

`TCB_Landing_Page::should_remove_theme_css()` reads `tve_disable_theme_dependency` and exposes the `tcb_theme_dependency` filter in `landing-page/inc/class-tcb-landing-page.php:2028–2036`.

This is a landing-page/theme-dependency issue, not a Spanish-language rule. Any other page using the same stripping mode and URLs can be vulnerable. Ordinary pages need not take that path.

## Reproduction

`reproduce-thrive-css.php` extracts and executes the actual local Thrive function in a small isolated harness. It does not bootstrap WordPress or access its database. Only the hostname changes between the two unpatched cases:

| URL hostname | Handles deregistered |
|---|---|
| `eyebank:8890` | `ed-base` (actual theme CSS) |
| `www.eyedonation.org` | `cwsatp`, `cwsatp-forced`, `tve_landing_page_blank_v2`, `ed-base`, `wp-core` |
| Either hostname with a path-based compatibility filter | `ed-base` only |

The fixture asserts all four expected results. This reproduces the enqueue-force failure, both missing stylesheet handles, and the local/live difference without assuming a different plugin version or cache configuration.

## C. Other interference and whitelist distinction

A search across local plugin/theme PHP found no explicit `cwsatp` dequeue and no custom `tcb_remove_theme_css` override. Other dequeue code exists, chiefly admin/editor integrations or unrelated handles; no stronger explanation for this pair of missing styles was found. No local `mu-plugins`, `advanced-cache.php`, or `object-cache.php` drop-in was present during inspection. This does not inventory live-only plugins or infrastructure.

Thrive also strips inline `<style>` blocks in `TCB_Landing_Page::strip_head_css()` (`class-tcb-landing-page.php:973`), using `tcb_lp_strip_css_whitelist` at line 1013. **That whitelist concerns inline style elements, not external stylesheet `<link>` tags.** Adding `cwsatp` to it alone would not repair this external-link problem. If an optimizer converts external styles to inline styles, the inline whitelist becomes an additional consideration.

The editor’s main-frame setup removes hooks in `inc/classes/class-tcb-editor.php:82–92`. That is editor-frame behavior and should not be mistaken for the cause of missing styles on the ordinary public `/es/` response.

## Current public live output and caching

A read-only request to https://www.eyedonation.org/es/ returned HTTP 200 with:

- Widget HTML and widget JS present.
- A direct `cwsatp-forced-css` link present, consistent with the temporary workaround described in the brief.
- No normal `cwsatp-css` or `tve_landing_page_blank_v2-css` link.
- Thrive frontend JavaScript and the `tve_lp_blank_v2` wrapper present.
- `X-Nginx-Upstream-Cache-Status: EXPIRED` and `X-Server-Powered-By: Engintron`.

The cache header is evidence of a server-side caching layer, not proof that the whole site is uncached or that every request is fresh. However, caching is not needed to reproduce the failure, and the failing pattern was present on this response. Clear server/page/CDN caches after a permanent fix; clearing cache alone will not correct the substring comparison.

## D. Broader builder problem

The same broad removal can suppress Thrive’s other same-domain queued styles, so restoring only `widget.css` leaves a plausible explanation for the remaining visual breakage.

Important caveat: **the local `landing-page/templates/css/blank_v2.css` is zero bytes.** Its absent link is a useful diagnostic marker, but that file alone cannot explain missing visual rules in this local version. Live contents may differ. Thrive’s optimized/generated CSS and remaining layout styles must also be checked.

The local page’s actual landing-page content exists: `tve_updated_post_blank_v2` contains **35,328 bytes**. An empty unsuffixed `tve_updated_post` is normal here: `tve_get_post_meta()` adds the landing-template suffix (`inc/functions.php:3235`). There is no evidence from this check that the local page lost its Thrive content.

Missing CSS can make a page appear broken; it does not establish a JavaScript exception or explain every editor failure. An authenticated Architect editor session, live console/network errors, live page metadata, and live generated-asset state were not available for verification. After correcting CSS classification, retest public layout and the authenticated editor separately. If problems remain, inspect failed JS/CSS requests and console errors, then compare/regenerate Thrive’s optimized assets and purge caches.

## Permanent fixes, ranked

1. **Preferred complete fix: a small Thrive compatibility filter maintained in an existing CWS plugin.** Use `tcb_remove_theme_css` to classify the URL’s path against the actual parent/child theme directory paths. Strip real theme styles, preserve plugin and WordPress styles. The isolated reproduction verifies this approach fixes the hostname collision. Keep the normal `cwsatp` enqueue; no raw link or additional permanent utility plugin is needed. This should address more than just the widget. Test both LP and normal pages, public and editor contexts, before deploying. Do not edit Thrive vendor files, where an update could overwrite the change.
2. **Smallest widget-only change:** return `false` from `tcb_remove_theme_css` for this plugin’s exact `widget.css` URL/path. This restores the widget but does not repair other Thrive/plugin styles misclassified by the same bug, so it is incomplete for the broader symptom.
3. **Thrive setting/filter workaround:** turn off stripping of theme dependencies for this page, or return `false` from `tcb_theme_dependency` scoped to the affected LP. This avoids the removal pass, but restores the theme’s CSS too and may change the landing page’s design. Inline-style stripping is separate. Confirm the live UI wording/version before changing the setting.
4. **Last-resort CSS bypass:** move the proven direct stylesheet link into the existing accessibility plugin, scoped and deduplicated, rather than retaining a separate force plugin. This bypasses the queue and works around the symptom; it does not solve broader style loss. Do not use an unconditional raw link sitewide or continue loading duplicate forced/normal widget styles after the main fix.

After deploying a tested compatibility fix: remove/deactivate the temporary forced-link utility, purge all cache layers, verify one widget stylesheet, verify other Thrive styles, test the widget and editor, and check an ordinary page for regressions. No new migration or re-creation of `/es/` is indicated by the evidence so far.

Run the isolated reproduction from the project root:

```sh
php deliverables/debug-2026-11-02/reproduce-thrive-css.php
```
