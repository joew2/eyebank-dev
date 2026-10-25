<?php
/**
 * Title: EyeDonation Complete Homepage
 * Slug: eyedonation/homepage
 * Categories: featured
 * Block Types: core/post-content
 * Description: Restore the complete native homepage with local media, editable links, and current news. Insert into an empty page.
 * Inserter: yes
 */
defined('ABSPATH') || exit;
echo ed_homepage_resource_label(ed_link_homepage_news_heading(ed_update_hero_donor_link(ed_portable_homepage_content())));
