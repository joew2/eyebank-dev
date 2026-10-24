<?php
/**
 * Plugin Name: Custom Newsletters CWS
 * Description: Editable newsletter PDF archive and accessible About Donation popup. Manage under Newsletters in the admin sidebar.
 * Version: 1.0.1
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */
defined('ABSPATH') || exit;

function cwsnl_register() {
    register_post_type('cws_newsletter', [
        'labels'=>['name'=>'Newsletters','singular_name'=>'Newsletter','add_new_item'=>'Add Newsletter','edit_item'=>'Edit Newsletter'],
        'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'menu_position'=>21,'menu_icon'=>'dashicons-media-document',
        'supports'=>['title','page-attributes'],'capability_type'=>'page','map_meta_cap'=>true,
    ]);
}
add_action('init','cwsnl_register');

/** Idempotent starter import. Existing records and edits are never overwritten. */
function cwsnl_seed() {
    cwsnl_register();
    if (get_option('cwsnl_imported_v1')) return;
    $rows=json_decode(file_get_contents(__DIR__.'/issues.json'),true);
    foreach ($rows as $i=>$row) {
        $key=sanitize_title($row['issue']);
        $existing=get_posts(['post_type'=>'cws_newsletter','post_status'=>'any','meta_key'=>'_cwsnl_seed','meta_value'=>$key,'fields'=>'ids','numberposts'=>1]);
        if ($existing) continue;
        $id=wp_insert_post(['post_type'=>'cws_newsletter','post_status'=>'publish','post_title'=>$row['title'],'menu_order'=>$i],true);
        if (is_wp_error($id)) return;
        update_post_meta($id,'_cwsnl_seed',$key);
        update_post_meta($id,'_cwsnl_issue',$row['issue']);
        // Store upload-relative paths so a database move keeps PDFs on the current site.
        update_post_meta($id,'_cwsnl_upload_path',$row['path']);
    }
    update_option('cwsnl_imported_v1',1,false);
}
register_activation_hook(__FILE__,'cwsnl_seed');

function cwsnl_pdf_url($id) {
    $attachment=(int)get_post_meta($id,'_cwsnl_pdf_id',true);
    if ($attachment && get_post_mime_type($attachment)==='application/pdf') return wp_get_attachment_url($attachment) ?: '';
    $path=get_post_meta($id,'_cwsnl_upload_path',true);
    return $path ? trailingslashit(wp_upload_dir()['baseurl']).$path : '';
}
add_action('add_meta_boxes',function () {
    add_meta_box('cwsnl-details','Newsletter issue and PDF',function ($post) {
        wp_nonce_field('cwsnl_save','cwsnl_nonce');
        echo '<p><label for="cwsnl-issue"><strong>Issue</strong></label><br><input required class="widefat" id="cwsnl-issue" name="cwsnl_issue" value="'.esc_attr(get_post_meta($post->ID,'_cwsnl_issue',true)).'" placeholder="Summer 2026"></p>';
        echo '<p>Use the title field for the newsletter name, such as Eye To Eye. Use Order in Page Attributes to sort issues (lowest first).</p>';
        echo '<input type="hidden" id="cwsnl-pdf-id" name="cwsnl_pdf_id" value="'.esc_attr(get_post_meta($post->ID,'_cwsnl_pdf_id',true)).'">';
        echo '<p><button type="button" class="button" id="cwsnl-select-pdf">Upload or select PDF</button> <button type="button" class="button" id="cwsnl-remove-pdf">Remove PDF</button></p>';
        echo '<p id="cwsnl-pdf-current">';
        $url=cwsnl_pdf_url($post->ID);
        if ($url) echo '<a href="'.esc_url($url).'" target="_blank" rel="noopener">View current PDF</a>';
        else echo 'No PDF selected.';
        echo '</p><input type="hidden" id="cwsnl-remove" name="cwsnl_remove" value="0"><p>Only published newsletters with a PDF appear in the archive.</p>';
    },'cws_newsletter','normal','high');
});
add_action('save_post_cws_newsletter',function ($id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_revision($id)) return;
    if (!isset($_POST['cwsnl_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cwsnl_nonce'])),'cwsnl_save') || !current_user_can('edit_post',$id)) return;
    update_post_meta($id,'_cwsnl_issue',sanitize_text_field(wp_unslash($_POST['cwsnl_issue'] ?? '')));
    $pdf=absint($_POST['cwsnl_pdf_id'] ?? 0);
    if ($pdf && get_post_mime_type($pdf)==='application/pdf') {
        update_post_meta($id,'_cwsnl_pdf_id',$pdf);
        delete_post_meta($id,'_cwsnl_upload_path');
    } elseif (!empty($_POST['cwsnl_remove'])) {
        delete_post_meta($id,'_cwsnl_pdf_id');delete_post_meta($id,'_cwsnl_upload_path');
    }
});
add_action('admin_enqueue_scripts',function () {
    $screen=get_current_screen();
    if (!$screen || $screen->post_type!=='cws_newsletter') return;
    wp_enqueue_media();
    wp_enqueue_script('cwsnl-admin',plugins_url('assets/admin.js',__FILE__),['media-editor'],'1.0.1',true);
});
add_filter('manage_cws_newsletter_posts_columns',function ($cols) {unset($cols['date']);$cols['issue']='Issue';$cols['pdf']='PDF';$cols['order']='Order';return $cols;});
add_action('manage_cws_newsletter_posts_custom_column',function ($col,$id) {
    if ($col==='issue') echo esc_html(get_post_meta($id,'_cwsnl_issue',true));
    if ($col==='order') echo (int)get_post_field('menu_order',$id);
    if ($col==='pdf') { $url=cwsnl_pdf_url($id);echo $url ? '<a href="'.esc_url($url).'">View PDF</a>' : 'No PDF'; }
},10,2);

