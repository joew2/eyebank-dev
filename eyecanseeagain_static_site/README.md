# Eye Can See Again — static website

The complete standalone site is in this directory. It does not depend on WordPress, a database, the media library, a build tool, or external fonts. All photos, portraits, logos, CSS, and JavaScript are bundled locally.

## Preview and upload

From this directory, run:

```sh
python3 -m http.server 8765 --bind 127.0.0.1
```

Open http://127.0.0.1:8765/. Alternatively, preview at https://eyebank:8890/eyecanseeagain_static_site/ on the local WordPress development server.

Upload `index.html` and the entire `assets` directory together to any static web host. The upload ZIP has those files at its root. Serve over HTTP/HTTPS; opening `index.html` directly with `file://` prevents browsers from fetching the portrait JSON.

## Content and links

- Edit page copy in `index.html`.
- Edit all seven video destinations and the Give Now, donor signup, and Watch More Videos links in `assets/js/config.js`.
- The six gallery videos use the provided Vimeo URLs. Yolanda's unlisted-video hash is retained.
- Adam's Vimeo URL and the third-party Watch More Videos destination are pending. They open a placeholder message instead of an invented link.
- Give Now reuses the existing site's Blackbaud destination. Donor signup and the header/footer logo links point to the public Eye-Bank website.
- CSS uses Arial, pixel font sizes, and the existing theme's blue #0345ab, teal #009e9f, cyan #00b2e3, and pale blue #e9eef8. Footer logo, Donate Life logo, and EBAA accreditation were copied from the theme. Embedded SVGs are used for the play, giving, and social icons.
- The copyright year updates automatically.

The main content is capped at 1,050px; the centered header and hero are capped at 1,150px. Hero typography scales with its container rather than the viewport. The footer background remains full width.

## Videos and responsive layout

Desktop displays a three-column, six-video gallery. Mobile stacks all six stories so all supplied videos remain available. Buttons are keyboard accessible; play controls appear on hover/focus and remain visible on touch layouts.

A native modal dialog creates a Vimeo iframe only on a video click and requests autoplay. Browser and Vimeo policies can still require pressing Play. The dialog has a close button, Escape dismissal, focus restoration, backdrop dismissal, a Vimeo fallback link, and reduced-motion support. Closing it removes the iframe and stops playback immediately.

The mobile navigation contains Videos, Faces of Sight Restoration, and eyedonation.org. It opens and closes with a fade; desktop retains the mockup's direct parent-site link.

## Portrait loading

The first 10 portraits are written into the HTML in the mockup's order: Noemi, Nicholas, Griff, Domitila, Justin, Beni, Marsha-Ann, Jaclyn, Kelvin, Yolanda. Their labels are already embedded in the supplied photos; alt text supplies their names to screen readers.

The remaining 61 portrait records are in `assets/data/portraits.json`. That manifest is fetched only on the first View More Portraits click. Each click requests 10 additional images (the last batch contains one), preserves ordering, fades them in, and announces progress to screen readers without displaying loading/count text. There are no hidden image elements or background downloads for subsequent batches. An error preserves the current gallery and allows retrying. The control disappears once all 71 portraits are shown.

The initial grid uses five columns on desktop and two on mobile. All portraits retain their original aspect ratio and embedded captions.

## Verification

`tests/browser.cjs` exercises eight viewport sizes, no horizontal overflow, valid local images, no premature portrait manifest/images, exact 10-image batches, all 71 unique portraits, Vimeo IDs/private hash/autoplay parameter, closing/focus restoration, placeholder links, and mobile menu controls. Vimeo requests are stubbed in this deterministic interaction test; actual playback depends on the supplied Vimeo account settings and network access. A separate live test successfully loaded Yolanda’s player; some other players returned Vimeo’s connection-security restriction in the test environment. The original supplied links and Watch on Vimeo fallbacks are retained.

Run with a local HTTP preview server and Playwright installed:

```sh
PLAYWRIGHT_MODULE=/path/to/node_modules/playwright CHROME_PATH='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' node tests/browser.cjs
```

Set `SITE_URL` to use another preview URL. No external player or portrait requests are sent before a visitor interacts with the relevant control.
