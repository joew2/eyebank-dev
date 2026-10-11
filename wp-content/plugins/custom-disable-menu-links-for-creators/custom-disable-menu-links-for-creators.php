<?php
/*
Plugin Name: Custom Plugin - Disable Menu Links for Creators
Description: Removes the Comments and Tools menu links for users with the 'creator' role.
*/

// Hook into the admin_menu action
add_action('admin_menu', 'disable_menu_links');

function disable_menu_links() {
    // Check if the current user has the 'creator' role
    if (current_user_can('creator')) {
        // Remove the Comments and Tools menu links
        remove_menu_page('edit-comments.php');
        remove_menu_page('tools.php');
        remove_menu_page('red_fm_manager');
    }
}
