<?php
require __DIR__ . '/bootstrap.php';
use CWS\CustomLinkReports\Date_Windows;
use CWS\CustomLinkReports\Api;
use CWS\CustomLinkReports\Renderer;
$fail=[];$eq=function($actual,$expected,$name)use(&$fail){if($actual!==$expected)$fail[]=$name.' expected '.json_encode($expected).' got '.json_encode($actual);};
$tz=new DateTimeZone('America/New_York');
$eq(Date_Windows::period('previous_7',new DateTimeImmutable('2026-11-13 12:00:00',$tz)),['2026-11-06','2026-11-12'],'previous seven');
$eq(Date_Windows::period('previous_week',new DateTimeImmutable('2026-11-13 12:00:00',$tz)),['2026-11-02','2026-11-08'],'previous week');
$eq(Date_Windows::previous('2026-11-06','2026-11-12',$tz),['2026-10-30','2026-11-05'],'previous equal period');
$config=['timezone'=>'America/New_York','weekday'=>1,'time'=>'09:00'];
$next=Date_Windows::next_occurrence($config,new DateTimeImmutable('2026-03-06 12:00:00',new DateTimeZone('UTC')));
$eq((new DateTimeImmutable('@'.$next))->setTimezone($tz)->format('Y-m-d H:i T'),'2026-03-09 09:00 EDT','DST local time');
$recent=Date_Windows::most_recent_occurrence($config,new DateTimeImmutable('2026-03-13 12:00:00',new DateTimeZone('UTC')));
$eq((new DateTimeImmutable('@'.$recent))->setTimezone($tz)->format('Y-m-d H:i T'),'2026-03-09 09:00 EDT','most recent occurrence');
$eq(Renderer::format_date('2026-11-15','America/New_York'),'Nov, Sun 15, 2026','US display date');
$fixture=['totalClicks'=>8,'humanClicks'=>6,'clickStatistics'=>['datasets'=>[['data'=>[['x'=>'2026-11-06T05:00:00.000Z','y'=>3],['x'=>'2026-11-07T05:00:00.000Z','y'=>5]]]]],'referer'=>[['referer'=>'example.com','score'=>4]],'country'=>[['country'=>'US','countryName'=>'United States','score'=>8]],'browser'=>[['browser'=>'Firefox','score'=>2]],'os'=>[['os'=>'Linux','score'=>1]]];
$normalized=Api::normalize($fixture,'lnk_test','2026-11-06','2026-11-12','America/New_York');
if(is_wp_error($normalized))$fail[]='normalization failed';else{$eq($normalized['total_clicks'],8,'total');$eq($normalized['daily'],['2026-11-06'=>3,'2026-11-07'=>5],'daily');$eq($normalized['breakdowns']['countries'][0]['label'],'United States','country');}
$malformed=Api::normalize([], 'lnk_test','2026-11-06','2026-11-12','UTC');if(!is_wp_error($malformed))$fail[]='malformed response accepted';
if($fail){fwrite(STDERR,implode("\n",$fail)."\n");exit(1);}echo "PASS: date windows, DST, display formatting, and API normalization\n";

