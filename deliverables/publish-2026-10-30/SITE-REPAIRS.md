# Separate repair plugin — October 30, 2026

Download **07-custom-site-repairs-1.1.0.zip** and install it as a new plugin through Plugins → Add New → Upload Plugin. Activate **Custom Site Repairs CWS**.

Open **Tools → Site Repairs CWS**, check the backup agreement, and click **Process all updates**. Keep that tab open. This does not use or require the original migration screen.

Repairs the two missing top portal buttons and their responsive sizing, User Downloads, Request Transplant Tissue and Request Research Tissue, and Sign Up (page 387956, Gravity Form 85). It preserves existing page IDs, form integrations and unrelated access settings. No theme ZIP update is needed.

If a required form/plugin is unavailable, the progress list identifies it. Correct that issue and click the same button to retry unfinished steps. Once complete, clear caches and review the pages. You may then deactivate this repair plugin; the applied repairs remain stored in WordPress.

Local tests passed. The online site and real form submissions have not been changed or tested by creating this ZIP.

Version 1.1.0 adds the original editable Sign Up layout and shortcode. The target page must use the `sign-up` URL slug and form 85 must exist. Completed earlier repairs are retained; the same button picks up the new Sign Up task.
