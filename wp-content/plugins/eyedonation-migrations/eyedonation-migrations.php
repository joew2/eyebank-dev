<?php
/**
 * Plugin Name: EyeDonation Site Migrations
 * Description: Explicit versioned deployment changes. Never seeds content on activation.
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Requires at least: 6.6
 * Text Domain: eyedonation-migrations
 */
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
const VERSION='0.1.0';
const DIR=__DIR__;
require DIR.'/src/Operations.php';
require DIR.'/src/Runner.php';
require DIR.'/src/Admin.php';
// Intentionally no activation, uninstall, frontend, or automatic migration hook.
require DIR.'/migrations/007_native_homepage.php';

require DIR.'/migrations/008_primary_navigation.php';

require DIR.'/migrations/009_restore_homepage.php';

require DIR.'/migrations/010_install_homepage.php';

require DIR.'/migrations/011_update_hero.php';

require DIR.'/migrations/012_install_updated_homepage.php';

require DIR.'/migrations/013_update_donor_panel.php';
require DIR.'/migrations/014_install_donor_homepage.php';
require DIR.'/migrations/015_update_footer.php';
require DIR.'/migrations/016_install_about.php';
require DIR.'/migrations/017_update_about_hero.php';
require DIR.'/migrations/018_install_updated_about.php';
require DIR.'/migrations/019_install_recipients_families.php';
require DIR.'/migrations/020_install_our_history.php';
require DIR.'/migrations/021_install_leadership.php';
require DIR.'/migrations/022_install_employment.php';
require DIR.'/migrations/023_install_volunteer.php';
require DIR.'/migrations/024_reflow_our_history.php';
require DIR.'/migrations/025_reflow_volunteer.php';
require DIR.'/migrations/026_install_about_donation.php';
require DIR.'/migrations/027_install_givingtuesday.php';
require DIR.'/migrations/027_bundle_page_media.php';
require DIR.'/migrations/028_update_about_hero_v3.php';
require DIR.'/migrations/029_install_sign_up.php';
require DIR.'/migrations/030_update_givingtuesday_copy.php';
require DIR.'/migrations/031_update_givingtuesday_hashtag.php';
require DIR.'/migrations/032_install_cornea_recipients.php';
require DIR.'/migrations/033_install_donor_families.php';
