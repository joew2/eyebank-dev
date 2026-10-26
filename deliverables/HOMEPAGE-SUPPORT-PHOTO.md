# Captioned homepage support photograph

The support section uses the supplied 1700×1003 desktop and 900×531 mobile JPEGs bundled under `assets/homepage-support-v4/`, with source paths and checksums in `manifest.json`.

An editable Group and Image replace the previous cropping Cover block. Each image follows its complete supplied proportions at all widths. A responsive `<picture>` source chooses the mobile file at 781px and below; the stored content remains a native editable Image block. If the editor replaces the desktop image independently, the renderer leaves that replacement intact. On desktop (782px and above), the unchanged editable support panel remains over the photograph with a 60px clearance from the bottom caption. At 781px and below, the panel follows the photograph in normal flow so the small image and caption remain unobstructed. The heading retains the requested cyan color, and the existing Give Now destination and other homepage content are preserved.

The homepage reusable pattern includes the same update. For another installation, install matching theme and migrations packages and run **042 — Updated homepage support crops**. The migration backs up the previous content in its history and preserves subsequent edits on repeat runs.

Verification passed at 1920, 1440, 1200, 782, 768, 390 and 320px. The images render at their complete supplied ratios, the desktop panel clears the bottom caption, and the mobile panel follows the image. All seven support-section blocks validate in WordPress. Both supplied JPEGs match the bundled files byte for byte; other homepage blocks and support-panel text/link were verified unchanged. Repeated migration runs preserve editorial changes.

The final tighter crops reduce image height while preserving the complete supplied artwork and captions. Responsive picture selection remains at 781px and below.
