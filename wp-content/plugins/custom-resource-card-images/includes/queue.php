<?php
namespace CWS\ResourceCardImages;
defined('ABSPATH') || exit;
function bulk_begin($steps) {
    $job=get_option(JOB_OPTION,[]);
    if (!$job) $job=['id'=>wp_generate_uuid4(),'results'=>[],'created'=>time()];
    $job['actor']=get_current_user_id();$job['status']='running';
    // A click retries only failed/interrupted steps, keeping completed records intact.
    foreach ($job['results'] as &$result) if (($result['status']??'')!=='completed') $result=['status'=>'pending'];
    unset($result);
    $job['total']=count($steps);update_option(JOB_OPTION,$job,false);return $job;
}
/** One step per request. Failures are reported and independent updates still run. */
function bulk_advance($job,$steps,$execute=null) {
    $execute=$execute??__NAMESPACE__.'\\bulk_execute';
    foreach ($steps as $step) {
        $status=$job['results'][$step['id']]['status']??'pending';
        if (in_array($status,['completed','failed'],true)) continue;
        $job['results'][$step['id']]=['status'=>'running','label'=>$step['label']];
        update_option(JOB_OPTION,$job,false);
        try {
            if (isset($step['depends'])&&($job['results'][$step['depends']]['status']??'')!=='completed') fail('Waiting for an earlier update: '.$step['depends'].'. Fix its reported error and click Process all updates again.');
            $message=$execute($step);
            if (is_array($message)&&!empty($message['pending'])) {
                $job['results'][$step['id']]=['status'=>'pending','label'=>$step['label'],'message'=>$message['message']];
                $job['updated']=time();update_option(JOB_OPTION,$job,false);return $job;
            }
            $job['results'][$step['id']]=['status'=>'completed','label'=>$step['label'],'message'=>is_string($message)?$message:'Completed.'];
        } catch (\Throwable $error) {
            $job['results'][$step['id']]=['status'=>'failed','label'=>$step['label'],'message'=>$error->getMessage()];
        }
        $job['updated']=time();update_option(JOB_OPTION,$job,false);
        return $job;
    }
    $failed=array_filter($job['results'],static function($r){return $r['status']==='failed';});
    $job['status']=$failed?'needs_attention':'completed';$job['updated']=time();
    update_option(JOB_OPTION,$job,false);return $job;
}
