<?php
// Focused role matrix test. Run: php tests/access-policy.php
namespace {
    define('ABSPATH', __DIR__ . '/');
    define('WP_CONTENT_DIR', '/tmp/wp-content');
    class WP_User { public array $roles; public function __construct(array $roles){$this->roles=$roles;} public function exists(){return true;} }
    class WP_Error {}
    function __($text){return $text;}
    function wp_roles(){ return (object) array('roles' => $GLOBALS['roles']); }
    function wp_get_current_user(){ return $GLOBALS['user']; }
    function is_user_logged_in(){ return true; }
    function sanitize_key($value){ return strtolower(preg_replace('/[^a-z0-9_\-]/', '', $value)); }
}
namespace SWS\DownloadsPortal {
function wp_roles(){ return \wp_roles(); }
function wp_get_current_user(){ return \wp_get_current_user(); }
function is_user_logged_in(){ return \is_user_logged_in(); }
function sanitize_key($value){ return \sanitize_key($value); }
require dirname(__DIR__) . '/includes/class-access.php';
$GLOBALS['roles'] = array(
    'administrator'=>array('name'=>'Administrator'),'eb_staff'=>array('name'=>'EB Staff'),
    'eb_staff_bod_only'=>array('name'=>'EB Staff BOD ONLY'),'board_of_director'=>array('name'=>'Board of Director'),
    'medical_advisory_board'=>array('name'=>'Medical Advisory Board'),'subscriber'=>array('name'=>'Subscriber'),
);
$cases = array(
    array(array('administrator'),array('bod'=>'manage','mab'=>'manage')),
    array(array('eb_staff'),array('bod'=>'manage','mab'=>'manage')),
    array(array('eb_staff_bod_only'),array('bod'=>'manage')),
    array(array('board_of_director'),array('bod'=>'read')),
    array(array('medical_advisory_board'),array('mab'=>'read')),
    array(array('subscriber'),array()),
    array(array('board_of_director','medical_advisory_board'),array('bod'=>'read','mab'=>'read')),
);
$failed=array(); foreach($cases as [$case_roles,$expected]){$actual=Access::permissions(new \WP_User($case_roles));if($actual!==$expected)$failed[]=implode(',',$case_roles).' expected '.json_encode($expected).' got '.json_encode($actual);}
$GLOBALS['roles']['board_of_director']['name']='Reused role'; if(Access::permissions(new \WP_User(array('board_of_director')))!==array())$failed[]='label guard';
if($failed){fwrite(STDERR,"FAIL: ".implode(', ',$failed)."\n");exit(1);} echo "PASS: exact role access matrix\n";
}
