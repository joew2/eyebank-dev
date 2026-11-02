<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
register_editable_page_migration(json_decode('{"number":53,"slug":"enter-new-password","title":"Enter New Password","kind":"account","dependencies":["wpv-reset-password-form"]}',true),__FILE__);
