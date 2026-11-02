# Forgot Password Boards migration

1. Install **12-custom-forgot-password-boards-1.0.0.zip** as a new plugin and activate **Custom Forgot Password Boards CWS**.
2. Open **Tools → Forgot Password Boards CWS**.
3. Check the backup agreement and click **Process all updates**. Keep the tab open until completion.
4. Clear the site cache and review `/password-boards/`.

This one ZIP includes the editable page, saved template and centered form styling. No theme update is required on live. The template is named **Forgot Password Boards — Editable Page**.

Uses the original Toolset forgot-password form, returns to `/member/`, and sends reset-email links to `/enter-new-password/`. Those existing destination pages and Toolset Views are required. The malformed legacy `wwww` failure domain is corrected. No password-reset request or email is sent by the migration itself.

Keep the plugin active for its authentication-page cache protection on the current live theme, and exclude this page from any external full-page cache. Existing header, navigation, footer, other pages and user records are retained.

Local installation completed on page 380388 at `https://Eyebank:8890/password-boards/`. The published page now uses `forgot-password-boards`; original before-images are retained in the plugin history. Both guest-form and signed-in-message rendering were verified with the installed Toolset plugin, without sending reset emails.
