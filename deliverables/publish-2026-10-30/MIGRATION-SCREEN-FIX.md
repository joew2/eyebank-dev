# Migration screen correction — October 30, 2026

## Update just one plugin

Install `06-eyedonation-migrations-2026-10-30-admin-fix1.zip` through Plugins → Add New → Upload Plugin. Choose **Replace current with uploaded**, then return to Tools → Site Migrations and refresh. No theme or other plugin update is needed for this fix. Activation/update does not run migrations.

The 50 later installer panels were incorrectly attached to WordPress's `admin_footer` hook, which runs outside the main content column. This correction renders them inside the content column, preserves panel 007, and wraps long preview values. It preserves all existing migration scripts, checksums, stored plans and history. The plugin's internal release remains 0.1.0 because the existing preflight requires it to match the theme release.

## What the supplied live screenshot shows

- The active theme is already `eyedonation` 0.1.0.
- Preview has been saved; this does not mean any page replacement ran.
- 002 reports `Navigation dependency incomplete: primary`. The older 001 menu creation has not run. This is a workflow dependency, not a missing plugin. Do not run 001 merely to clear this warning: its original menu payload contains historical placeholder destinations. Later navigation workflows and the theme's default navigation must be considered when choosing the menu steps.
- 003 reports `Media support_photo: missing`. Its expected original upload is `2026/10/support_our_sight-saving_programs_photo.jpg`. The other eight entries shown are resolved.
- 004/006 can appear ready in the old preview table even while their declared dependencies remain incomplete. The runner still checks dependencies before execution. Ready does not mean all earlier steps can be skipped.

## Address the missing photo and get the next preview

1. Download the accompanying original `support_our_sight-saving_programs_photo.jpg`. Its SHA-256 was verified against the existing migration manifest.
2. On the destination, first search Media Library for that exact filename. If it is absent, upload this original through Media → Add New. The expected attachment path is `2026/10/support_our_sight-saving_programs_photo.jpg`; with dated uploads enabled, an October 2026 upload normally uses that folder. If WordPress renames the file or uses a different folder, do not assume the preflight has been satisfied.
3. Click the top **Preview Changes** again. This only saves preview/planning data. Confirm whether the `support_photo` row now resolves. A physical file alone is not enough: the legacy preflight requires a matching Media Library attachment and original-file checksum. If it remains missing/ambiguous or reports a checksum difference, provide the new message rather than bypassing the check.
4. Confirm Settings → Reading selects the existing intended static homepage. Find **Install updated editable homepage — migration 014** (the donor-panel homepage installer) and use its **Preview homepage installation** button. Its underlying action is `edm_install_donor_homepage_preview`. Do not click Install yet. The historical 014 installer still resolves original homepage Media Library assets; the theme's newer portable patterns use bundled media, but that does not remove 014's original preflight requirements.
5. Share the resulting preview/status or use Download Report for the main table. The next execution steps should be selected from the destination's results. Do not run 003 or all historical migrations just to make every old table row green. The complete current homepage also has later portable-media/support updates; 014 alone is not the complete final homepage deployment sequence.

This fix does not connect to or change the live site. No content migrations were executed during its development.

## Verification

An offline integration test using WordPress's real hook system rendered all 52 main/installer sections, moved all 50 footer panels, retained an unrelated footer callback, and rejected unexpected database writes. A local browser check with WordPress admin CSS passed at 1440px (expanded and collapsed sidebar) and 390px, with no horizontal overflow or clipped action buttons, including a long preview value. The screenshot's live site has not been browser-tested remotely.
