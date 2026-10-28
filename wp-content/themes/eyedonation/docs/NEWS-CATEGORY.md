# News category scope

Homepage Eye-Bank News and the About Donation archive display only published, password-free posts assigned to **About Donation**. The category is resolved by slug `about-donation`, with a name fallback, rather than a database-specific ID. Child categories alone do not qualify. If the category is absent, these sections return no stories instead of showing unrelated posts.

The shared filter applies to the homepage's native `ed-news` Query Loop and all marked `ed_news_batch` queries, including initial archive rendering, REST Load More, the editor preview and the JavaScript-free next-batch fallback. Existing ordering, three-post homepage size, nine-post archive batches and date/ID cursors remain unchanged. Other queries are unaffected. No content migration is required; deploy the matching theme update.

Verification: all 60 local matching stories were checked across seven batches, with no duplicates or unrelated posts. REST next-batch results matched the expected category ordering. Homepage query results matched the newest three category posts. An unrelated Query Loop retained its original query constraints.

## News-card publication dates

Homepage and archive cards show their own publication date directly below the title, before the excerpt, using `F jS, Y` (for example, August 12th, 2026). Styling is Arial, 12px, italic, #727272. The same component renders dates for REST Load More batches. Existing suppression of native date blocks on other page/post templates is unchanged.
