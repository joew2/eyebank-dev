<?php
/** Durable records writer, adapted from the local CWS deployment runner. */
namespace CWS\ResourceCardImages;
defined('ABSPATH') || exit;
function bulk_records($key,$records) {
    $token=lock_acquire();
    try {
        $h=history();$state=$h[$key]??[];
        if (($state['status']??'')==='completed') return 'Already completed; later edits preserved.';
        if (empty($state['records'])) {
            foreach ($records as &$record) {
                if ($record['id']&&!current_user_can('edit_post',$record['id'])) fail('Permission denied for '.$record['type']);
                $record['before']=$record['id']?get_post_field('post_content',$record['id']):null;
                $record['meta_before']=[];
                foreach ($record['meta'] as $field=>$value) $record['meta_before'][$field]=$record['id']?get_post_meta($record['id'],$field,true):'';
            }
            unset($record);
            $state=['status'=>'running','actor'=>get_current_user_id(),'started'=>time(),'records'=>$records];
            $h[$key]=$state;lock_check($token);save_history($h);
        }
        foreach ($state['records'] as $i=>&$record) {
            lock_check($token);
            if (!$record['id']) {
                $marker=$key.':'.$i;
                $found=get_posts(['post_type'=>$record['type'],'post_status'=>'any','meta_key'=>'_cwsrcimg_record','meta_value'=>$marker,'fields'=>'ids','numberposts'=>2]);
                if (count($found)>1) fail('Ambiguous deployment template record.');
                if(!$found&&!current_user_can($record['type']==='page'?'publish_pages':'edit_theme_options')) fail('Cannot create the missing repair record.');
                $id=$found?(int)$found[0]:wp_insert_post(wp_slash(['post_type'=>$record['type'],'post_status'=>'publish','post_name'=>$record['slug'],'post_title'=>$record['title']??ucwords(str_replace('-',' ',$record['slug'])),'post_content'=>'','meta_input'=>['_cwsrcimg_record'=>$marker]]),true);
                if (is_wp_error($id)||!$id) fail('Cannot create '.$record['slug']);
                $record['id']=(int)$id;$h[$key]=$state;save_history($h);
            }
            $id=$record['id'];
            if (!current_user_can('edit_post',$id)) fail('Permission denied for deployment record.');
            $current=get_post_field('post_content',$id);
            if ($current!==$record['content']) {
                if ($current!==$record['before']&&!($record['before']===null&&$current==='')) fail('Content changed during deployment. Restore/review that record before retrying.');
                if (empty($record['revision_saved'])) {wp_save_post_revision($id);$record['revision_saved']=true;$h[$key]=$state;save_history($h);}
                $result=wp_update_post(['ID'=>$id,'post_content'=>wp_slash($record['content'])],true);
                if (is_wp_error($result)) fail($result->get_error_message());
            }
            if (get_post_field('post_content',$id)!==$record['content']) fail('Content verification failed.');
            foreach ($record['meta'] as $field=>$value) {
                $current=get_post_meta($id,$field,true);
                if ($current!==$value&&$current!==$record['meta_before'][$field]) fail('Page settings changed during deployment: '.$field);
                if ($value==='') delete_post_meta($id,$field);else update_post_meta($id,$field,$value);
                if (get_post_meta($id,$field,true)!==$value) fail('Page setting verification failed: '.$field);
            }
            if (in_array($record['type'],['wp_template','wp_template_part'],true)) {
                $result=wp_set_object_terms($id,'eyedonation','wp_theme');
                if (is_wp_error($result)) fail($result->get_error_message());
                if ($record['type']==='wp_template_part') {
                    $result=wp_set_object_terms($id,$record['slug'],'wp_template_part_area');
                    if (is_wp_error($result)) fail($result->get_error_message());
                }
            }
            lock_check($token);$record['completed']=true;$h[$key]=$state;save_history($h);
        }
        unset($record);
        $state['status']='completed';$state['completed']=time();$h[$key]=$state;save_history($h);
        return 'Installed and verified.';
    } finally {lock_release($token);}
}
