<?php
$source=rtrim(getenv('EYE_DONATION_WP_ROOT')?:die('Set EYE_DONATION_WP_ROOT to the source WordPress directory.'),'/').'/';
$config=file_get_contents($source.'wp-config.php');
foreach(['DB_NAME','DB_USER','DB_PASSWORD','DB_HOST','DB_CHARSET','DB_COLLATE'] as $key){if(preg_match('/define\(\s*[\'\"]'.preg_quote($key,'/').'[\'\"]\s*,\s*([\'\"])(.*?)\1\s*\)/s',$config,$match))define($key,$match[2]);}
define('ABSPATH',$source);define('WP_CONTENT_DIR',__DIR__.'/wp-content');define('WP_CONTENT_URL','http://127.0.0.1:8899/wp-content');define('WP_HOME','http://127.0.0.1:8899');define('WP_SITEURL','http://127.0.0.1:8899');define('DISABLE_WP_CRON',true);define('WP_DEBUG',true);define('WP_DEBUG_DISPLAY',false);
$table_prefix='edrehearsal_';
require $source.'wp-settings.php';
add_filter('pre_wp_mail',fn()=>true);
add_filter('pre_http_request',fn()=>new WP_Error('isolated','Outbound HTTP disabled in rehearsal'));
