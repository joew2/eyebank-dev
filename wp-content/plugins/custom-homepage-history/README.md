# Custom Homepage History CWS — 1.4.0

Install and activate. Open Tools → Homepage History CWS, check the backup agreement and click Process all updates. Activation alone changes nothing. No earlier history migration is required.

Imports bundled desktop-v3 (1305 × 532) and mobile-v3 (533 × 698) JPEGs into Media Library, with checksum verification and deduplication. Updates only the static homepage’s history section, preserving its editable heading, paragraph, About Us link and other homepage sections. Original font sizes are retained; “sight to” is joined with a nonbreaking space.

Desktop displays the complete photo at its natural ratio, with the narrower white panel and separate button. At widths through 900px the mobile layout overlays the content at the bottom. Mobile displays the complete mobile photo at its natural ratio without cropping the top or bottom, with the white panel and button aligned to the bottom and 30px below the button.

Original page and Additional CSS are retained in cwshh_history and page revisions. A new processing job applies this version even after earlier versions completed. Completed retries preserve later edits. Deactivate after success; content, images and saved styles remain.

Version 1.4.0 also stacks the three Resources cards through 900px and matches their heights: 420px from 601–900px, 300px at 600px and below. Extends the recipients/families mobile photo source to 900px, preserving its hover effect. Native history image blocks select the mobile photo through 900px without requiring updated theme PHP on live.
