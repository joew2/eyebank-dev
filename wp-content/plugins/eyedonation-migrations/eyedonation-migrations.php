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
