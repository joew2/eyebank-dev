<?php
defined('ABSPATH') || exit;
foreach (['setup','post-metadata','post-title-context','assets','management','news','news-grid','about-donation','givingtuesday','history','testimonial','social-icons','footer','footer-navigation','mobile-header','about','about-hero','recipients-families','our-history','history-layout','leadership','employment','volunteer','volunteer-layout','editor','navigation','pattern-media','hero-update','donor-panel','hero-donor-link','approved-links','portable-pages','placeholders'] as $module) require __DIR__ . '/inc/' . $module . '.php';
