<?php
define('ABSPATH', dirname(__DIR__, 5) . '/');
function sanitize_text_field($v){return trim(strip_tags((string)$v));}
function __($v,$domain=null){return $v;}
function wp_date($format,$timestamp=null,$timezone=null){$tz=$timezone instanceof DateTimeZone?$timezone:new DateTimeZone('UTC');return (new DateTimeImmutable('@'.($timestamp??time())))->setTimezone($tz)->format($format);}
class WP_Error {public function __construct(public string $code='',public string $message=''){} public function get_error_message(){return $this->message;}}
function is_wp_error($v){return $v instanceof WP_Error;}
require_once dirname(__DIR__) . '/includes/class-date-windows.php';
require_once dirname(__DIR__) . '/includes/class-api.php';
require_once dirname(__DIR__) . '/includes/class-renderer.php';

