# Search indexing fix — EyeDonation Search 0.1.1

The live Edwin article was publicly accessible while a fresh public search still returned zero results. Without authenticated live database access, the exact state of that site's index/cron cannot be confirmed. The previous rebuild button merely queued background work; enhanced search required derived index text even for title matches.

## Update only the Search plugin

1. Download **11-eyedonation-search-0.1.1.zip**.
2. Upload through Plugins → Add New → Upload Plugin, and choose **Replace current with uploaded** for **EyeDonation Search**.
3. Open **Search** in the live admin sidebar.
4. Click **Rebuild public search text** and keep the tab open. It processes the index directly in batches and shows indexed/total/missing counts and any errors.
5. After completion, clear the site cache and test the public search for Edwin.

This is an update to the existing plugin, not another migration plugin. No page-content migration is needed. Settings and exclusions are preserved. Title/excerpt matches can work before indexing completes; body-only matches require the completed index.

If it still returns no results, report the new counts and any error. Also confirm that excluded post types does not contain `post` and excluded IDs does not contain the live Edwin post's ID. The update deliberately retains configured exclusions.

Only local tests were run; installing this ZIP and starting the rebuild on live remain your steps.
