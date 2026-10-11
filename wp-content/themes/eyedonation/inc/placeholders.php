<?php
defined('ABSPATH') || exit;
add_filter('template_include',function($template){
 if(isset($_GET['eyedonation-placeholder'])&&is_string($_GET['eyedonation-placeholder']))return __DIR__.'/placeholder-template.php';
 return $template;
});
