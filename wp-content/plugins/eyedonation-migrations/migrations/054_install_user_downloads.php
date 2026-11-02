<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
register_editable_page_migration(json_decode('{"number":54,"slug":"user-downloads","title":"User Downloads","kind":"account","dependencies":["filemanager","wpv-login-form"]}',true),__FILE__);
