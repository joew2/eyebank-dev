<?php
/**
 * Plugin Name: Custom Plugin - Donor Family Survey Two Column Formatter
 * Description: Formats Gravity Forms fields into two columns for English and Spanish donor family surveys.
 * Version: 1.4
 * Author:
 */

add_filter('gform_get_form_filter', function ($form_string, $form) {
    if (empty($form['id'])) {
        return $form_string;
    }

    $form_id = (int) $form['id'];

    if ($form_id === 81) {
        $fields = ['field_81_34', 'field_81_35', 'field_81_36'];
    } elseif ($form_id === 83) {
        $fields = ['field_83_34', 'field_83_35', 'field_83_36'];
    } else {
        return $form_string;
    }

    $patterns = [
        '/<div\s+id=["\']' . preg_quote($fields[0], '/') . '["\'][^>]*>.*?<\/div\s*>/is',
        '/<div\s+id=["\']' . preg_quote($fields[1], '/') . '["\'][^>]*>.*?<\/div\s*>/is',
        '/<div\s+id=["\']' . preg_quote($fields[2], '/') . '["\'][^>]*>.*?<\/div\s*>/is',
    ];

    $replacements = [
        '<div class="dfs-wrapper"><div class="dfs-col-1">',
        '</div><div class="dfs-col-2">',
        '</div></div>',
    ];

    return preg_replace($patterns, $replacements, $form_string);
}, 10, 2);