# Theme-bundled landing pages and media

The current Home, About Us, Transplant Recipients & Donor Families, Our History, Leadership, Employment, Volunteer, and About Donation layouts are available as reusable theme patterns and independent saved-layout templates. Their static page photographs are bundled in the theme. The homepage now uses `assets/portable-pages/main_hero_photo_v4.jpg`, with its focal point at the bottom center; any vertical cover crop comes from the top.

`assets/portable-pages/manifest.json` maps legacy homepage/About upload paths to bundled files and records their SHA-256 hashes. Existing About photos reuse the files already included in `assets/about-hero-v2/`. The other recent pages retain their dedicated bundled photo directories. Home defaults now resolve directly from theme files and do not query the Media Library. Native image selections remain editable.

To restore a layout on another installation, insert its EyeDonation pattern into an empty page or use its Saved Layout template. The current Home and About patterns use bundled media. Migration **027_bundle_page_media** updates static image references on existing recent pages while preserving their text, links, forms, and news blocks. Preview/apply it in Tools → Site Migrations after saving a recoverable database backup. Completed runs preserve later edits. Earlier migration payloads are retained unchanged for checksum/history integrity; apply 027 after older page installers when migrating an existing site.

Dynamic news cards still use the featured images and excerpts of their individual posts. Those images belong to the post content rather than the landing-page design. The Volunteer form remains managed by Gravity Forms, as documented separately.
