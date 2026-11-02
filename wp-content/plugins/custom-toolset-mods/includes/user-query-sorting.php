<?php
/** Last-name ordering carried over from the original site's theme functions. */
namespace CWS\ToolsetMods;
defined('ABSPATH') || exit;

add_filter('wpv_filter_user_query', __NAMESPACE__ . '\\sort_selected_user_views', 100, 3);

function sort_selected_user_views($query_args, $view_settings, $view_id) {
    // Board of Directors, Medical Advisory Board, and Honorary Directors.
    if (in_array((string) $view_id, ['266', '274', '267'], true)) {
        $query_args['meta_key'] = 'last_name';
        $query_args['orderby'] = 'meta_value';
        $query_args['order'] = 'ASC';
    }

    return $query_args;
}
