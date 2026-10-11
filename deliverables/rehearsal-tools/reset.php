<?php
define('WP_INSTALLING',true);require __DIR__.'/bootstrap.php';foreach($wpdb->get_col("SHOW TABLES LIKE 'edrehearsal\\_%'") as $table){if(str_starts_with($table,'edrehearsal_'))$wpdb->query("DROP TABLE `".esc_sql($table)."`");}echo 'Removed only synthetic rehearsal tables.';
