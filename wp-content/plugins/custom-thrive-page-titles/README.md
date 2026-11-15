# Custom Thrive Page Titles CWS

One-button migration for the EyeDonation block theme. It installs `inc/document-title-compatibility.php` and registers that module in the theme's `functions.php` loader. The module explicitly enables WordPress `title-tag` support.

This repairs a compatibility gap on true Thrive landing pages. Thrive bypasses WordPress's block-template canvas but suppresses its legacy title output when the active theme supports block templates. Without explicit `title-tag` support, neither renderer prints the document title.

Activation alone changes nothing. Open **Tools → Thrive Page Titles CWS**, confirm a recoverable backup, and click **Process all updates**. The migration stores the original file contents and checksums in the non-autoloaded `cws_tpt_history_v1` option, uses locked atomic writes, and verifies both installed files.

The migration does not modify page content, Thrive metadata, visible headings, permalinks, or SEO fields. After processing, purge all cache layers and confirm that `/es/` and `/spanish-nuestra-historia/` contain exactly one HTML `<title>` element.

