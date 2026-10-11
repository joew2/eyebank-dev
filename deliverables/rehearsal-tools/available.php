<?php
require __DIR__.'/bootstrap.php';$q=new WP_Query;$GLOBALS['wp_the_query']=$q;$GLOBALS['wp_query']=$q;$q->query(['s'=>'Needle']);$groups=\EyeDonation\Search\available_groups($q);if(!isset($groups['pages'])||isset($groups['research']))throw new Exception('Availability probe did not hide irrelevant group');echo "PASS: SQL group probes hide irrelevant groups.\n";
