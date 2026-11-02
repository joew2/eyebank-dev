# Giving Tuesday attribution links

The English `/givingtuesday/` page includes an editable paragraph at the bottom of the campaign appeal with links to `https://www.givingtuesday.org/` and the supplied Creative Commons Attribution 4.0 International License legal-code page. The visible site label is `GivingTuesday.org`. The small centered links wrap on mobile and use shared theme link colors. They sit 16px above the footer, separated from the thank-you by a 36px top margin.

The reusable Giving Tuesday pattern includes the same attribution. Migration **039 — Giving Tuesday attribution links** appends it to the English page without replacing the rest of the page. Repeated runs preserve editorial changes and do not duplicate the links. The original content is backed up in migration history.

The Spanish banner was not present in the inspected local campaign content or Spanish homepage. Its location must be identified before applying the corresponding banner change.

The current campaign has no hashtag. Its Thank You text is bold (700), 26px on desktop and 24px on phones. The Give Now button uses Blackbaud form `a1ed5d6a-a86e-4dc2-9f59-9c24c43b58de`. `inc/givingtuesday-current.php` applies these link/copy revisions to the reusable pattern and existing rendered campaign blocks without changing frozen migration payloads.
