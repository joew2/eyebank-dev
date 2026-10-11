<?php
/**
 * Plugin Name: Custom Plugin - Donor Family Survey Two Column Formatter
 * Description: Replaces specific Gravity Forms divs on the page with slug "temp-donor-family-survey".
 * Version: 1.0
 * Author: 
 */

add_action('template_redirect', function() {
    if (!is_page('donor-family-survey')) {
        return; // Exit if not the target page
    }

    ob_start(function($content) {
        // Regex patterns (case-insensitive, dotall so newlines are matched)
        $patterns = [
            // Replace <div id="field_81_34"...>...</div>
            '/<div\s+id\s*=\s*["\']field_81_34["\'][^>]*>.*?<\/div\s*>/is',
            // Replace <div id="field_81_35"...>...</div>
            '/<div\s+id\s*=\s*["\']field_81_35["\'][^>]*>.*?<\/div\s*>/is',
            // Replace <div id="field_81_36"...>...</div>
            '/<div\s+id\s*=\s*["\']field_81_36["\'][^>]*>.*?<\/div\s*>/is'
        ];

        // Replacement strings
        $replacements = [
            '<div class="dfs-wrapper"> <div class="dfs-col-1">',
            '</div> <div class="dfs-col-2">',
            '</div> </div>'
        ];

        // Perform replacements in order
        $modified = preg_replace($patterns, $replacements, $content);

        return $modified;
    });
});
