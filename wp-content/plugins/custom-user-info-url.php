<?php
/*
Plugin Name: Custom Plugin - User Info URL Plugin
Description: Appends the currently logged-in user's email and full name to the URL as query parameters on pages containing "request-transplant-tissue".
Version: 1.0
Author: Your Name
*/

// Function to append user info to the URL
function append_user_info_to_url($url) {
    // Check if user is logged in
    if (is_user_logged_in()) {
        // Get user information
        $current_user = wp_get_current_user();

        // Concatenate first_name and last_name into full_name with a space between them
        $full_name = $current_user->first_name . ' ' . $current_user->last_name;

        // Append user info to the URL
        $url = add_query_arg(
            array(
                'user_email' => $current_user->user_email,
                'full_name' => $full_name,
            ),
            $url
        );

        // Remove original first_name and last_name parameters
        $url = remove_query_arg(array('first_name', 'last_name'), $url);
    }

    return $url;
}

// Hook into the init action to modify the URL
function modify_url_with_user_info() {
    // Check if the current page URL contains "request-transplant-tissue"
    if (strpos($_SERVER['REQUEST_URI'], 'request-transplant-tissue') !== false) {
        // Get the current URL
        $current_url = home_url($_SERVER['REQUEST_URI']);

        // Modify the URL with user info
        $modified_url = append_user_info_to_url($current_url);

        // Redirect to the modified URL
        if ($current_url !== $modified_url) {
            wp_redirect($modified_url);
            exit;
        }
    }
}
add_action('init', 'modify_url_with_user_info');
