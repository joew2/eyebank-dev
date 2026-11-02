<?php
namespace CWS\LandingNoHeaderFooter;
defined('ABSPATH') || exit;
function bulk_steps(){return [['id'=>'landing_template_v1','label'=>'Install content-only template and assign Fall Benefit','kind'=>'page']];}
function bulk_execute($step){
 preflight();
 if((history()[$step['id']]['status']??'')==='completed')return 'Template already installed and assigned; later edits preserved.';
 $page=get_page_by_path('fall-benefit',OBJECT,'page');
 if(!$page||in_array($page->post_status,['trash','auto-draft'],true))fail('The existing /fall-benefit/ page was not found. Restore or create it before processing.');
 if(!current_user_can('edit_post',$page->ID))fail('You cannot edit the Fall Benefit page.');
 $template=get_block_template('eyedonation//landing-no-header-footer','wp_template');
 return bulk_records($step['id'],[
 ['id'=>(int)($template->wp_id??0),'type'=>'wp_template','slug'=>'landing-no-header-footer','title'=>'Landing Page No Header and Footer','content'=>file_get_contents(DIR.'/payloads/template.html'),'meta'=>[]],
 ['id'=>(int)$page->ID,'type'=>'page','slug'=>$page->post_name,'content'=>$page->post_content,'meta'=>['_wp_page_template'=>'landing-no-header-footer']],
 ]);
}
