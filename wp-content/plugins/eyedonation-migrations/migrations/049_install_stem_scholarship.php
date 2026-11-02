<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
register_editable_page_migration(json_decode('{"number":49,"slug":"stem-scholarship","title":"STEM Scholarship","kind":"scholarship","dependencies":["wpv-view","stem-scholarship-winners"]}',true),__FILE__);
