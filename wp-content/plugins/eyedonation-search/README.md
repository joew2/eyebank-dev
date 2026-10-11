# EyeDonation Search 0.1.0

Permanent public search functionality, independent of the EyeDonation Site Migrations plugin. Install alongside EyeDonation theme. Activation queues only derived public indexing; saved search settings are preserved. Deactivation leaves settings/history/derived data and returns the theme to native search.

Administrator configuration: top-level Search menu. Set independent enhanced/filters/highlight toggles, page size 1–50 and exclusions. Ordered groups use labeled JSON: `{"news":{"label":"News","enabled":true,"types":["post"]}}` is a syntax example only, not an approved project mapping. `terms` maps taxonomy slugs to term IDs; `ancestors` lists page IDs. Specific mappings win over generic type rules. Missing IDs/types/terms are rejected. The release ships no fabricated groups.

Public API: `EyeDonation\Search\settings()`, `validate_settings($value)`, `update_settings($value)`, `request_rebuild()`, `rebuild_batch()`. Settings are stored in nonautoloaded eds_settings. Rebuild jobs/leases use eds_rebuild_* options and a shared job across activation/manual/migration requests. Derived readable text and page ancestry use _eds_public_text/_eds_ancestors metadata. Rebuilds process 30 IDs per batch; ensure WordPress cron runs. No arbitrary shortcodes/content filters, private metadata or third-party adapters are indexed.

Theme search integration replaces only core Group marked ed-search-experience through pre_render_block before native fallback Query rendering. The main WordPress query retains SQL eligibility, totals and pagination. It searches public published non-password-protected posts/pages/publicly searchable types, with exclusions before totals, AND/quoted/minus queries, title relevance and stable date/ID ordering. Group filtering is a scoped SQL CASE, not a list of source-site IDs. Search form, filters and pagination work without JavaScript. No query logging or external service.

Cross-package behavior tests and current verification are documented in docs/VERIFICATION.md. Group mapping UI uses advanced JSON; provide an owner walkthrough. Native editor and live-copy rehearsal gates remain pending.
