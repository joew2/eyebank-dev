<?php
$source=file_get_contents(($argv[1]??getcwd()).'/wp-content/plugins/thrive-visual-editor/inc/functions.php');
if(!preg_match('/function tve_remove_theme_css\(\) \{.*?\n\}/s',$source,$m))throw new Exception('Function not found');
function get_template(){return 'eyedonation';}
function get_stylesheet_directory(){return '/wp-content/themes/eyedonation';}
function apply_filters($name,$remove,$src){
 if(empty($GLOBALS['path_fix']))return $remove;
 if(!$remove||!is_string($src))return $remove;
 $path=parse_url($src,PHP_URL_PATH);if(!is_string($path))return $remove;
 return str_starts_with($path,'/wp-content/themes/eyedonation/');
}
function wp_deregister_style($handle){$GLOBALS['removed'][]=$handle;}
eval($m[0]);
foreach([false,true] as $fixed){$GLOBALS['path_fix']=$fixed;
foreach(['https://eyebank:8890','https://www.eyedonation.org'] as $host){
 $GLOBALS['removed']=[];$wp_styles=(object)['queue'=>['cwsatp','cwsatp-forced','tve_landing_page_blank_v2','ed-base','wp-core'],'registered'=>[]];
 foreach(['cwsatp'=>'/wp-content/plugins/custom-accessibility-translation/assets/widget.css','cwsatp-forced'=>'/wp-content/plugins/custom-accessibility-translation/assets/widget.css','tve_landing_page_blank_v2'=>'/wp-content/plugins/thrive-visual-editor/landing-page/templates/css/blank_v2.css','ed-base'=>'/wp-content/themes/eyedonation/assets/css/base.css','wp-core'=>'/wp-includes/css/dashicons.min.css'] as $handle=>$path)$wp_styles->registered[$handle]=(object)['src'=>$host.$path];
 tve_remove_theme_css();$expected=($fixed||str_contains($host,'eyebank:8890'))?['ed-base']:['cwsatp','cwsatp-forced','tve_landing_page_blank_v2','ed-base','wp-core'];if($GLOBALS['removed']!==$expected)throw new Exception('Unexpected stylesheet removal result');echo json_encode(['path_fix'=>$fixed,'host'=>$host,'deregistered'=>$GLOBALS['removed']],JSON_UNESCAPED_SLASHES).PHP_EOL;
}

}
