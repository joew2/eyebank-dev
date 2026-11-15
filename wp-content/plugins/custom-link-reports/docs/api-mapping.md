# Short.io API mapping

Verified against the official Short.io reference on November 13, 2026. No live key was available, so response mapping is documentation-verified but not account-verified.

- Authentication: private key is the raw `Authorization` header value; no Bearer prefix.
- Domains: `GET https://api.short.io/api/domains`, using `limit` (maximum 300) and `offset`. The adapter accepts either the documented list or a `domains` envelope defensively.
- Links: `GET https://api.short.io/api/links?domain_id=...&limit=150&pageToken=...`. Response fields used: `links`, `nextPageToken`, optional `count`; per-link `idString`/`id`, `title`, `path`, `secureShortURL`/`shortURL`, `originalURL`, and `DomainId`.
- Analytics: `POST https://statistics.short.io/statistics/link/{linkId}` with JSON `period=custom`, IANA `tz`, explicit `startDate`/`endDate`, `clicksChartInterval=day`, and `skipTops=false`.
- Analytics fields: `totalClicks`, optional `humanClicks`, `clickStatistics.datasets[].data[].x/y`, `referer[].referer/score`, `country[].countryName/score`, `browser[].browser/score`, and `os[].os/score`.

Missing optional fields are represented as unavailable, never silently converted to zero. API calls are restricted to the two fixed HTTPS hosts, TLS verification remains enabled, and redirects are disabled so credentials cannot be forwarded.

Date-boundary behavior remains to be compared with a real Short.io account. The request uses the documented date-only custom fields and records the requested inclusive dates and timezone.

