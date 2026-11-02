<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
register_editable_page_migration(json_decode('{"number":51,"slug":"registration-tissue-request","title":"Tissue Request Registration","kind":"account","dependencies":["cred_user_form","request-tissue-registration"]}',true),__FILE__);
