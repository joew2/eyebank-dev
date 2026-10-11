# Asset checklist

The supplied SVG logo and original EBAA PNG are packaged under theme assets/images. No screenshot crops or Soroka images were used. Footer logo reverses the supplied mark with CSS; approve its final appearance or supply a separately approved reversed original.

The nine homepage source files remain destination Media Library assets rather than bundled imports. See migration payloads/assets.json for original-file SHA256 checksums, exact paths and fallback alt descriptions. Original/scaled-image metadata is supported; thumbnail variants are not separate matches. Destination IDs/URLs are derived locally. All nine resolved in the isolated fixture with IDs different from the local site.

| Key | Expected upload-relative path | Status |
|---|---|---|
| homepage_hero | 2026/10/main_hero_photo.jpg | Supplied |
| donate_life_logo | 2026/10/donate_life_logo.png | Supplied |
| resources_recipients_families | 2026/10/transplant_recipients_donor_families.jpg | Supplied |
| resources_research_portal | 2026/10/research_portal_photo.jpg | Supplied |
| resources_transplant_portal | 2026/10/transplant_portal.jpg | Supplied |
| history_desktop | 2026/10/worlds_first_eye_bank_photo_desktop.jpg | Supplied |
| history_mobile | 2026/10/worlds_first_eye_bank_photo_mobile.jpg | Supplied |
| eye_can_see_again | 2026/10/eye-can-see-again.jpg | Supplied; includes quotation in artwork |
| support_photo | 2026/10/support_our_sight-saving_programs_photo.jpg | Supplied; checksum and unique local attachment verified |

Testimonial video URL, donor/donation destinations, final captions and final alt text require approval. Attachment alt text wins over manifest fallback text. The quote contained in the testimonial artwork has an equivalent fallback alt description; retain an accessible equivalent if replacing the poster.

Inspect media preflight on the destination before processing. Missing or ambiguous attachments and unapproved checksum differences block homepage work. No automatic imports, duplicate attachments or live-file replacement are performed. Confirm actual live upload completion and paths; the owner's upload plan is not treated as verification.
