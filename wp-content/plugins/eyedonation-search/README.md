# EyeDonation Search 0.1.1

Permanent public search functionality, independent of the EyeDonation Site Migrations plugin. Install alongside EyeDonation theme. Activation queues only derived public indexing; saved search settings are preserved. Deactivation leaves settings/history/derived data and returns the theme to native search.

Administrator configuration: top-level Search menu. Set independent enhanced/filters/highlight toggles, page size 1–50 and exclusions. Ordered groups use labeled JSON: `{"news":{"label":"News","enabled":true,"types":["post"]}}` is a syntax example only, not an approved project mapping. `terms` maps taxonomy slugs to term IDs; `ancestors` lists page IDs. Specific mappings win over generic type rules. Missing IDs/types/terms are rejected. The release ships no fabricated groups.

Public API: `EyeDonation\Search\settings()`, `validate_settings($value)`, `update_settings($value)`, `request_rebuild()`, `rebuild_batch()`. Settings are stored in nonautoloaded eds_settings. Rebuild jobs/leases use eds_rebuild_* options and a shared job across activation/manual/migration requests. Derived readable text and page ancestry use _eds_public_text/_eds_ancestors metadata. Rebuilds process 30 candidate IDs per batch. The Search screen processes batches directly through authenticated AJAX while the tab is open, so manual rebuilds do not require cron. Background cron remains supported. Revisions and unindexed attachments are skipped; old ineligible index entries are cleaned. No arbitrary shortcodes/content filters, private metadata or third-party adapters are indexed.

Theme search integration replaces only core Group marked ed-search-experience through pre_render_block before native fallback Query rendering. The main WordPress query retains SQL eligibility, totals and pagination. It searches public published non-password-protected posts/pages/publicly searchable types, with exclusions before totals, AND/quoted/minus queries, title relevance and stable date/ID ordering. Group filtering is a scoped SQL CASE, not a list of source-site IDs. Search form, filters and pagination work without JavaScript. No query logging or external service.

Cross-package behavior tests and current verification are documented in docs/VERIFICATION.md. Group mapping UI uses advanced JSON; provide an owner walkthrough. Native editor and live-copy rehearsal gates remain pending.


## 0.1.1 — Reliable destination indexing

Update the existing EyeDonation Search plugin, then open **Search** and click **Rebuild public search text**. Keep the tab open until the displayed counts show completion. Existing jobs resume from their checkpoint, expired leases are reclaimed atomically, and errors are displayed. The screen reports how many public searchable records have index entries and how many are missing. The old button only queued cron work and did not demonstrate that it had completed.

Public titles and excerpts now match directly alongside indexed text, so a missing destination index no longer hides a published article whose title contains the query. Body-only matches still require indexing. Public eligibility, passwords, configured exclusions, groups, negative/quoted terms, pagination and existing settings remain in effect. No author restriction is introduced and no private metadata or third-party fields are added to the index.

`PLUGIN_VERSION` is 0.1.1; `VERSION` remains 0.1.0 as the migration API compatibility identifier for existing deployment preflights. This update does not migrate page content, alter authors or reset search settings.

Regression checks: real SQL against uniquely named disposable MySQL tables (removed after the test), empty-index title/excerpt matches, body matches after rebuilding, private/draft/password exclusions, negative/quoted terms, candidate batching, stale-index cleanup, active/expired locks, failed-query reporting and resume state. Administrator/nonce checks and real admin markup are tested separately; local browser tests simulate batches, progress and error/retry responses. These checks do not establish the live database's current state.
