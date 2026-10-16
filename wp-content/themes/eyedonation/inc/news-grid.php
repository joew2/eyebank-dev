<?php
/** Public news batches reuse the homepage's native image, title, and excerpt blocks. */
defined('ABSPATH') || exit;
function ed_news_cursor_valid($cursor) {
    if ($cursor === '') return true;
    if (!preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\|([1-9]\d{0,18})$/D', $cursor, $match)) return false;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $match[1]);
    return $date && $date->format('Y-m-d H:i:s') === $match[1] && (int) $match[2] > 0;
}
function ed_news_batch($cursor = '') {
    if (!ed_news_cursor_valid($cursor)) return new WP_Error('ed_invalid_cursor', 'Invalid news cursor.', ['status'=>400]);
    $args = ['post_type'=>'post','post_status'=>'publish','has_password'=>false,'posts_per_page'=>10,'ignore_sticky_posts'=>true,'orderby'=>['date'=>'DESC','ID'=>'DESC'],'no_found_rows'=>true,'ed_news_before'=>$cursor];
    $where = static function ($sql, $query) use ($cursor) {
        if ($cursor === '' || $query->get('ed_news_before') !== $cursor) return $sql;
        global $wpdb;
        [$date, $id] = explode('|', $cursor);
        return $sql.$wpdb->prepare(" AND ($wpdb->posts.post_date < %s OR ($wpdb->posts.post_date = %s AND $wpdb->posts.ID < %d))", $date, $date, (int)$id);
    };
    add_filter('posts_where', $where, 10, 2);
    try { $query = new WP_Query($args); } finally { remove_filter('posts_where', $where, 10); }
    $posts = array_slice($query->posts, 0, 9);
    $cards = '';
    $template = '<!-- wp:group {"layout":{"type":"default"}} --><div class="wp-block-group"><!-- wp:post-featured-image {"isLink":true,"sizeSlug":"medium_large","className":"ed-news-image"} /--><!-- wp:post-title {"isLink":true,"level":3} /--><!-- wp:eyedonation/first-paragraph /--></div><!-- /wp:group -->';
    $parsed = parse_blocks($template)[0];
    foreach ($posts as $post) {
        $card = new WP_Block($parsed, ['postId'=>$post->ID,'postType'=>'post']);
        $cards .= '<li class="wp-block-post" data-post-id="'.esc_attr($post->ID).'">'.$card->render().'</li>';
    }
    $last = $posts ? end($posts) : null;
    return ['html'=>$cards,'count'=>count($posts),'hasMore'=>count($query->posts)>9,'cursor'=>$last?$last->post_date.'|'.$last->ID:''];
}
add_action('rest_api_init', static function () {
    register_rest_route('eyedonation/v1', '/news', [
        'methods'=>'GET','permission_callback'=>'__return_true',
        'args'=>['cursor'=>['type'=>'string','default'=>'','validate_callback'=>static fn($value)=>is_string($value)&&ed_news_cursor_valid($value)]],
        'callback'=>static fn($request)=>rest_ensure_response(ed_news_batch($request->get_param('cursor'))),
    ]);
});
add_action('init', static function () {
    wp_register_script('ed-news-grid', get_theme_file_uri('/assets/js/news-grid.js'), [], filemtime(get_theme_file_path('/assets/js/news-grid.js')), true);
    wp_register_script('ed-news-grid-editor', get_theme_file_uri('/assets/js/news-grid-editor.js'), ['wp-blocks','wp-element','wp-server-side-render'], filemtime(get_theme_file_path('/assets/js/news-grid-editor.js')), true);
    register_block_type('eyedonation/news-grid', [
        'api_version'=>3,'title'=>'Eye-Bank News grid','category'=>'theme','editor_script'=>'ed-news-grid-editor','supports'=>['html'=>false],
        'render_callback'=>static function () {
            $cursor = isset($_GET['ed-news-before']) && is_string($_GET['ed-news-before']) ? wp_unslash($_GET['ed-news-before']) : '';
            if (!ed_news_cursor_valid($cursor)) $cursor = '';
            $batch = ed_news_batch($cursor);
            if (is_wp_error($batch)) return '';
            wp_enqueue_script('ed-news-grid');
            $id = wp_unique_id('ed-news-grid-');
            $out = '<div '.get_block_wrapper_attributes(['class'=>'ed-news ed-news-archive','data-news-endpoint'=>rest_url('eyedonation/v1/news'),'data-news-cursor'=>$batch['cursor']]).'>';
            $out .= '<ul id="'.esc_attr($id).'" class="wp-block-post-template ed-news-grid-list">'.$batch['html'].'</ul>';
            if (!$batch['count']) $out .= '<p>No stories are available yet.</p>';
            if ($batch['hasMore']) {
                $out .= '<div class="ed-news-load-controls"><button type="button" class="ed-news-load-more" aria-controls="'.esc_attr($id).'" hidden>Load more</button>';
                $fallback = add_query_arg('ed-news-before', $batch['cursor'], get_permalink()).'#'.$id;
                $out .= '<noscript><a class="ed-news-load-more" href="'.esc_url($fallback).'">Load more stories</a></noscript></div>';
            }
            return $out.'<p class="ed-news-load-status" role="status" aria-live="polite" aria-atomic="true"></p></div>';
        },
    ]);
});
