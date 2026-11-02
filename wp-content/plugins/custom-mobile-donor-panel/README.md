# Custom Mobile Donor Panel CWS

Version 1.0.0 · Creative Way Solutions · GPL-3.0-or-later · WordPress 6.6+ / PHP 8.1+.

Install as a new plugin, activate **Custom Mobile Donor Panel CWS**, then open **Tools → Mobile Donor Panel CWS**. Check the backup agreement and click **Process all updates**. Clear the site/CDN cache afterward. Activation alone does not change the site's CSS. No theme ZIP or homepage-content migration is required.

## Change

At the theme's mobile/tablet breakpoint (1024px and below), proportionally scale the entire homepage hero donor panel to **80%** using a single CSS transform. The logo, copy, arrow, padding, border radius and card all scale together, without reflowing or rewriting the content.

Anchor the panel's bottom-right corner **12px above the hero's lower edge**, instead of the earlier 60px inset. This lowers the smaller card away from the child's face. Keep the card anchored to the hero rather than to the viewport. Normalize the independent hover-scale property on mobile so it cannot distort the requested 80% scale. Desktop behavior above 1024px is unchanged.

## Scope and recovery

This is a CSS-only migration. It appends a marked, scoped block to WordPress Additional CSS for EyeDonation, preserving unrelated styles. It does not edit homepage content, the hero photograph/crop, signup copy, destination link, header, navigation, other pages or user data.

Independent options `cwsmdp_job_v1` / `cwsmdp_history` store progress, actor and original/updated CSS. Leases coordinate with the other migration processors. Requests require administrator/editing capabilities, a nonce and the explicit agreement. Interrupted requests can resume. Completed runs preserve subsequent CSS edits and do not append duplicates. Recovery uses the retained CSS before-image or database backup, not automatic rollback.

The applied CSS persists after deactivation, so this migration plugin may be deactivated after verifying the change. The local EyeDonation theme also includes the same scoped stylesheet for future theme packages, but the standalone live migration does not require that theme update.

## Verification

Real-asset browser fixtures use the current hero photo and actual theme styles. At widths 320, 375, 390, 394, 600, 768 and 1024px, the card, logo, text and arrow each measured 80% of their earlier width/height; right edge aligned with the hero, bottom inset was 12px, and scrolling confirmed hero-relative positioning. At 1025 and 1440px, measurements remained unchanged. Visual inspection confirmed the card is below the child's face. Tests cover preservation of unrelated CSS/page content, before-images, idempotence and the one-button admin screen.

Applied locally using the migration's normal writer, with unrelated legacy plugins omitted only for that isolated CLI invocation. Saved activation settings were unchanged. No live-site updates are performed by building this ZIP.
