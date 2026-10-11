# Isolated synthetic test harness

Use a fresh temporary directory, never the original site's wp-content. Set EYE_DONATION_WP_ROOT to the original local WordPress root. Copy these helper files into that directory; copy the **three new package directories only** into its wp-content/themes and wp-content/plugins. The bootstrap reads local DB constants in memory from wp-config.php but uses the separate fixed `edrehearsal_` prefix and isolated wp-content. No credentials are written to these helpers or reports. Do not run this on a database that already uses that prefix for real data.

Run install.php with the local PHP/MAMP socket configured, then `php /path/to/eyedonation-migrations/tests/integration.php /absolute/fixture-directory`. The integration suite refuses other table prefixes and modifies only copied fixture payloads. It requires the supplied originals at the manifest paths under the source root's wp-content/uploads. Run reset.php only to drop the synthetic edrehearsal_ tables. It never drops tables of another prefix. Reinstall for a fresh repeat.

Serve router.php with PHP's development server bound to 127.0.0.1:8899. Use locally installed Playwright/Chrome for the browser scripts. The router is test-only and is not a replacement for a real WordPress web-server configuration. The bootstrap blocks outbound HTTP/mail and disables cron; migration requests drive index batches.

Native editor context is not emulated by the lightweight validation route. This harness does not replace fresh live-backup rehearsal or production-like admin endpoint tests. Stop the loopback server and drop synthetic fixture tables when done. Preserve original development data.
