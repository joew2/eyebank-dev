<?php
define('WP_INSTALLING',true);require __DIR__.'/bootstrap.php';require_once ABSPATH.'wp-admin/includes/upgrade.php';
if(!is_blog_installed())wp_install('EyeDonation isolated rehearsal','rehearsal','rehearsal@example.invalid',true,'','LocalFixtureOnly_2026!');
update_option('stylesheet','eyedonation');update_option('template','eyedonation');update_option('active_plugins',['eyedonation-search/eyedonation-search.php','eyedonation-migrations/eyedonation-migrations.php']);echo "Isolated fixture tables ready. Original site tables untouched.\n";
