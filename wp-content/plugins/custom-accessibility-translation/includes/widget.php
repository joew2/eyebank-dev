<?php
defined('ABSPATH') || exit;
function cwsatp_defaults() {
    return ['enabled'=>1, 'placement'=>'footer', 'mobile_bottom'=>1, 'side'=>'right', 'offset'=>160, 'bottom_offset'=>5, 'remember'=>1];
}
function cwsatp_settings() { return wp_parse_args(get_option('cwsatp_settings', []), cwsatp_defaults()); }
function cwsatp_import_legacy_mappings() {
    global $wpdb;
    if (get_option('cwsatp_legacy_imported')) return;
    $legacy = $wpdb->prefix . 'ctnl_translation_mappings';
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($legacy))) !== $legacy) return;
    $rows = $wpdb->get_results("SELECT english_path, spanish_path, enabled FROM {$legacy}", ARRAY_A);
    $known = [];
    foreach (cwsatp_get_all_mappings() as $row) {
        $known[$row['english_path']] = true; $known[$row['spanish_path']] = true;
    }
    foreach ($rows as $row) {
        $en = cwsatp_normalize_path($row['english_path']); $es = cwsatp_normalize_path($row['spanish_path']);
        if (!$en || !$es || $en === $es || isset($known[$en]) || isset($known[$es])) continue;
        $now = current_time('mysql');
        $result = $wpdb->insert(cwsatp_get_table_name(), ['english_path'=>$en,'spanish_path'=>$es,'enabled'=>(int)(bool)$row['enabled'],'created_at'=>$now,'updated_at'=>$now], ['%s','%s','%d','%s','%s']);
        if ($result === false) return;
        $known[$en] = true; $known[$es] = true;
    }
    update_option('cwsatp_legacy_imported', 1);
}
function cwsatp_sanitize_settings($value) {
    $value = is_array($value) ? $value : [];
    $placement = $value['placement'] ?? 'footer';
    return ['mobile_bottom'=>empty($value['mobile_bottom'])?0:1,
        'placement'=>in_array($placement,['footer','screen','viewport-bottom'],true)?$placement:'footer',
        'enabled'=>empty($value['enabled'])?0:1, 'remember'=>empty($value['remember'])?0:1,
        'side'=>($value['side']??'right')==='left'?'left':'right',
        'offset'=>max(0,min(500,(int)($value['offset']??160))),
        'bottom_offset'=>max(0,min(100,(int)($value['bottom_offset']??5)))];
}
add_action('admin_init', function () {
    register_setting('cwsatp_widget', 'cwsatp_settings', ['sanitize_callback'=>'cwsatp_sanitize_settings']);
});
function cwsatp_settings_form() {
    $s = cwsatp_settings();
    echo '<div class="wrap"><h1>Custom Accessibility Translation CWS (ATP)</h1><p>Accessibility controls and language links in one widget. Existing translation pairs are copied on activation; the original records are preserved.</p><form action="'.esc_url(admin_url('options.php')).'" method="post">';
    settings_fields('cwsatp_widget');
    echo '<p><label><input type="checkbox" name="cwsatp_settings[enabled]" value="1" '.checked($s['enabled'],1,false).'> Show widget</label></p>';
    echo '<h2>Desktop position</h2><p><label>Placement <select name="cwsatp_settings[placement]">';
    foreach (['footer'=>'Footer — scrolls with the page','screen'=>'Top of viewport — always visible','viewport-bottom'=>'Bottom of viewport — always visible'] as $value=>$label) {
        echo '<option value="'.esc_attr($value).'" '.selected($s['placement'],$value,false).'>'.esc_html($label).'</option>';
    }
    echo '</select></label></p><p class="description">The viewport is the visible browser window. Bottom of viewport keeps both icons in the same bottom corner as you scroll; it does not wait for the footer.</p>';
    echo '<p><label>Widget side <select name="cwsatp_settings[side]"><option value="right" '.selected($s['side'],'right',false).'>Right</option><option value="left" '.selected($s['side'],'left',false).'>Left</option></select></label></p>';
    echo '<p><label>Top gap (pixels, top-of-viewport mode only) <input type="number" min="0" max="500" name="cwsatp_settings[offset]" value="'.esc_attr($s['offset']).'"></label></p>';
    echo '<p><label>Bottom gap (pixels, bottom-of-viewport mode only) <input type="number" min="0" max="100" name="cwsatp_settings[bottom_offset]" value="'.esc_attr($s['bottom_offset']).'"></label></p><p class="description">Bottom placement opens the accessibility panel and language list upward. The side gap is 5px.</p>';
    echo '<h2>Tablets and phones</h2><p><label><input type="checkbox" name="cwsatp_settings[mobile_bottom]" value="1" '.checked($s['mobile_bottom'],1,false).'> Use the bottom-right viewport corner on tablets and phones</label></p><p class="description">At widths of 1024px or less, this overrides the desktop settings with a 5px gap from the bottom and right edges. Turn it off to use the desktop placement and gaps on all screen sizes.</p>';
    echo '<h2>Visitor preferences</h2><p><label><input type="checkbox" name="cwsatp_settings[remember]" value="1" '.checked($s['remember'],1,false).'> Remember visitor preferences on this device</label></p>';
    submit_button('Save Widget Settings');
    echo '</form><p>Language switching appears only for enabled page pairs below. Unmapped pages never link to an unrelated translation. These links open your existing translated pages; they do not translate content automatically.</p></div>';
}
function cwsatp_language_pair() {
    foreach (cwsatp_get_current_paths() as $path) {
        foreach (['spanish'=>'en','english'=>'es'] as $direction=>$current) {
            $row = cwsatp_get_mapping_for_paths([$path],$direction);
            if ($row) return ['current'=>$current,'en'=>cwsatp_get_frontend_url($row['english_path']),'es'=>cwsatp_get_frontend_url($row['spanish_path'])];
        }
    }
    return null;
}
add_action('wp_enqueue_scripts', function () {
    if (!cwsatp_settings()['enabled']) return;
    $base = plugin_dir_url(dirname(__FILE__)) . 'assets/';
    wp_enqueue_style('cwsatp', $base.'widget.css', [], filemtime(dirname(__DIR__).'/assets/widget.css'));
    wp_enqueue_script('cwsatp', $base.'widget.js', [], filemtime(dirname(__DIR__).'/assets/widget.js'), true);
});
add_action('wp_footer', function () {
    $s = cwsatp_settings(); if (!$s['enabled']) return;
    $pair = cwsatp_language_pair(); $spanish = $pair && $pair['current'] === 'es';
    $labels = $spanish ? ['Accesibilidad','Cerrar','Aumentar texto','Reducir texto','Alto contraste','Escala de grises','Subrayar enlaces','Fuente legible','Restablecer','Idioma'] : ['Accessibility','Close','Increase text','Decrease text','High contrast','Grayscale','Underline links','Readable font','Reset','Language'];
    ?>
    <aside id="cwsatp-widget" class="cwsatp-side-<?php echo esc_attr($s['side']); ?>" style="--cwsatp-top:<?php echo absint($s['offset']); ?>px;--cwsatp-bottom:<?php echo absint($s['bottom_offset']); ?>px" data-mobile-bottom="<?php echo absint($s['mobile_bottom']); ?>" data-placement="<?php echo esc_attr($s['placement']); ?>" data-remember="<?php echo absint($s['remember']); ?>" aria-label="<?php echo esc_attr($labels[0].' / '.$labels[9]); ?>">
      <button class="cwsatp-trigger" type="button" aria-expanded="false" aria-controls="cwsatp-panel" aria-label="<?php echo esc_attr($labels[0]); ?>"><svg viewBox="0 0 32 40" aria-hidden="true" focusable="false"><circle cx="14" cy="5" r="4" fill="currentColor"/><path d="M13 12v12h13l4 10M14 18h10M10 17a11 11 0 1 0 12 18" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      <section id="cwsatp-panel" hidden aria-labelledby="cwsatp-title">
        <div class="cwsatp-panel-heading"><h2 id="cwsatp-title"><?php echo esc_html($labels[0]); ?></h2><button type="button" class="cwsatp-close" aria-label="<?php echo esc_attr($labels[1]); ?>">×</button></div>
        <div class="cwsatp-controls">
        <?php foreach (['increase'=>2,'decrease'=>3,'contrast'=>4,'grayscale'=>5,'links'=>6,'font'=>7,'reset'=>8] as $action=>$index): ?>
          <button type="button" data-action="<?php echo esc_attr($action); ?>" <?php if(in_array($action,['contrast','grayscale','links','font'],true)) echo 'aria-pressed="false"'; ?>><?php echo esc_html($labels[$index]); ?></button>
        <?php endforeach; ?>
        </div><output class="cwsatp-status" aria-live="polite"></output>
      </section>
      <?php if ($pair): ?>
      <div class="cwsatp-language"><button type="button" class="cwsatp-language-trigger" aria-expanded="false" aria-controls="cwsatp-languages" aria-label="<?php echo esc_attr($labels[9]); ?>"><?php echo $spanish?'ES':'EN'; ?> <svg viewBox="0 0 12 12" aria-hidden="true"><path d="m2 4 4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.5"/></svg></button>
      <ul id="cwsatp-languages" hidden><?php foreach(['en'=>'English','es'=>'Español'] as $code=>$label): ?><li><a href="<?php echo esc_url($pair[$code]); ?>" lang="<?php echo esc_attr($code); ?>" hreflang="<?php echo esc_attr($code); ?>" <?php if($pair['current']===$code) echo 'aria-current="page"'; ?>><?php echo esc_html($label); ?></a></li><?php endforeach; ?></ul></div>
      <?php endif; ?>
    </aside>
    <?php
}, 10);
