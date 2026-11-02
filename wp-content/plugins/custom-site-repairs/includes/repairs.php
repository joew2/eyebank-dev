<?php
namespace CWS\SiteRepairs;
defined('ABSPATH') || exit;
function bulk_steps(){return [
 ['id'=>'repair_template_v1','label'=>'Editable page template','kind'=>'template'],
 ['id'=>'repair_styles_v1','label'=>'Responsive header and page sizing','kind'=>'styles'],
 ['id'=>'repair_portals_v1','label'=>'Transplant Portal and Research Portal links','kind'=>'header','depends'=>'repair_styles_v1'],
 ['id'=>'repair_downloads_v1','label'=>'User Downloads','kind'=>'downloads','depends'=>'repair_template_v1'],
 ['id'=>'repair_transplant_v1','label'=>'Request Transplant Tissue','kind'=>'transplant','depends'=>'repair_template_v1'],
 ['id'=>'repair_research_v1','label'=>'Request Research Tissue','kind'=>'research','depends'=>'repair_template_v1'],
 ['id'=>'repair_signup_v1','label'=>'Sign Up — page 387956, Gravity Form 85','kind'=>'signup'],
];}
function existing_page($slug){return get_page_by_path($slug,OBJECT,'page');}
function repair_record($slug,$title,$content){
 $page=existing_page($slug);
 return ['id'=>$page?(int)$page->ID:0,'type'=>'page','slug'=>$slug,'title'=>$title,'content'=>$content,'meta'=>['_wp_page_template'=>'cws-repair-page','tcb_editor_disabled'=>'1','tcb_editor_enabled'=>'']];
}
function page_content($heading,$inner,$extra=''){
 return '<!-- wp:group {"className":"ed-section cws-repair-page '.$extra.'","layout":{"type":"default"}} --><div class="wp-block-group ed-section cws-repair-page '.$extra.'"><!-- wp:heading {"level":1} --><h1 class="wp-block-heading">'.esc_html($heading).'</h1><!-- /wp:heading -->'.$inner.'</div><!-- /wp:group -->';
}
function header_links($content){
 $blocks=parse_blocks($content);$count=0;
 $walk=function(&$items)use(&$walk,&$count){foreach($items as &$block){
  $classes=preg_split('/\s+/',$block['attrs']['className']??'');
  if(($block['blockName']??'')==='core/group'&&in_array('ed-brand',$classes,true)){
   $count++;
   if(!in_array('cwsr-header',$classes,true))$block['attrs']['className']=trim(($block['attrs']['className']??'').' cwsr-header');
   $patch=static function($html){$tags=new \WP_HTML_Tag_Processor($html);if($tags->next_tag('DIV'))$tags->add_class('cwsr-header');return $tags->get_updated_html();};
   $block['innerHTML']=$patch($block['innerHTML']);foreach($block['innerContent'] as &$piece)if(is_string($piece)){$piece=$patch($piece);break;}unset($piece);
   $target=null;foreach($block['innerBlocks'] as $i=>$child)if(($child['blockName']??'')==='core/buttons'){$target=$i;break;}
   if($target===null){$new=parse_blocks('<!-- wp:buttons --><div class="wp-block-buttons"></div><!-- /wp:buttons -->')[0];$block['innerBlocks'][]=$new;$target=count($block['innerBlocks'])-1;array_splice($block['innerContent'],count($block['innerContent'])-1,0,[null]);}
   $buttons=&$block['innerBlocks'][$target];
   foreach(['Transplant Portal'=>'/transplant/','Research Portal'=>'/researchers/'] as $label=>$url){
    $exists=false;foreach($buttons['innerBlocks'] as $child)if(str_contains($child['innerHTML']??'',$url)||str_contains(wp_strip_all_tags($child['innerHTML']??''),$label))$exists=true;
    if($exists)continue;
    $html='<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="'.esc_url(home_url($url)).'">'.esc_html($label).'</a></div><!-- /wp:button -->';
    $buttons['innerBlocks'][]=parse_blocks($html)[0];array_splice($buttons['innerContent'],count($buttons['innerContent'])-1,0,[null]);
   }
   unset($buttons);
  }elseif(!empty($block['innerBlocks']))$walk($block['innerBlocks']);
 }};$walk($blocks);
 if($count!==1)fail('Expected one EyeDonation logo/header row. The current saved header needs review.');
 return serialize_blocks($blocks);
}
function shortcode_block($code){return "<!-- wp:shortcode -->\n".$code."\n<!-- /wp:shortcode -->";}
function sources($page){if(!$page)return [];return [$page->post_content,(string)get_post_meta($page->ID,'tve_updated_post',true)];}
function transplant_embed($page){
 foreach(sources($page) as $source){
  if(!preg_match('~new\s+Hipaatizer\s*\(~i',$source))continue;
  preg_match_all('~<script\b[^>]*>.*?</script>~is',$source,$scripts);$keep=[];
  foreach($scripts[0] as $script)if(str_contains($script,'hipaatizer-form-renderer.js')||preg_match('~new\s+Hipaatizer\s*\(~i',$script))$keep[]=$script;
  if(count($keep)>=2)return implode("\n",$keep);
 }
 if($page&&preg_match('~<(?:iframe|form|script)\b|\[(?:gravityform|cred_form)\b~i',$page->post_content))fail('The transplant page has an unrecognized form embed. It was left intact; restore its original embed and retry.');
 return '<script id="39830e66-a85a-480b-a84b-def1bb893104-script" src="https://app.hipaatizer.com/shared/hipaatizer-form-renderer.js"></script><script>new Hipaatizer("39830e66-a85a-480b-a84b-def1bb893104", false, "").render();</script>';
}
function research_shortcode($page){
 $code='';foreach(sources($page) as $source)if(preg_match('~\[gravityform\b[^\]]*\]~i',$source,$match)){$code=$match[0];break;}
 if($code===''){
  if($page&&preg_match('~<(?:iframe|form|script)\b|\[(?:cred_form|cred_user_form)\b~i',$page->post_content))fail('The research page has an unrecognized form embed. It was left intact.');
  $code='[gravityform id="67" title="false" description="false"]';
 }
 $attributes=shortcode_parse_atts(preg_replace('~^\[gravityform\s*|\]$~i','',$code));$id=(int)($attributes['id']??0);
 if(!shortcode_exists('gravityform')||!class_exists('GFAPI')||!$id||!\GFAPI::get_form($id))fail('Activate Gravity Forms and restore research request form '.$id.', then process the repairs again.');
 return $code;
}
function bulk_execute($step){
 preflight();$kind=$step['kind'];
 if($kind==='signup'){
  $page=get_post(387956);
  if(!$page||$page->post_type!=='page'||in_array($page->post_status,['trash','auto-draft'],true))fail('Sign Up page 387956 is unavailable. Confirm that the new page still exists, then retry.');
  if($page->post_name!=='sign-up')fail('Page 387956 must use the sign-up permalink so the existing Sign Up styling and links work. Set its URL slug to sign-up, then retry.');
  if(!current_user_can('edit_post',387956))fail('You cannot edit Sign Up page 387956.');
  if(!shortcode_exists('gravityform')||!class_exists('GFAPI')||!\GFAPI::get_form(85))fail('Activate Gravity Forms and restore mailing-list form 85, then process the repairs again.');
  if(!function_exists('ed_sign_up_content')||!is_file(get_theme_file_path('/templates/sign-up.html')))fail('Re-upload the matching EyeDonation theme with its Sign Up layout.');
  $template=get_block_template('eyedonation//sign-up','wp_template');
  return bulk_records($step['id'],[
   ['id'=>(int)($template->wp_id??0),'type'=>'wp_template','slug'=>'sign-up','content'=>file_get_contents(get_theme_file_path('/templates/sign-up.html')),'meta'=>[]],
   ['id'=>387956,'type'=>'page','slug'=>'sign-up','content'=>ed_sign_up_content(),'meta'=>['_wp_page_template'=>'sign-up','tcb_editor_disabled'=>'1','tcb_editor_enabled'=>'']],
  ]);
 }
 if($kind==='styles')return repair_styles();
 if($kind==='template'){
  $template=get_block_template('eyedonation//cws-repair-page','wp_template');
  $content='<!-- wp:template-part {"slug":"header","tagName":"header"} /--><!-- wp:group {"tagName":"main","anchor":"ed-main","className":"ed-main ed-main-full","layout":{"type":"default"}} --><main id="ed-main" class="wp-block-group ed-main ed-main-full"><!-- wp:post-content /--></main><!-- /wp:group --><!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->';
  return bulk_records($step['id'],[['id'=>(int)($template->wp_id??0),'type'=>'wp_template','slug'=>'cws-repair-page','content'=>$content,'meta'=>[]]]);
 }
 if($kind==='header'){
  $header=get_block_template('eyedonation//header','wp_template_part');if(!$header)fail('The EyeDonation header template is unavailable.');
  return bulk_records($step['id'],[['id'=>(int)($header->wp_id??0),'type'=>'wp_template_part','slug'=>'header','content'=>header_links($header->content),'meta'=>[]]]);
 }
 if($kind==='downloads'){
  if(!shortcode_exists('filemanager')||!shortcode_exists('wpv-login-form'))fail('Activate Advanced File Manager and Toolset Views so their filemanager and wpv-login-form shortcodes are available.');
  $guest='<!-- wp:group {"className":"ed-converted-guests","layout":{"type":"default"}} --><div class="wp-block-group ed-converted-guests"><!-- wp:paragraph --><p>Please log in to access member downloads.</p><!-- /wp:paragraph -->'.shortcode_block('[wpv-login-form redirect_url="'.esc_url(home_url('/user-downloads/')).'" allow_remember="true" remember_default="true"]').'</div><!-- /wp:group -->';
  $members='<!-- wp:group {"className":"ed-converted-members","layout":{"type":"default"}} --><div class="wp-block-group ed-converted-members">'.shortcode_block('[filemanager foldername="*"]').'</div><!-- /wp:group -->';
  return bulk_records($step['id'],[repair_record('user-downloads','User Downloads',page_content('User Downloads',$guest.$members,'cws-repair-downloads'))]);
 }
 if($kind==='transplant'){
  if(!current_user_can('unfiltered_html'))fail('An administrator with unfiltered HTML permission must restore the Hipaatizer embed.');
  $embed=transplant_embed(existing_page('request-transplant-tissue'));
  return bulk_records($step['id'],[repair_record('request-transplant-tissue','Request Transplant Tissue',page_content('Request Transplant Tissue','<!-- wp:html -->'.$embed.'<!-- /wp:html -->'))]);
 }
 if($kind==='research'){
  $code=research_shortcode(existing_page('request-research-tissue'));
  return bulk_records($step['id'],[repair_record('request-research-tissue','Request Research Tissue',page_content('Request Research Tissue',shortcode_block($code)))]);
 }
 fail('Unknown repair step.');
}
function repair_styles(){
 $token=lock_acquire();try{
  $h=history();$key='repair_styles_v1';$state=$h[$key]??[];if(($state['status']??'')==='completed')return 'Sizing already repaired; later CSS edits preserved.';
  $before=wp_get_custom_css('eyedonation');
  if(!$state){$after=preg_replace('~/\* CWS SITE REPAIRS START \*/.*?/\* CWS SITE REPAIRS END \*/~s','',$before);$after=rtrim($after)."\n/* CWS SITE REPAIRS START */\n".file_get_contents(DIR.'/assets/repairs.css')."\n/* CWS SITE REPAIRS END */\n";$state=['status'=>'running','before'=>$before,'after'=>$after,'actor'=>get_current_user_id()];$h[$key]=$state;save_history($h);}
  if($before!==$state['before']&&$before!==$state['after'])fail('Additional CSS changed during the repair. Review it before resuming.');
  lock_check($token);$result=wp_update_custom_css_post($state['after'],['stylesheet'=>'eyedonation']);if(is_wp_error($result))fail($result->get_error_message());
  if(wp_get_custom_css('eyedonation')!==$state['after'])fail('Sizing stylesheet verification failed.');
  $state['status']='completed';$h[$key]=$state;save_history($h);return 'Responsive sizing saved in Additional CSS.';
 }finally{lock_release($token);}
}
