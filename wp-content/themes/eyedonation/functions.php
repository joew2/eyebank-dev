<?php
defined('ABSPATH') || exit;
foreach (['setup','assets','management','news','history','testimonial','social-icons','footer','mobile-header','editor','navigation','pattern-media','hero-update','donor-panel','placeholders'] as $module) require __DIR__ . '/inc/' . $module . '.php';
