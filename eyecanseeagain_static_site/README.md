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
- Adam’s video uses the supplied Vimeo URL and private hash. Watch More Videos links directly to https://www.eyedonation.org/videos/.
- Give Now reuses the existing site's Blackbaud destination. Donor signup and the header/footer logo links point to the public Eye-Bank website.
- CSS uses Arial, pixel font sizes, and the existing theme's blue #0345ab, teal #009e9f, cyan #00b2e3, and pale blue #e9eef8. Footer logo, Donate Life logo, and EBAA accreditation were copied from the theme. Embedded SVGs are used for the play, giving, and social icons. The Give Now SVG is copied directly from the WordPress theme’s `assets/icons/give-now.svg`.
- The copyright year updates automatically. The donor signup hover matches the theme’s pale background change and stronger shadow; footer social links use WordPress’s 1.1× hover enlargement, also available on keyboard focus. Portrait headings, introductory text, grid controls, and the story invitation use tighter desktop and mobile spacing.

The main content is capped at 1,050px; the desktop hero copy aligns with the header logo using the same responsive side gutter. The desktop header spans the viewport with responsive 24–72px side padding; the mobile header retains its existing layout. The hero photo stretches across the viewport, with its donor button anchored to the right edge. Its desktop gradient remains translucent at the left edge and strengthens behind the copy. The footer background remains full width.

The desktop hero uses the tighter `hero-photo-desktop-v2.jpg` crop at its natural 1700×759 ratio up to a 740px desktop height cap, reducing the previous height. On larger screens the photo crops from the bottom while keeping the top of Adam’s head visible. The supplied quote SVG is embedded directly in the HTML, independently positioned at the same top offset as the heading block (with 20px extra desktop top spacing) and with a right gutter equal to the copy’s left gutter. Mobile retains its original photo, embedded quote, and layout; the desktop SVG is hidden there.

## Videos and responsive layout

Desktop displays a three-column, six-video gallery. Mobile stacks all six stories so all supplied videos remain available. Buttons are keyboard accessible; play controls appear on hover/focus and remain visible on touch layouts.

A native modal dialog creates a Vimeo iframe only on a video click and requests autoplay. Browser and Vimeo policies can still require pressing Play. The dialog has a close button, Escape dismissal, focus restoration, backdrop dismissal, reduced-motion support, and a player sized to fit both viewport width and height, including clearance for the close button. Closing it removes the iframe and stops playback immediately.

The mobile navigation contains Videos, Faces of Sight Restoration, and eyedonation.org. It opens and closes with a fade; desktop retains the mockup's direct parent-site link.

## Portrait loading

The first 10 portraits are written into the HTML in the mockup's order: Noemi, Nicholas, Griff, Domitila, Justin, Beni, Marsha-Ann, Jaclyn, Kelvin, Yolanda. Their labels are already embedded in the supplied photos; alt text supplies their names to screen readers.

The remaining 61 portrait records are in `assets/data/portraits.json`. That manifest is fetched only on the first View More Portraits click. Each click requests 10 additional images (the last batch contains one), preserves ordering, fades them in, and announces progress to screen readers without displaying loading/count text. There are no hidden image elements or background downloads for subsequent batches. An error preserves the current gallery and allows retrying. The control disappears once all 71 portraits are shown.

The initial grid uses five columns on desktop and two on mobile. All portraits retain their original aspect ratio and embedded captions.

## Verification

`tests/browser.cjs` exercises eight viewport sizes, no horizontal overflow, valid local images, no premature portrait manifest/images, exact 10-image batches, all 71 unique portraits, Vimeo IDs/private hash/autoplay parameter, closing/focus restoration, the Watch More Videos destination, and mobile menu controls. `tests/video-viewport.cjs` additionally checks all seven video dialogs and their close controls at eight screen sizes, including short laptop and phone landscape viewports. Vimeo requests are stubbed in this deterministic interaction test; actual playback depends on the supplied Vimeo account settings and network access. A separate live test successfully loaded Yolanda’s player; some other players returned Vimeo’s connection-security restriction in the test environment. The original supplied video links are retained; no Watch on Vimeo message is displayed.

Run with a local HTTP preview server and Playwright installed:

```sh
PLAYWRIGHT_MODULE=/path/to/node_modules/playwright CHROME_PATH='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' node tests/browser.cjs
```

Set `SITE_URL` to use another preview URL. No external player or portrait requests are sent before a visitor interacts with the relevant control.
