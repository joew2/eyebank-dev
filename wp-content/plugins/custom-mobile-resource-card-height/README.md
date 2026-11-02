# Custom Mobile Resource Card Height CWS

Version 1.1.0 · Creative Way Solutions · GPL-3.0-or-later · WordPress 6.6+ / PHP 8.1+.

Install after the supplied desktop/mobile photos have been processed by Custom Resource Card Images CWS. Activate this new plugin, open **Tools → Mobile Resource Card Height CWS**, check the backup agreement and click **Process all updates**. Clear site/CDN caches afterward. No manual preview, theme update or image upload is needed. Activation alone does not apply changes.

## Mobile-only change

For the recipients/families Resources card introduced by the image migration:

- At widths 601–781px: 420px card height, matching Research Portal and Transplant Portal in the stacked layout.
- At widths 600px and below: 300px height, matching those same cards on phones.
- Use the existing mobile image with `object-fit: cover` and `object-position: center -30px`. Exactly 30 CSS pixels are cropped from the top, and the remaining overflow is cropped from the bottom.

Desktop at 782px and above retains its current image, proportions and presentation. No photos are replaced or reimported. Homepage content, labels/links, other Resources cards, header/navigation and unrelated styles are unchanged.

## Storage and recovery

Appends a scoped, marked stylesheet to EyeDonation Additional CSS, after the image migration's existing styles. The independent `cwsmrch_job_v1` and `cwsmrch_history` options retain progress, actor and original/updated CSS. The same button resumes interrupted work; completed runs preserve later edits. The plugin requires administrator/editing capabilities, nonce and agreement; record/request leases coordinate writes with the other migrations.

Keep a recoverable backup; original CSS is also retained in the plugin history. There is no automatic rollback. After verification, this migration plugin can be deactivated because the applied CSS remains in WordPress. The local theme contains the same stylesheet/module for later theme packages.

## Verification

The actual migrated local homepage was checked at 1440, 1024, 782, 781, 768, 600, 430, 394, 375 and 320px. The two neighboring card heights matched at every mobile width. Desktop proportions and contain behavior remained unchanged. Existing responsive image selection was preserved; no horizontal overflow appeared. Visual inspection confirmed the phone crop favors the lower image area and leaves the faces visible.

Isolated tests cover CSS-only scope, before-images, preservation of unrelated styles/page content, idempotence and one-button UI. Browser tests with simulated AJAX cover processing, errors and retry. No live migration is run while building this ZIP.

Version 1.1.0 uses a new `mobile_resource_card_height_v2` checkpoint, so it runs even if the prior height adjustment has already completed. Install over the existing plugin and click Process all updates again. Previous before-images/history are retained.
