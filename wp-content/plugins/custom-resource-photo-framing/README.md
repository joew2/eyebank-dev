# Custom Resource Photo Framing CWS

Version 1.0.0 · Creative Way Solutions · GPL-3.0-or-later · WordPress 6.6+ / PHP 8.1+.

## Install

Upload this as a new plugin and activate **Custom Resource Photo Framing CWS**. Open **Tools → Resource Photo Framing CWS**, check the backup agreement, and click **Process all updates**. Clear site/CDN caches afterward. Activation alone does not apply the migration. No theme ZIP update is required.

## Framing

Only the homepage Resources card linking to `/transplant-recipients-donor-families/` is targeted. The original photograph is 900 × 781px.

- Desktop: enlarge the cover photograph by 5% and request a 30px rightward shift.
- Mobile (781px and below, matching the stacked Resources layout): enlarge by 5%, request a 30px rightward shift and a 20px downward shift.
- Bound the offsets to keep the image filling the card and preserve the man's upper/left hairline with a small clearance. On narrow phone cards there is not enough spare crop to shift a 5%-enlarged image the full requested pixel amount; the shift is capped rather than exposing an empty strip. Top alignment is also corrected as needed to keep his head in view.

Existing hover/focus enlargement remains, with its origin moved to the image's top-left so the head does not get clipped during hover. Reduced-motion behavior remains supported. Card sizes, headings, links and other Resources photographs are unchanged.

CSS size-container units calculate the image geometry from the actual card size. The card's old padding is transferred to its inner content so the heading retains its spacing while the photograph can use the full card dimensions. Current browsers supporting size containers use the adjustment; older browsers keep their existing framing through an `@supports` guard.

## Storage and recovery

The migration appends one scoped, marked block to WordPress Additional CSS for EyeDonation. Options `cwsrpf_job_v1` and `cwsrpf_history` retain job progress, actor and original/updated CSS. Unrelated CSS is preserved. Repeated completed runs preserve later edits; interrupted work can resume with the same button. Requests require administrator/editing capabilities, a nonce and explicit agreement. Execution leases coordinate with the existing migration tools.

This does not replace page content, media files, header/footer, navigation, forms, posts or settings outside the scoped CSS. The applied change remains after deactivation. A database backup or recorded CSS before-image supplies recovery; there is no automatic rollback.

The matching stylesheet/module is also present in the local EyeDonation theme for future theme packages. The standalone live plugin includes its own migration payload and does not require that theme update.

## Verification

Browser fixtures use the real 900 × 781 source photograph and theme styles at 1920, 1440, 1200, 1024, 782, 781, 768, 600, 430, 394, 375 and 320px widths. They check 5% enlargement, image coverage without empty edges, visible man's head, correct desktop/mobile offset parameters, unchanged card sizes and other cards, and hover behavior. Desktop/mobile visual previews were inspected.

Isolated migration tests verify CSS-only scope, original before-images, unrelated CSS/page preservation, repeat-run preservation and one-button UI. Applied locally with the same CSS writer. No online-site changes occur while packaging.
