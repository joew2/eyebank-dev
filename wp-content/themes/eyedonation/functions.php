<?php
defined('ABSPATH') || exit;
foreach (['setup','assets','management','news','history','testimonial','social-icons','editor','navigation','pattern-media','hero-update','donor-panel','placeholders'] as $module) require __DIR__ . '/inc/' . $module . '.php';
