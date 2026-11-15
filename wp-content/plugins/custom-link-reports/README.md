# Custom Link Reports CWS

Version 1.0.0. Requires WordPress 6.2+ and PHP 8.1+ with Sodium. Single-site or per-site multisite activation only.

## Setup

1. Activate **Custom Link Reports CWS**.
2. Open **Link Reports → Settings**, paste a private Short.io API key, save, then test the connection. The encrypted key is never redisplayed. Changing WordPress salts requires entering the key again.
3. Choose the default domain, timezone, recipients, branding, retention, and cache duration.
4. Open **Reports → Add report**, choose a domain, save, select links, and customize content, recipients, subject, and schedule.
5. Save, create a live preview, and send an explicit test. Tests go only to the entered address and carry a `[TEST]` prefix.
6. Enable and unpause the weekly schedule only after verification.

Scheduled events use one-off WP-Cron jobs calculated in the report timezone. WordPress runs them after the selected time when traffic reaches the site. The intended date window is frozen from the scheduled occurrence. A successful `wp_mail()` result means accepted for processing, not confirmed inbox delivery. Optional BCC recipients receive separately addressed private copies so their addresses never appear to regular recipients or to one another.

The plugin only reads Short.io domains, links, and aggregate analytics. It does not create, edit, or delete links and stores no click-level IP or visitor data.

## Storage

- Non-autoloaded `cws_clr_settings` option, including authenticated Sodium ciphertext.
- Private `cws_clr_report` posts with `_cws_clr_config` metadata.
- `{prefix}cws_clr_runs` for durable run identity, frozen configuration/aggregate snapshots, status, errors, and per-recipient acceptance state.
- Transients for bounded API caching and execution locks.

Deactivation cancels plugin jobs and preserves data. Uninstall deletes data only when **Delete plugin data on uninstall** is enabled.

## Security note

Encryption protects against casual database disclosure, not full server compromise. Keys and Authorization headers are excluded from HTML, JavaScript, logs, errors, and history.

## Verification limitation

Automated local tests use fixtures/mocks only. No live Short.io key or real email was used during development; live analytics parity and inbox delivery must be explicitly tested by an authorized administrator.