function cwsnl_archive_url() {return add_query_arg('newsletters','1',home_url('/about-donation/')).'#cwsnl-dialog';}
add_filter('render_block_core/button',function ($html,$block) {
    if (!is_page('about-donation') || !in_array('ed-newsletter-issues',explode(' ',$block['attrs']['className'] ?? ''),true)) return $html;
    // Preserve the editable button label and styling; only replace its destination.
    return preg_replace('/href="[^"]*"/','href="'.esc_url(cwsnl_archive_url()).'" data-cwsnl-open aria-haspopup="dialog" aria-controls="cwsnl-dialog"',$html,1);
},10,2);
add_action('wp_enqueue_scripts',function () {
    if (!is_page('about-donation')) return;
    wp_enqueue_style('cwsnl',plugins_url('assets/newsletters.css',__FILE__),[],'1.0.1');
    wp_enqueue_script('cwsnl',plugins_url('assets/newsletters.js',__FILE__),[],'1.0.1',true);
});
add_action('wp_footer',function () {
    if (!is_page('about-donation')) return;
    $open=isset($_GET['newsletters']) && $_GET['newsletters']==='1';
    echo '<dialog id="cwsnl-dialog" class="cwsnl-dialog" aria-labelledby="cwsnl-title"'.($open?' open data-cwsnl-initial':'').'><div class="cwsnl-panel"><a class="cwsnl-close" data-cwsnl-close href="'.esc_url(home_url('/about-donation/')).'" aria-label="Close newsletter archive">×</a><h2 id="cwsnl-title">Previous newsletters</h2><p>Browse Eye To Eye and download an issue below.</p><div class="cwsnl-table-wrap"><table><caption class="screen-reader-text">Available newsletter PDFs, newest issues first</caption><thead><tr><th scope="col">Title</th><th scope="col">Issue</th><th scope="col">PDF</th></tr></thead><tbody>';
    $posts=get_posts(['post_type'=>'cws_newsletter','post_status'=>'publish','numberposts'=>-1,'orderby'=>['menu_order'=>'ASC','ID'=>'DESC']]);
    $count=0;
    foreach ($posts as $post) {
        $url=cwsnl_pdf_url($post->ID);if (!$url) continue;$count++;
        $issue=get_post_meta($post->ID,'_cwsnl_issue',true);
        echo '<tr><td>'.esc_html(get_the_title($post)).'</td><td>'.esc_html($issue).'</td><td><a class="cwsnl-download" href="'.esc_url($url).'" download aria-label="'.esc_attr('Download '.get_the_title($post).' — '.$issue.' (PDF)').'">Download <span class="screen-reader-text">PDF</span></a></td></tr>';
    }
    if (!$count) echo '<tr><td colspan="3">Newsletters will be available soon.</td></tr>';
    echo '</tbody></table></div><p class="cwsnl-note">Files are in PDF format.</p></div></dialog>';
});
