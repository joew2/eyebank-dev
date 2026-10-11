<?php
/*
Plugin Name: Custom Plugin - Disable Layout Metaboxes for Creators
Description: Removes the layout metaboxes for users with the 'creator' role in the Genesis theme.
*/

// Hook into the after_setup_theme action
add_action('after_setup_theme', 'disable_layout_metaboxes');

function disable_layout_metaboxes() {
    // Check if the current user has the 'creator' role
    if (current_user_can('creator')) {
        // Remove the layout metaboxes
        remove_theme_support('genesis-inpost-layouts');
        remove_theme_support('genesis-archive-layouts');
    }
}

function remove_revolution_slider_meta_boxes() {
    remove_meta_box( 'mymetabox_revslider_0', 'page', 'normal' );
    remove_meta_box( 'mymetabox_revslider_0', 'post', 'normal' );
    remove_meta_box( 'mymetabox_revslider_0', 'YOUR_CUSTOM_POST_TYPE', 'normal' );
}

add_action( 'do_meta_boxes', 'remove_revolution_slider_meta_boxes' );