<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
register_editable_page_migration(json_decode('{"number":50,"slug":"medal","title":"Gift of Life Donor Medal","kind":"form","dependencies":["gravityform",35]}',true),__FILE__);
