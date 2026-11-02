<?php
namespace EyeDonation\Search;
defined('ABSPATH') || exit;
function settings(){return array_merge(['enhanced'=>true,'filters'=>true,'highlight'=>true,'per_page'=>6,'excluded_types'=>[],'excluded_ids'=>[],'groups'=>[]],get_option('eds_settings',[]));}
function clean_text($text){
 $text=preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is','',$text);
 $text=preg_replace('/<!--.*?-->/s','',$text);
 $text=preg_replace('/\[\/?[^\]]+\]/u','',$text);
 return trim(preg_replace('/\s+/u',' ',html_entity_decode(wp_strip_all_tags($text),ENT_QUOTES|ENT_HTML5,'UTF-8')));
}
function eligible($post){$type=get_post_type_object($post->post_type);return $post->post_status==='publish'&&!$post->post_password&&$type&&$type->public&&($type->publicly_queryable||$post->post_type==='page')&&!$type->exclude_from_search&&$post->post_type!=='attachment';}
function index_post($id){$p=get_post($id);if(!$p)return;if(!eligible($p)){delete_post_meta($id,'_eds_public_text');return;}update_post_meta($id,'_eds_ancestors',implode(',',array_merge([$id],get_post_ancestors($id))));update_post_meta($id,'_eds_public_text',clean_text($p->post_title.' '.$p->post_excerpt.' '.$p->post_content));update_option('eds_generation',(int)get_option('eds_generation')+1,false);}
/** Public types eligible for derived indexing; query-time exclusions remain separate. */
function index_types(){return array_keys(array_filter(get_post_types(['public'=>true,'exclude_from_search'=>false],'objects'),static fn($t)=>$t->name!=='attachment'&&($t->publicly_queryable||$t->name==='page')));}
function public_index_where($alias='p'){
 global $wpdb;$types=index_types();
 $clause=$types?$wpdb->prepare("$alias.post_type IN (".implode(',',array_fill(0,count($types),'%s')).')',$types):'1=0';
 return "$alias.post_status='publish' AND $alias.post_password='' AND ($clause)";
}
function request_rebuild(){
 if(!get_option('eds_rebuild_job'))add_option('eds_rebuild_job',['cursor'=>0,'processed'=>0,'started'=>time(),'status'=>'pending'],'',false);
 // An existing stalled job must be able to schedule a missing tick again.
 if(!wp_next_scheduled('eds_rebuild_tick'))wp_schedule_single_event(time()+1,'eds_rebuild_tick');
}
function rebuild_batch(){
 global $wpdb;
 $lease=['token'=>wp_generate_uuid4(),'expires'=>time()+60];
 if(!add_option('eds_rebuild_lock',$lease,'',false)){
  $old=get_option('eds_rebuild_lock');if(($old['expires']??PHP_INT_MAX)>=time())return false;
  $deleted=$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s",'eds_rebuild_lock',maybe_serialize($old)));
  wp_cache_delete('eds_rebuild_lock','options');
  if(!$deleted||!add_option('eds_rebuild_lock',$lease,'',false))return false;
 }
 try{
  $job=get_option('eds_rebuild_job');if(!$job)return true;
  // Skip revisions/attachments, but visit old indexed records to remove stale ineligible entries.
  $where=public_index_where('p');
  $ids=$wpdb->get_col($wpdb->prepare("SELECT p.ID FROM $wpdb->posts p WHERE p.ID>%d AND (($where) OR EXISTS(SELECT 1 FROM $wpdb->postmeta old_index WHERE old_index.post_id=p.ID AND old_index.meta_key='_eds_public_text')) ORDER BY p.ID ASC LIMIT 30",(int)$job['cursor']));
  if($wpdb->last_error)throw new \RuntimeException('Search index database query failed: '.$wpdb->last_error);
  foreach($ids as $id){index_post($id);$job['cursor']=(int)$id;$job['processed']=(int)($job['processed']??0)+1;}
  if(count($ids)<30){delete_option('eds_rebuild_job');update_option('eds_rebuild_completed',time(),false);wp_clear_scheduled_hook('eds_rebuild_tick');return true;}
  $job['status']='running';unset($job['error']);update_option('eds_rebuild_job',$job,false);
  if(!wp_next_scheduled('eds_rebuild_tick'))wp_schedule_single_event(time()+1,'eds_rebuild_tick');return false;
 }catch(\Throwable $e){if(!empty($job)){$job['status']='failed';$job['error']=$e->getMessage();update_option('eds_rebuild_job',$job,false);}throw $e;}
 finally{$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s",'eds_rebuild_lock',maybe_serialize($lease)));wp_cache_delete('eds_rebuild_lock','options');}
}
function rebuild_status(){
 global $wpdb;$where=public_index_where('p');
 $total=(int)$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts p WHERE $where");
 $indexed=(int)$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts p WHERE $where AND EXISTS(SELECT 1 FROM $wpdb->postmeta m WHERE m.post_id=p.ID AND m.meta_key='_eds_public_text')");
 $job=get_option('eds_rebuild_job');
 return ['status'=>$job?($job['status']??'pending'):'idle','running'=>(bool)$job,'indexed'=>$indexed,'total'=>$total,'missing'=>max(0,$total-$indexed),'cursor'=>(int)($job['cursor']??0),'processed'=>(int)($job['processed']??0),'error'=>$job['error']??'','completed'=>(int)get_option('eds_rebuild_completed',0)];
}
function terms($query){$query=mb_substr(trim(preg_replace('/\s+/u',' ',wp_strip_all_tags($query))),0,200);preg_match_all('/(-?"[^"]+"|-?[^\s"]+)/u',$query,$matches);return array_slice(array_map(function($v){$negative=str_starts_with($v,'-');return ['text'=>trim($negative?substr($v,1):$v,'"'),'negative'=>$negative];},$matches[0]),0,12);}
function group_for($post){$groups=settings()['groups'];$fallback='';foreach($groups as $slug=>$g){if(empty($g['enabled']))continue;
 if(!empty($g['terms']))foreach($g['terms'] as $tax=>$ids)if(has_term($ids,$tax,$post))return $slug;
 if($post->post_type==='page'&&!empty($g['ancestors']))if(array_intersect(array_merge([$post->ID],get_post_ancestors($post)),$g['ancestors']))return $slug;
 if(in_array($post->post_type,$g['types']??[],true)&&!$fallback)$fallback=$slug;
 }return $fallback;}
function prepare_query($q){if(is_admin()||!$q->is_main_query()||!$q->is_search()||!settings()['enhanced'])return;
 $s=settings();$q->set('s',mb_substr(trim((string)$q->get('s')),0,200));$restricted=$q->get('post_status');$q->set('post_status','publish');if($restricted&&!in_array('publish',(array)$restricted,true))$q->set('post__in',[0]);$q->set('has_password',false);$types=array_keys(array_filter(get_post_types(['public'=>true,'exclude_from_search'=>false],'objects'),fn($type)=>$type->name!=='attachment'&&($type->publicly_queryable||$type->name==='page')));$types=array_values(array_diff($types,$s['excluded_types']));$q->set('post_type',$types?:['__none']);$q->set('post__not_in',array_values(array_unique(array_merge((array)$q->get('post__not_in'),$s['excluded_ids']))));$q->set('posts_per_page',$s['per_page']);$q->set('ignore_sticky_posts',true);$q->set('orderby',['relevance'=>'DESC','date'=>'DESC','ID'=>'DESC']);$q->set('eds_enhanced',true);
 $selected=isset($_GET['group'])?sanitize_key(wp_unslash($_GET['group'])):'';
 $q->set('eds_group',$s['filters']&&isset($s['groups'][$selected])&&!empty($s['groups'][$selected]['enabled'])?$selected:'');
}
function search_sql($sql,$q){
 if(!$q->get('eds_enhanced'))return $sql;
 global $wpdb;$conditions=[];
 foreach(terms($q->get('s')) as $term){
  if($term['text']==='')continue;$like='%'.$wpdb->esc_like($term['text']).'%';
  // Public titles/excerpts must remain searchable while a destination index is missing or incomplete.
  $clause=$wpdb->prepare("($wpdb->posts.post_title LIKE %s OR $wpdb->posts.post_excerpt LIKE %s OR EXISTS (SELECT 1 FROM $wpdb->postmeta eds_text WHERE eds_text.post_id=$wpdb->posts.ID AND eds_text.meta_key='_eds_public_text' AND eds_text.meta_value LIKE %s))",$like,$like,$like);
  $conditions[]=($term['negative']?'NOT ':'').$clause;
 }
 return $conditions?' AND ('.implode(' AND ',$conditions).')':' AND 1=0';
}
function highlight($text,$query){if(!settings()['highlight'])return esc_html($text);$parts=[];foreach(terms($query) as $t)if(!$t['negative']&&$t['text']!=='')$parts[]=preg_quote($t['text'],'/');if(!$parts)return esc_html($text);$tokens=preg_split('/('.implode('|',$parts).')/iu',$text,-1,PREG_SPLIT_DELIM_CAPTURE);if($tokens===false)return esc_html($text);$safe='';foreach($tokens as $i=>$token)$safe.=($i%2?'<mark>':'').esc_html($token).($i%2?'</mark>':'');return $safe;}
function snippet($post,$query){$text=clean_text($post->post_excerpt.' '.$post->post_content);$pos=0;foreach(terms($query) as $t){if(!$t['negative']&&($match=mb_stripos($text,$t['text']))!==false){$pos=max(0,$match-100);break;}}$text=wp_trim_words(mb_substr($text,$pos),45,'…');return highlight(($pos?'…':'').$text,$query);}
function group_expression(){global $wpdb;$groups=settings()['groups'];$sql='CASE ';foreach($groups as $slug=>$g){if(empty($g['enabled']))continue;$rules=[];foreach($g['terms']??[] as $tax=>$ids){$ids=array_map('absint',$ids);if($ids)$rules[]=$wpdb->prepare("EXISTS(SELECT 1 FROM $wpdb->term_relationships r JOIN $wpdb->term_taxonomy t ON t.term_taxonomy_id=r.term_taxonomy_id WHERE r.object_id=$wpdb->posts.ID AND t.taxonomy=%s AND t.term_id IN (".implode(',',$ids).'))',$tax);}foreach($g['ancestors']??[] as $id)$rules[]=$wpdb->prepare("EXISTS(SELECT 1 FROM $wpdb->postmeta a WHERE a.post_id=$wpdb->posts.ID AND a.meta_key='_eds_ancestors' AND FIND_IN_SET(%s,a.meta_value)>0)",(string)absint($id));if($rules)$sql.='WHEN ('.implode(' OR ',$rules).') THEN '.$wpdb->prepare('%s ',$slug);}foreach($groups as $slug=>$g){if(empty($g['enabled'])||empty($g['types']))continue;$sql.=$wpdb->prepare("WHEN $wpdb->posts.post_type IN (".implode(',',array_fill(0,count($g['types']),'%s')).') THEN %s ',array_merge($g['types'],[$slug]));}return $sql."ELSE '' END";}
function query_clauses($clauses,$q){if(!$q->get('eds_enhanced'))return $clauses;global $wpdb;$clauses['where'].=" AND $wpdb->posts.post_password='' AND $wpdb->posts.post_status='publish'";if($q->get('eds_group'))$clauses['where'].=$wpdb->prepare(' AND ('.group_expression().')=%s',$q->get('eds_group'));$clauses['orderby']=$wpdb->prepare("($wpdb->posts.post_title LIKE %s) DESC, ",'%'.$wpdb->esc_like($q->get('s')).'%').$clauses['orderby'];return $clauses;}
function available_groups($main){$groups=[];if(trim((string)$main->get('s'))==='')return $groups;foreach(settings()['groups'] as $slug=>$group){if(empty($group['enabled']))continue;$args=$main->query_vars;$args['eds_group']=$slug;$args['paged']=1;$args['offset']=0;$args['posts_per_page']=1;$args['no_found_rows']=true;$args['fields']='ids';$probe=new \WP_Query($args);if($probe->posts||$main->get('eds_group')===$slug)$groups[$slug]=$group;}return $groups;}
function render_results($pre,$block){if($pre!==null||!is_search()||!settings()['enhanced']||($block['blockName']??'')!=='core/group'||!str_contains($block['attrs']['className']??'','ed-search-experience'))return $pre;
 global $wp_query;$s=settings();$query=get_search_query(false);$selected=isset($_GET['group'])?sanitize_key(wp_unslash($_GET['group'])):'';
 ob_start();echo '<main id="ed-main" class="ed-search-experience"><h1>Search results</h1><p>Results for “'.esc_html($query).'”</p>';get_search_form();
 if($s['filters']){echo '<nav class="ed-search-filters" aria-label="Search groups"><a href="'.esc_url(add_query_arg('s',$query,home_url('/'))).'">All results</a>';foreach(available_groups($wp_query) as $slug=>$g){echo '<a '.($selected===$slug?'aria-current="page" ':'').'href="'.esc_url(add_query_arg(['s'=>$query,'group'=>$slug],home_url('/'))).'">'.esc_html($g['label']).'</a>';}echo '</nav>';}
 $page=max(1,(int)get_query_var('paged'));$total=(int)$wp_query->found_posts;$count=count($wp_query->posts);echo '<p>'.($count?esc_html((($page-1)*$s['per_page']+1).'–'.(($page-1)*$s['per_page']+$count).' of '.$total):'0 results').'</p>';
 foreach($wp_query->posts as $p){$group=group_for($p);$type=get_post_type_object($p->post_type);echo '<article class="ed-search-result"><p>'.esc_html($s['groups'][$group]['label']??$type->labels->singular_name).'</p><h2><a href="'.esc_url(get_permalink($p)).'">'.highlight(get_the_title($p),$query).' →</a></h2><p>'.snippet($p,$query).'</p></article>';}
 if(!$count)echo '<p>'.($query===''?'Enter a search term.':'No matches on this page. Try another term, All results, or the first page.').'</p>';
 echo '<nav class="ed-search-pagination" aria-label="Search result pages">'.wp_kses_post(paginate_links(['total'=>$wp_query->max_num_pages,'current'=>$page,'add_args'=>array_filter(['s'=>$query,'group'=>$selected]),'prev_text'=>'Previous','next_text'=>'Next'])??'').'</nav></main>';return ob_get_clean();
}
function update_settings($value){$error=validate_settings($value);if(is_wp_error($error))return $error;update_option('eds_settings',$value,false);return settings();}
function validate_settings($s){if(!is_array($s)||!isset($s['per_page'])||!is_int($s['per_page'])||$s['per_page']<1||$s['per_page']>50)return new \WP_Error('settings','Results per page must be an integer from 1 to 50.');foreach(['enhanced','filters','highlight'] as $k)if(!isset($s[$k])||!is_bool($s[$k]))return new \WP_Error('settings','Toggles must be boolean.');foreach(['excluded_types','excluded_ids','groups'] as $key)if(!isset($s[$key])||!is_array($s[$key]))return new \WP_Error('settings','Exclusions and groups must be arrays or JSON objects.');foreach($s['excluded_types']??[] as $type)if(!post_type_exists($type))return new \WP_Error('type','Unknown excluded post type.');foreach($s['excluded_ids']??[] as $id)if(!get_post($id))return new \WP_Error('post','Missing excluded post ID.');foreach($s['groups']??[] as $slug=>$g){if(!is_string($slug)||!is_array($g)||!isset($g['enabled'])||!is_bool($g['enabled'])||sanitize_key($slug)!==$slug||empty($g['label'])||!is_string($g['label']))return new \WP_Error('group','Each group needs a stable slug and label.');foreach($g['types']??[] as $type)if(!post_type_exists($type))return new \WP_Error('type','Unknown group post type.');foreach($g['ancestors']??[] as $id)if(get_post_type($id)!=='page')return new \WP_Error('page','Missing ancestor page.');foreach($g['terms']??[] as $tax=>$ids)foreach($ids as $id)if(!term_exists($id,$tax))return new \WP_Error('term','Missing mapped term.');}return true;}
