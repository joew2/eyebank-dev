<?php
/**
 * Plugin Name: Custom Plugin - ES Donor Family Survey Two Column Formatter
 * Description: Replaces specific Gravity Forms divs on the page with slug "comparta-su-experiencia".
 * Version: 1.1
 * Author: 
 */

add_action('template_redirect', function() {
    if (!is_page('comparta-su-experiencia')) {
        return; // Exit if not the target page
    }

    ob_start(function($content) {
        // Regex patterns (case-insensitive, dotall so newlines are matched)
        $patterns = [
            // Replace <div id="field_83_34"...>...</div>
            '/<div\s+id\s*=\s*["\']field_83_34["\'][^>]*>.*?<\/div\s*>/is',
            // Replace <div id="field_83_35"...>...</div>
            '/<div\s+id\s*=\s*["\']field_83_35["\'][^>]*>.*?<\/div\s*>/is',
            // Replace <div id="field_83_36"...>...</div>
            '/<div\s+id\s*=\s*["\']field_83_36["\'][^>]*>.*?<\/div\s*>/is'
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
