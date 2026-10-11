<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if($path==='/validation'){require __DIR__.'/validation.php';return;}
$base=str_starts_with($path,'/wp-content/')?__DIR__:rtrim(getenv('EYE_DONATION_WP_ROOT')?:die('Set EYE_DONATION_WP_ROOT.'),'/');$file=realpath($base.$path);
if($file&&str_starts_with($file,$base.'/')&&is_file($file)&&pathinfo($file,PATHINFO_EXTENSION)!=='php'){$types=['css'=>'text/css','js'=>'application/javascript','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','woff2'=>'font/woff2'];header('Content-Type: '.($types[pathinfo($file,PATHINFO_EXTENSION)]??'application/octet-stream'));readfile($file);return;}
define('WP_USE_THEMES',true);require __DIR__.'/bootstrap.php';wp();require ABSPATH.'wp-includes/template-loader.php';
