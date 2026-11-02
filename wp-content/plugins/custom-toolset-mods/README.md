# Custom Toolset Mods CWS

Version 1.0.0 · Creative Way Solutions · GPL-3.0-or-later.

A standalone utility plugin for site-specific Toolset modifications. Directory/entry point: `custom-toolset-mods/custom-toolset-mods.php`. PHP code uses the `CWS\ToolsetMods` namespace. Additional Toolset modifications can be added as separate modules under `includes/`; the plugin is not limited to Views.

## Current modification

Preserves the original theme's user-query sorting for these Toolset View IDs:

| View ID | View | Sort |
|---|---|---|
| 266 | Board of Directors | `last_name`, ascending |
| 274 | Medical Advisory Board | `last_name`, ascending |
| 267 | Honorary Directors | `last_name`, ascending |

Registers `wpv_filter_user_query` at priority 100 with three arguments. For these three Views it sets `meta_key = last_name`, `orderby = meta_value`, and `order = ASC`. Other query arguments and other Views are left unchanged. Numeric and string IDs are supported. The query behavior matches the supplied original code, including WordPress's handling of users without a last-name metadata record.

## Install

Upload the ZIP under Plugins → Add New → Upload Plugin, then activate **Custom Toolset Mods CWS**. No migration, Process button, settings or index rebuild is needed. Sorting takes effect when Toolset next builds these user queries. Clear existing site/View caches if previously rendered output remains visible.

Toolset must retain the same View IDs on the destination; this plugin does not recreate or renumber Views or modify user records. If Toolset is inactive, this filter remains dormant. Deactivation removes the plugin's sorting contribution without deleting data. No theme files are edited, and the namespaced callback avoids collisions with the original theme's function names.
