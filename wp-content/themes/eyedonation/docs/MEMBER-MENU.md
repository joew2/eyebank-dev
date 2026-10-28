# Logged-in member menu

Open **Appearance → Member Menu** to edit the strip in the WordPress Site Editor. Select the **Member Access** Navigation block to change labels, destinations, and order using the same native controls as other menus. Member Profile links to `/member-profile/`, Request Transplant Tissue to `/request-transplant-tissue/`, and Request Research Tissue to `/request-research-tissue/`. Downloads remains a placeholder.

The Member Menu template part contains the editable Member Access caption, navigation, and a dynamic Member welcome and logout block. That block displays the current user's first name (falling back to display name), initial, and WordPress's secure logout link. No personal information is stored in the template.

The strip renders only for logged-in users, below the header on every theme template that uses the Header part. Guests receive no member menu markup. It scrolls with the page independently of the sticky header. Desktop uses a single row; smaller screens use a two-column grid beneath the greeting.

Icons are inline SVG code in `inc/member-menu.php`. Native navigation link Advanced CSS classes select each icon: `ed-member-profile`, `ed-member-downloads`, `ed-member-transplant`, and `ed-member-research`. Retain these classes when editing existing links.

For another installation, install the matching theme and migrations plugin, then run **Tools → Site Migrations → 043 — Native member menu**, previewing first and taking a recoverable backup. It creates the named native navigation and editable template part and preserves subsequent editorial changes. The theme also includes file-based defaults in `parts/member-menu.html` when no saved template part exists.

Logged-in responses use WordPress no-cache headers. Any external full-page cache must continue its normal exclusion of authenticated WordPress requests.
