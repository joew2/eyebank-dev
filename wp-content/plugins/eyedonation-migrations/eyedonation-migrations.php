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
require DIR.'/migrations/034_install_become_a_donor.php';
require DIR.'/migrations/035_install_board_of_directors.php';
require DIR.'/migrations/036_install_contact_us.php';
require DIR.'/migrations/037_install_faqs.php';
require DIR.'/migrations/038_update_about_hero_v4.php';
require DIR.'/migrations/039_givingtuesday_attribution.php';
require DIR.'/migrations/040_update_homepage_support_photo.php';
require DIR.'/migrations/041_update_homepage_support_responsive.php';
require DIR.'/migrations/042_update_homepage_support_crop.php';
require DIR.'/migrations/043_install_member_menu.php';

require DIR.'/migrations/044_install_for_physicians.php';
require DIR.'/migrations/045_install_medical_advisory_board.php';

require DIR.'/migrations/support/EditablePages.php';
require DIR.'/migrations/046_install_responsibilities_and_benefits.php';
require DIR.'/migrations/047_install_privacy_policy.php';
require DIR.'/migrations/048_install_young_ambassador_scholarship_criteria.php';
require DIR.'/migrations/049_install_stem_scholarship.php';
require DIR.'/migrations/050_install_medal.php';
require DIR.'/migrations/051_install_registration_tissue_request.php';
require DIR.'/migrations/052_install_member_profile.php';
require DIR.'/migrations/053_install_enter_new_password.php';
require DIR.'/migrations/054_install_user_downloads.php';
require DIR.'/migrations/055_install_transplant_surgery.php';
require DIR.'/migrations/056_install_thank_you_your_submission_has_been_sent.php';
require DIR.'/migrations/057_install_other_give_now.php';

// Admin presentation only; leaves released migration checksums and page payloads unchanged.
require DIR.'/admin-layout.php';
require DIR.'/bulk-admin.php';
