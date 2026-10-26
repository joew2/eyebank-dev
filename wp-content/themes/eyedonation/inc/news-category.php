<?php
/** Shared category restriction for the homepage query and paginated news archive. */
defined('ABSPATH') || exit;
function ed_about_donation_news_query($args){
    // Resolve by slug/name so deployments do not depend on a local term ID.
    $term=get_term_by('slug','about-donation','category')?:get_term_by('name','About Donation','category');
    if(!$term){$args['post__in']=[0];return $args;} // Never fall back to unrelated posts.
    $required=['taxonomy'=>'category','field'=>'term_id','terms'=>[(int)$term->term_id],'include_children'=>false];
    $existing=$args['tax_query']??[];
    $args['tax_query']=$existing?['relation'=>'AND',$existing,$required]:[$required];
    return $args;
}
add_filter('query_loop_block_query_vars',function($args,$block){
    if(in_array('ed-news',preg_split('/\s+/',$block->parsed_block['attrs']['className']??''),true))return ed_about_donation_news_query($args);
    return $args;
},20,2);
add_action('pre_get_posts',function($query){
    // ed_news_batch uses this marker for initial, REST Load More and no-JS batches.
    if(($query->query['post_type']??'')!=='post'||!array_key_exists('ed_news_before',$query->query))return;
    $args=ed_about_donation_news_query($query->query_vars);
    $query->set('tax_query',$args['tax_query']??[]);
    if(isset($args['post__in']))$query->set('post__in',$args['post__in']);
});
