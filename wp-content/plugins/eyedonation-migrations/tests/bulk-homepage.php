<?php
/** Build the current homepage using core's block/HTML parser without database/media lookups. */
define('ABSPATH',dirname(__DIR__,4).'/');define('WPINC','wp-includes');
require ABSPATH.WPINC.'/plugin.php';
foreach(['class-wp-block-parser-block.php','class-wp-block-parser-frame.php','class-wp-block-parser.php','blocks.php','kses.php','compat-utf8.php','utf8.php','class-wp-token-map.php','html-api/html5-named-character-references.php','html-api/class-wp-html-attribute-token.php','html-api/class-wp-html-span.php','html-api/class-wp-html-doctype-info.php','html-api/class-wp-html-text-replacement.php','html-api/class-wp-html-decoder.php','html-api/class-wp-html-tag-processor.php'] as $file)require ABSPATH.WPINC.'/'.$file;
function get_theme_file_path($p){return ABSPATH.'wp-content/themes/eyedonation'.$p;}
function get_theme_file_uri($p){return 'https://example.test/wp-content/themes/eyedonation'.$p;}
function home_url($p='/'){return 'https://example.test'.$p;}
function wp_json_encode($v,$flags=0){return json_encode($v,$flags);}
function esc_url($v){return htmlspecialchars($v,ENT_QUOTES);}
function esc_attr($v){return htmlspecialchars($v,ENT_QUOTES);}
function add_query_arg($k,$v,$url){return $url.'?'.urlencode($k).'='.urlencode($v);}
function wp_parse_url($url,$part=-1){return parse_url($url,$part);}
function _doing_it_wrong(...$args){throw new RuntimeException('Core parser usage error');}
foreach(['approved-links.php','donor-panel.php','portable-pages.php','homepage-support-photo.php','homepage-support-responsive-v2.php'] as $file)require get_theme_file_path('/inc/'.$file);
$content=ed_update_homepage_support_responsive_v2(ed_portable_homepage_content(),true);
foreach(['{{','/placeholder.svg','eyedonation-placeholder','Eyebank:8890'] as $bad)if(strpos($content,$bad)!==false)throw new RuntimeException('Unresolved homepage content: '.$bad);
if(strpos($content,'main_hero_photo_v4.jpg')===false||strpos($content,'homepage-support-v4/support-desktop.jpg')===false)throw new RuntimeException('Latest design images missing');
preg_match_all('~https://example.test/wp-content/themes/eyedonation/[^"<>\s]+~',$content,$matches);
foreach(array_unique($matches[0]) as $url){$path=html_entity_decode(parse_url($url,PHP_URL_PATH));if(!is_file(ABSPATH.ltrim($path,'/')))throw new RuntimeException('Bundled asset missing: '.$path);}
echo "PASS: latest editable homepage builds without database/media lookups, all bundled assets exist, no unresolved placeholders, current hero/support crop included.\n";
