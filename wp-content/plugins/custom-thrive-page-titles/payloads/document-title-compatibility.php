<?php
/** Ensure PHP renderers such as Thrive landing pages receive a document title. */
defined('ABSPATH') || exit;

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
}, 1);

