<?php
/** One-click deployment coordinator. Existing numbered installers remain immutable. */
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
const BULK_OPTION = 'edm_bulk_20261030_v1';
function bulk_steps() {
    $steps = [
        ['id'=>'bulk_homepage_v1','label'=>'Current editable homepage','kind'=>'home'],
    ];
    foreach ([
        'UpdatedAboutPageMigration'=>'About the Eye-Bank', 'RecipientsFamiliesMigration'=>'Recipients and donor families',
        'OurHistoryMigration'=>'Our history', 'LeadershipMigration'=>'Leadership', 'EmploymentMigration'=>'Employment',
        'VolunteerMigration'=>'Volunteer', 'HistoryLayoutMigration'=>'History layout', 'VolunteerLayoutMigration'=>'Volunteer layout',
        'AboutDonationMigration'=>'About donation', 'GivingTuesdayMigration'=>'Giving Tuesday', 'SignupMigration'=>'Sign up',
        'GivingTuesdayCopyMigration'=>'Giving Tuesday copy', 'GivingTuesdayHashtagMigration'=>'Giving Tuesday hashtag',
        'DonorLetterMigration'=>'Write to a donor family', 'RecipientLetterMigration'=>'Write to a recipient',
        'BecomeDonorMigration'=>'Become a donor', 'BoardDirectorsMigration'=>'Board of directors',
        'ContactUsMigration'=>'Contact us', 'FAQsMigration'=>'FAQs', 'AboutHeroV4Migration'=>'Current About hero',
        'GivingTuesdayAttributionMigration'=>'Giving Tuesday attribution', 'PortablePageMediaMigration'=>'Bundled page images',
        'MemberMenuMigration'=>'Member access menu', 'ForPhysiciansMigration'=>'For physicians',
        'MedicalAdvisoryBoardMigration'=>'Medical advisory board',
    ] as $name=>$label) {
        $class=__NAMESPACE__.'\\'.$name;
        $steps[]=['id'=>$class::ID,'label'=>$label,'kind'=>'installer','class'=>$class];
    }
    $dependencies=[
        '024_reflow_our_history'=>'020_install_our_history',
        '025_reflow_volunteer'=>'023_install_volunteer',
        '030_update_givingtuesday_copy'=>'027_install_givingtuesday',
        '031_update_givingtuesday_hashtag'=>'030_update_givingtuesday_copy',
        '038_update_about_hero_v4'=>'018_install_updated_about',
        '039_givingtuesday_attribution'=>'031_update_givingtuesday_hashtag',
    ];
    foreach ($steps as &$step) if (isset($dependencies[$step['id']])) $step['depends']=$dependencies[$step['id']];
    unset($step);
    foreach ($GLOBALS['edm_selected_pages'] ?? [] as $slug=>$migration) {
        $steps[]=['id'=>$migration->id(),'label'=>ucwords(str_replace('-',' ',$slug)),'kind'=>'selected','slug'=>$slug];
    }
    $steps[]=['id'=>'bulk_shared_v1','label'=>'Current header, footer and editable front-page template','kind'=>'shared'];
    $steps[]=['id'=>'bulk_search_v1','label'=>'Rebuild public search index','kind'=>'search'];
    return $steps;
}
function bulk_begin($steps) {
    $job=get_option(BULK_OPTION,[]);
    if (!$job) $job=['id'=>wp_generate_uuid4(),'results'=>[],'created'=>time()];
    $job['actor']=get_current_user_id();$job['status']='running';
    // A click retries only failed/interrupted steps, keeping completed records intact.
    foreach ($job['results'] as &$result) if (($result['status']??'')!=='completed') $result=['status'=>'pending'];
    unset($result);
    $job['total']=count($steps);update_option(BULK_OPTION,$job,false);return $job;
}
/** One step per request. Failures are reported and independent updates still run. */
function bulk_advance($job,$steps,$execute=null) {
    $execute=$execute??__NAMESPACE__.'\\bulk_execute';
    foreach ($steps as $step) {
        $status=$job['results'][$step['id']]['status']??'pending';
        if (in_array($status,['completed','failed'],true)) continue;
        $job['results'][$step['id']]=['status'=>'running','label'=>$step['label']];
        update_option(BULK_OPTION,$job,false);
        try {
            if (isset($step['depends'])&&($job['results'][$step['depends']]['status']??'')!=='completed') fail('Waiting for an earlier update: '.$step['depends'].'. Fix its reported error and click Process all updates again.');
            $message=$execute($step);
            if (is_array($message)&&!empty($message['pending'])) {
                $job['results'][$step['id']]=['status'=>'pending','label'=>$step['label'],'message'=>$message['message']];
                $job['updated']=time();update_option(BULK_OPTION,$job,false);return $job;
            }
            $job['results'][$step['id']]=['status'=>'completed','label'=>$step['label'],'message'=>is_string($message)?$message:'Completed.'];
        } catch (\Throwable $error) {
            $job['results'][$step['id']]=['status'=>'failed','label'=>$step['label'],'message'=>$error->getMessage()];
        }
        $job['updated']=time();update_option(BULK_OPTION,$job,false);
        return $job;
    }
    $failed=array_filter($job['results'],static function($r){return $r['status']==='failed';});
    $job['status']=$failed?'needs_attention':'completed';$job['updated']=time();
    update_option(BULK_OPTION,$job,false);return $job;
}
function bulk_execute($step) {
    preflight();
    if ($step['kind']==='installer') {
        $class=$step['class'];
        if ((history()[$step['id']]['status']??'')==='completed') return 'Already completed; later edits preserved.';
        $class::preview();return $class::run();
    }
    if ($step['kind']==='selected') {
        $migration=$GLOBALS['edm_selected_pages'][$step['slug']];
        if ((history()[$step['id']]['status']??'')==='completed') return 'Already completed; later edits preserved.';
        $migration->preview();return $migration->run();
    }
    if ($step['kind']==='search') {
        if (!function_exists('EyeDonation\\Search\\request_rebuild')) fail('Activate EyeDonation Search, then click Process all updates to retry.');
        if (!get_option('edm_bulk_search_started_v1')) {
            \EyeDonation\Search\request_rebuild();update_option('edm_bulk_search_started_v1',time(),false);
        }
        if (get_option('eds_rebuild_job')) \EyeDonation\Search\rebuild_batch();
        if (get_option('eds_rebuild_job')) return ['pending'=>true,'message'=>'Indexing public search content…'];
        if (!get_option('eds_rebuild_completed')) fail('Search indexing has not completed. Click Process all updates to retry.');
        return 'Public search index rebuilt.';
    }
    if ($step['kind']==='home') {
        $id=(int)get_option('page_on_front');
        if (get_option('show_on_front')!=='page'||!$id||get_post_type($id)!=='page') fail('Choose your existing static homepage under Settings → Reading, then click Process all updates again.');
        if (!current_user_can('edit_post',$id)) fail('You cannot edit the selected homepage.');
        $content=ed_update_homepage_support_responsive_v2(ed_portable_homepage_content(),true);
        if (str_contains($content,'{{')||str_contains($content,'/placeholder.svg')) fail('The current homepage theme assets are incomplete. Re-upload the matching EyeDonation theme.');
        return bulk_records($step['id'],[['id'=>$id,'type'=>'page','slug'=>'','content'=>$content,'meta'=>['_wp_page_template'=>'homepage','tcb_editor_disabled'=>'1','tcb_editor_enabled'=>'']]]);
    }
    if ($step['kind']==='shared') {
        $records=[];
        foreach (['header','footer'] as $slug) {
            $template=get_block_template('eyedonation//'.$slug,'wp_template_part');
            $records[]=['id'=>(int)($template->wp_id??0),'type'=>'wp_template_part','slug'=>$slug,'content'=>file_get_contents(get_theme_file_path('/parts/'.$slug.'.html')),'meta'=>[]];
        }
        $template=get_block_template('eyedonation//front-page','wp_template');
        $records[]=['id'=>(int)($template->wp_id??0),'type'=>'wp_template','slug'=>'front-page','content'=>file_get_contents(get_theme_file_path('/templates/front-page.html')),'meta'=>[]];
        return bulk_records($step['id'],$records);
    }
    fail('Unknown deployment step.');
}
/** Durable before-images and per-field reconciliation for the current bundled layouts. */
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
                $found=get_posts(['post_type'=>$record['type'],'post_status'=>'any','meta_key'=>'_edm_bulk_record','meta_value'=>$marker,'fields'=>'ids','numberposts'=>2]);
                if (count($found)>1) fail('Ambiguous deployment template record.');
                $id=$found?(int)$found[0]:wp_insert_post(wp_slash(['post_type'=>$record['type'],'post_status'=>'publish','post_name'=>$record['slug'],'post_title'=>ucwords(str_replace('-',' ',$record['slug'])),'post_content'=>'','meta_input'=>['_edm_bulk_record'=>$marker]]),true);
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
            if ($record['type']!=='page') {
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
