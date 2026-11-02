# Simple migration plugin — October 30, 2026

1. Download **06-eyedonation-migrations-2026-10-30-simple-process.zip**.
2. In WordPress, use Plugins → Add New → Upload Plugin and select that ZIP.
3. Choose **Replace current with uploaded**. Activate the plugin if it is inactive.
4. Open **Tools → Site Migrations** and refresh the page. You should see one agreement checkbox and **Process all updates**.
5. Check the agreement and click **Process all updates**. Keep the tab open while the progress list runs.
6. When it finishes, clear the site cache and review the pages. If it lists a missing form/plugin, correct that issue and click the same button again. Completed work is retained.

No manual preview steps, numbered buttons or original support-photo upload are needed for this workflow. Updating/activating the plugin itself still does not run content updates. Only this migration plugin needs replacement; the theme and other ZIPs supplied earlier are unchanged.

The job covers the current converted pages/designs and supporting menu/template/search updates, using the existing destination users, forms, Views, media and files. It does not overwrite the destination database with the development database. It replaces saved EyeDonation header/footer overrides with the supplied designs and retains before-images. It preserves completed steps on subsequent clicks; the button is also the resume/retry control.

Tests passed locally for queue processing, interruption/resume, dependency errors, current homepage assets and the one-button browser flow. No migrations were run against the online site by this update.
