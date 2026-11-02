# Custom Mobile Resource Image CWS — 1.2.0

Self-contained desktop and mobile Resources image migration. Includes desktop-v2 and mobile-v3 originals. No earlier image migration is required. Handles both the original Cover card and the previously migrated responsive Group card.

Upload this ZIP as an update to Custom Mobile Resource Image CWS. Activate, open Tools → Mobile Resource Image CWS, check the backup agreement and click Process all updates. Activation alone does not change content. This version uses a fresh processing job, so a failed or completed earlier mobile-only run will not skip the combined update.

Imports both JPEGs into Media Library with checksum verification and deduplication. Replaces only the recipients/families Resources card, preserving its heading/link and all other homepage sections. Removes earlier framing and mobile offset rules. Desktop uses its supplied full crop. Mobile uses the v3 photo with cover and center top: excess crops from the bottom. Heights match Research Portal and Transplant Portal: 420px through 781px, 300px at 600px and below.

Original page content and Additional CSS are saved in migration history and page revisions. Retries preserve completed updates and later edits. After processing, deactivate this migration plugin; do not process the older photo/height migrations again.

Version 1.2.0 adds the same 1.035 image zoom and 0.3s ease transition as the neighboring cards, on hover and keyboard focus. Reduced-motion preferences disable the zoom. A new processing job applies the CSS even after version 1.1.0 completed.
