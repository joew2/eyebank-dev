<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
register_editable_page_migration(json_decode('{"number":52,"slug":"member-profile","title":"Member Profile","kind":"account","dependencies":["cred_user_form","user-profile","wpv-login-form"]}',true),__FILE__);
