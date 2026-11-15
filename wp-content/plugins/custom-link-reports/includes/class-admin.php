<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Admin {
    public static function menu(): void {
        add_menu_page(__('Link Reports', 'custom-link-reports'), __('Link Reports', 'custom-link-reports'), 'manage_options', 'custom-link-reports', array(self::class, 'dashboard'), 'dashicons-chart-bar', 58);
        add_submenu_page('custom-link-reports', __('Dashboard', 'custom-link-reports'), __('Dashboard', 'custom-link-reports'), 'manage_options', 'custom-link-reports', array(self::class, 'dashboard'));
        add_submenu_page('custom-link-reports', __('Reports', 'custom-link-reports'), __('Reports', 'custom-link-reports'), 'manage_options', 'custom-link-reports-reports', array(self::class, 'reports'));
        add_submenu_page('custom-link-reports', __('Settings', 'custom-link-reports'), __('Settings', 'custom-link-reports'), 'manage_options', 'custom-link-reports-settings', array(self::class, 'settings'));
        add_submenu_page('custom-link-reports', __('Activity', 'custom-link-reports'), __('Activity', 'custom-link-reports'), 'manage_options', 'custom-link-reports-activity', array(self::class, 'activity'));
        add_submenu_page(null, __('Edit Link Report', 'custom-link-reports'), __('Edit Link Report', 'custom-link-reports'), 'manage_options', 'custom-link-reports-edit', array(self::class, 'editor'));
    }

    /** Place this plugin immediately after the existing Newsletters menu without assuming its slug. */
    public static function menu_order(array $order): array {
        if (!$order || !in_array('custom-link-reports', $order, true)) return $order;
        global $menu;
        $newsletter_slug = '';
        foreach ((array) $menu as $item) {
            $label = isset($item[0]) ? strtolower(trim(wp_strip_all_tags((string) $item[0]))) : '';
            $slug = isset($item[2]) ? (string) $item[2] : '';
            if ($label === 'newsletters' || str_contains(strtolower($slug), 'newsletter')) {
                $newsletter_slug = $slug;
                break;
            }
        }
        $order = array_values(array_diff($order, array('custom-link-reports')));
        $after = $newsletter_slug !== '' ? array_search($newsletter_slug, $order, true) : false;
        if ($after === false) return array_merge($order, array('custom-link-reports'));
        array_splice($order, $after + 1, 0, array('custom-link-reports'));
        return $order;
    }

    public static function assets(string $hook): void {
        if (!str_contains($hook, 'custom-link-reports')) return;
        wp_enqueue_style('cws-clr-admin', plugins_url('assets/css/admin.css', FILE), array(), VERSION);
        wp_enqueue_script('cws-clr-admin', plugins_url('assets/js/admin.js', FILE), array(), VERSION, true);
        wp_enqueue_media();
    }

    private static function guard(): void {
        if (!current_user_can('manage_options')) wp_die(esc_html__('You are not allowed to access link reports.', 'custom-link-reports'), '', array('response' => 403));
    }

    public static function handle_actions(): void {
        if (!is_admin() || empty($_POST['cws_clr_action'])) return;
        self::guard();
        check_admin_referer('cws_clr_action');
        $action = sanitize_key(wp_unslash($_POST['cws_clr_action']));
        try {
            if ($action === 'save_settings') self::save_settings();
            elseif ($action === 'remove_key') { Settings::remove_key(); self::redirect('custom-link-reports-settings', 'key_removed'); }
            elseif ($action === 'test_connection') self::test_connection();
            elseif ($action === 'save_report') self::save_report();
            elseif ($action === 'delete_report') self::delete_report();
            elseif ($action === 'duplicate_report') self::duplicate_report();
            elseif ($action === 'toggle_report') self::toggle_report();
            elseif ($action === 'preview_report') self::preview_report();
            elseif ($action === 'dashboard_preview') self::dashboard_preview();
            elseif ($action === 'send_report') self::send_report(false);
            elseif ($action === 'test_report') self::send_report(true);
        } catch (\Throwable $e) {
            self::redirect(sanitize_key($_POST['return_page'] ?? 'custom-link-reports'), 'error', $e->getMessage());
        }
    }

    private static function save_settings(): void {
        $old = Settings::get(); $in = wp_unslash($_POST['settings'] ?? array());
        if (!is_array($in)) $in = array();
        $settings = $old;
        $settings['default_domain'] = preg_replace('/[^0-9]/', '', (string) ($in['default_domain'] ?? ''));
        $tz = sanitize_text_field((string) ($in['timezone'] ?? 'UTC'));
        $settings['timezone'] = in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC';
        $settings['recipients'] = implode(', ', Reports::emails($in['recipients'] ?? ''));
        $settings['heading'] = sanitize_text_field((string) ($in['heading'] ?? ''));
        $settings['accent'] = sanitize_hex_color((string) ($in['accent'] ?? '')) ?: '#0057b8';
        $settings['logo_id'] = absint($in['logo_id'] ?? 0);
        $settings['footer'] = wp_kses((string) ($in['footer'] ?? ''), Reports::allowed_html());
        $settings['retention_days'] = min(3650, max(1, absint($in['retention_days'] ?? 90)));
        $settings['cache_minutes'] = min(1440, max(1, absint($in['cache_minutes'] ?? 15)));
        $settings['global_pause'] = !empty($in['global_pause']);
        $settings['delete_on_uninstall'] = !empty($in['delete_on_uninstall']);
        $new_key = trim((string) ($in['api_key'] ?? ''));
        Settings::update($settings);
        if ($new_key !== '') Settings::replace_key($new_key);
        self::redirect('custom-link-reports-settings', 'saved');
    }

    private static function test_connection(): void {
        $key = trim((string) wp_unslash($_POST['api_key'] ?? ''));
        $api = new Api($key !== '' ? $key : null);
        $domains = $api->domains(true);
        $settings = Settings::get(); $settings['last_check'] = time();
        if (is_wp_error($domains)) { $settings['connection_error'] = $domains->get_error_message(); $settings['domain_count'] = null; Settings::update($settings); self::redirect('custom-link-reports-settings', 'error', $domains->get_error_message()); }
        $settings['connection_error'] = ''; $settings['domain_count'] = count($domains); Settings::update($settings);
        self::redirect('custom-link-reports-settings', 'connected');
    }

    private static function save_report(): void {
        $id = absint($_POST['report_id'] ?? 0); $name = sanitize_text_field(wp_unslash($_POST['report_name'] ?? ''));
        if ($name === '') throw new \RuntimeException(__('Report name is required.', 'custom-link-reports'));
        $config = wp_unslash($_POST['config'] ?? array()); if (!is_array($config)) $config = array();
        $unknown = Renderer::unknown_tokens((string) ($config['subject'] ?? ''));
        if ($unknown) throw new \RuntimeException(sprintf(__('Unknown subject token(s): %s', 'custom-link-reports'), implode(', ', $unknown)));
        if (!$id) $id = wp_insert_post(array('post_type' => REPORT_TYPE, 'post_status' => 'private', 'post_title' => $name), true);
        else wp_update_post(array('ID' => $id, 'post_title' => $name));
        if (is_wp_error($id)) throw new \RuntimeException($id->get_error_message());
        Reports::save((int) $id, $config);
        self::redirect('custom-link-reports-edit', 'saved', '', array('id' => (int) $id));
    }

    private static function delete_report(): void {
        $id = absint($_POST['report_id'] ?? 0); Scheduler::clear_report($id); wp_delete_post($id, true); self::redirect('custom-link-reports-reports', 'deleted');
    }

    private static function duplicate_report(): void {
        $id = absint($_POST['report_id'] ?? 0); $post = get_post($id); if (!$post || $post->post_type !== REPORT_TYPE) throw new \RuntimeException('Report not found.');
        $new = wp_insert_post(array('post_type' => REPORT_TYPE, 'post_status' => 'private', 'post_title' => $post->post_title . ' Copy'), true);
        $config = Reports::get($id); $config['schedule_enabled'] = false; $config['paused'] = true; Reports::save((int) $new, $config);
        self::redirect('custom-link-reports-edit', 'duplicated', '', array('id' => (int) $new));
    }

    private static function toggle_report(): void {
        $id = absint($_POST['report_id'] ?? 0); $config = Reports::get($id);
        $config['paused'] = empty($config['paused']); $config['schedule_enabled'] = true;
        Reports::save($id, $config); self::redirect('custom-link-reports-reports', 'saved');
    }

    private static function send_report(bool $test): void {
        $id = absint($_POST['report_id'] ?? 0); $config = Reports::get($id); $tz = new \DateTimeZone($config['timezone']);
        $start = sanitize_text_field(wp_unslash($_POST['date_start'] ?? '')); $end = sanitize_text_field(wp_unslash($_POST['date_end'] ?? ''));
        if (!$start || !$end) [$start, $end] = Date_Windows::period('previous_7', new \DateTimeImmutable('now', $tz));
        $recipient = $test ? sanitize_email(wp_unslash($_POST['test_recipient'] ?? '')) : null;
        if ($test && !is_email($recipient)) throw new \RuntimeException(__('Enter a valid test recipient.', 'custom-link-reports'));
        $result = Runner::manual($id, array($start, $end), $recipient, !empty($_POST['refresh']));
        if (is_wp_error($result)) throw new \RuntimeException($result->get_error_message());
        self::redirect('custom-link-reports-activity', $test ? 'test_sent' : 'sent');
    }

    private static function preview_report(): void {
        $id = absint($_POST['report_id'] ?? 0); $config = Reports::get($id);
        $start = sanitize_text_field(wp_unslash($_POST['date_start'] ?? '')); $end = sanitize_text_field(wp_unslash($_POST['date_end'] ?? ''));
        $data = Renderer::aggregate($config, $start, $end, !empty($_POST['refresh']));
        if (is_wp_error($data)) throw new \RuntimeException($data->get_error_message());
        set_transient('cws_clr_preview_' . get_current_user_id() . '_' . $id, array('html' => Renderer::html(Reports::title($id), $config, $data), 'created' => time(), 'period' => array($start, $end)), 15 * MINUTE_IN_SECONDS);
        self::redirect('custom-link-reports-edit', 'previewed', '', array('id' => $id));
    }

    private static function dashboard_preview(): void {
        $domain = preg_replace('/[^0-9]/', '', (string) wp_unslash($_POST['domain_id'] ?? ''));
        $ids = array_map('sanitize_text_field', (array) wp_unslash($_POST['link_ids'] ?? array()));
        $start = sanitize_text_field(wp_unslash($_POST['date_start'] ?? '')); $end = sanitize_text_field(wp_unslash($_POST['date_end'] ?? ''));
        $links = self::load_links($domain); if (is_wp_error($links)) throw new \RuntimeException($links->get_error_message());
        $selected = array_values(array_filter($links, static fn($link) => in_array($link['id'], $ids, true)));
        if (!$selected) throw new \RuntimeException(__('Select at least one link.', 'custom-link-reports'));
        $settings = Settings::get(); $config = Reports::defaults(); $config['domain_id'] = $domain; $config['links'] = $selected; $config['timezone'] = $settings['timezone'];
        $data = Renderer::aggregate($config, $start, $end, !empty($_POST['refresh'])); if (is_wp_error($data)) throw new \RuntimeException($data->get_error_message());
        set_transient('cws_clr_dashboard_' . get_current_user_id(), array('domain' => $domain, 'ids' => $ids, 'start' => $start, 'end' => $end, 'data' => $data, 'html' => Renderer::html(__('Dashboard analytics', 'custom-link-reports'), $config, $data)), 15 * MINUTE_IN_SECONDS);
        self::redirect('custom-link-reports', 'previewed', '', array('domain_id' => $domain));
    }

    private static function redirect(string $page, string $notice, string $message = '', array $extra = array()): void {
        $args = array_merge(array('page' => $page, 'cws_clr_notice' => $notice), $extra);
        if ($message !== '') $args['cws_clr_message'] = $message;
        wp_safe_redirect(add_query_arg($args, admin_url('admin.php'))); exit;
    }

    private static function header(string $title): void {
        self::guard(); echo '<div class="wrap cws-clr"><h1>' . esc_html($title) . '</h1>'; self::notice();
    }
    private static function footer(): void { echo '</div>'; }
    private static function notice(): void {
        $key = sanitize_key($_GET['cws_clr_notice'] ?? ''); if (!$key) return;
        $messages = array('saved'=>'Saved.','connected'=>'Connection successful.','key_removed'=>'API key removed; schedules stopped.','deleted'=>'Report deleted.','duplicated'=>'Report duplicated and paused.','previewed'=>'Live preview refreshed.','sent'=>'Report submitted to wp_mail().','test_sent'=>'Test submitted to wp_mail().');
        $error = $key === 'error'; $text = $error ? sanitize_text_field(wp_unslash($_GET['cws_clr_message'] ?? 'An error occurred.')) : ($messages[$key] ?? '');
        if ($text) echo '<div class="notice ' . ($error ? 'notice-error' : 'notice-success') . ' is-dismissible"><p>' . esc_html($text) . '</p></div>';
    }

    public static function dashboard(): void {
        self::header(__('Link Reports Dashboard', 'custom-link-reports')); $settings = Settings::get(); $reports = Reports::all(); $runs = Runs::recent(5); $domains=Settings::has_key()?(new Api())->domains():array();
        echo '<div class="cws-clr-cards"><div><strong>Connection</strong><span>' . esc_html(Settings::has_key() ? ($settings['connection_error'] ? 'Needs attention' : 'Key stored') : 'Not connected') . '</span></div><div><strong>Saved reports</strong><span>' . count($reports) . '</span></div><div><strong>Timezone</strong><span>' . esc_html($settings['timezone']) . '</span></div></div>';
        if (!Settings::has_key()) echo '<div class="notice notice-warning inline"><p>Connect a Short.io key in <a href="' . esc_url(admin_url('admin.php?page=custom-link-reports-settings')) . '">Settings</a>.</p></div>';
        echo '<h2>First-use checklist</h2><ol><li>Connect and test a private Short.io key.</li><li>Select a default domain.</li><li>Create a report and choose links.</li><li>Preview or send a test.</li><li>Explicitly enable its weekly schedule.</li></ol>';
        echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=custom-link-reports-edit')) . '">Create report</a> <a class="button" href="' . esc_url(admin_url('admin.php?page=custom-link-reports-reports')) . '">View reports</a></p>';
        if(Settings::has_key()&&!is_wp_error($domains)){ $domain=preg_replace('/[^0-9]/','',(string)($_GET['domain_id']??$settings['default_domain']));$links=$domain?self::load_links($domain):array();[$start,$end]=Date_Windows::period('previous_7',new \DateTimeImmutable('now',new \DateTimeZone($settings['timezone'])));$saved=get_transient('cws_clr_dashboard_'.get_current_user_id());echo '<section><h2>Analytics explorer</h2><p class="description">Choose a domain, select one or more links, and load their current Short.io statistics.</p><form method="get"><input type="hidden" name="page" value="custom-link-reports"><label for="cws-clr-domain"><strong>Domain</strong></label> <select id="cws-clr-domain" name="domain_id">';foreach($domains as$d)echo '<option value="'.esc_attr($d['id']).'" '.selected($domain,$d['id'],false).'>'.esc_html($d['hostname']).'</option>';echo '</select> <button class="button">Load domain links</button></form>';if($domain&&!is_wp_error($links)){echo '<form method="post">';wp_nonce_field('cws_clr_action');echo '<input type="hidden" name="cws_clr_action" value="dashboard_preview"><input type="hidden" name="return_page" value="custom-link-reports"><input type="hidden" name="domain_id" value="'.esc_attr($domain).'"><div class="cws-clr-link-tools" data-cws-link-tools><label class="screen-reader-text" for="cws-clr-dash-search">Filter loaded links</label><input id="cws-clr-dash-search" type="search" data-cws-link-search placeholder="Filter by link name or URL"><button type="button" class="button" data-cws-link-clear>Clear</button><span class="cws-clr-link-status" data-cws-link-status aria-live="polite"></span></div><div class="cws-clr-links">';foreach($links as$link)echo '<label data-cws-link-row><input type="checkbox" name="link_ids[]" value="'.esc_attr($link['id']).'"> '.esc_html($link['label'].' — '.$link['short_url']).'</label>';echo '</div><div class="cws-clr-field"><div class="cws-clr-label">Inclusive dates</div><div><input type="date" name="date_start" required value="'.esc_attr($saved['start']??$start).'"> – <input type="date" name="date_end" required value="'.esc_attr($saved['end']??$end).'"><br><label><input type="checkbox" name="refresh" value="1"> Ignore cached statistics for this request</label></div></div>';submit_button('Load selected analytics');echo '</form>';}if(is_array($saved)&&!empty($saved['data'])){self::daily_chart($saved['data']);echo '<div class="cws-clr-preview">'.$saved['html'].'</div>';}echo '</section>';}
        echo '<h2>Recent activity</h2>'; self::runs_table($runs); self::footer();
    }

    public static function reports(): void {
        self::header(__('Reports', 'custom-link-reports')); echo '<p><a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=custom-link-reports-edit')) . '">Add report</a></p>';
        echo '<table class="widefat striped"><thead><tr><th>Name</th><th>Links</th><th>Recipients</th><th>Schedule</th><th>Last run</th><th>Next run</th><th>Actions</th></tr></thead><tbody>';
        foreach (Reports::all() as $post) { $c=Reports::get($post->ID);$last=Runs::last_for_report($post->ID);$recipient_summary=implode(', ',$c['recipients']);if($c['bcc_recipients'])$recipient_summary.=($recipient_summary?' · ':'').count($c['bcc_recipients']).' private BCC';echo '<tr><td><strong>' . esc_html($post->post_title) . '</strong></td><td>' . count($c['links']) . '</td><td>' . esc_html($recipient_summary) . '</td><td>' . esc_html($c['schedule_enabled'] && !$c['paused'] ? 'Enabled' : 'Paused') . '</td><td>'.esc_html($last?$last->status:'—').'</td><td>' . esc_html($c['next_run'] ? wp_date('M, D j, Y g:i a T', $c['next_run'], new \DateTimeZone($c['timezone'])) : '—') . '</td><td><a href="' . esc_url(admin_url('admin.php?page=custom-link-reports-edit&id='.$post->ID)) . '">Edit / Preview / Send</a> ';self::small_form('toggle_report',$post->ID,$c['paused']?'Resume':'Pause'); self::small_form('duplicate_report',$post->ID,'Duplicate'); self::small_form('delete_report',$post->ID,'Delete','delete'); echo '</td></tr>'; }
        if (!Reports::all()) echo '<tr><td colspan="7">No reports yet.</td></tr>'; echo '</tbody></table>'; self::footer();
    }

    public static function settings(): void {
        self::header(__('Link Reports Settings', 'custom-link-reports')); $s=Settings::get(); $domains=Settings::has_key() ? (new Api())->domains() : array();
        echo '<p>Status: <strong>' . esc_html(Settings::has_key() ? ($s['connection_error'] ?: 'API key stored') : 'Not connected') . '</strong>'; if ($s['last_check']) echo ' · Last check ' . esc_html(wp_date('M, D j, Y g:i a T',$s['last_check'])); echo '</p>';
        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) echo '<div class="notice notice-warning inline"><p>DISABLE_WP_CRON is enabled. Confirm a system cron calls wp-cron.php.</p></div>';
        if (!Crypto::available()) echo '<div class="notice notice-error inline"><p>PHP sodium is unavailable; the API key cannot be stored securely.</p></div>';
        echo '<form method="post">'; wp_nonce_field('cws_clr_action'); echo '<input type="hidden" name="cws_clr_action" value="save_settings"><input type="hidden" name="return_page" value="custom-link-reports-settings"><table class="form-table">';
        self::row('API key','<input class="regular-text" type="password" autocomplete="new-password" name="settings[api_key]" value="" placeholder="'.(Settings::has_key()?'Stored — leave blank to preserve':'Paste private key').'"><p class="description">The stored key is never redisplayed.</p>');
        $opts='<option value="">— Select —</option>'; if (!is_wp_error($domains)) foreach($domains as $d)$opts.='<option value="'.esc_attr($d['id']).'" '.selected($s['default_domain'],$d['id'],false).'>'.esc_html($d['hostname']).'</option>'; self::row('Default domain','<select name="settings[default_domain]">'.$opts.'</select>');
        self::row('Reporting timezone','<select name="settings[timezone]">'.wp_timezone_choice($s['timezone'],get_user_locale()).'</select>');
        self::row('Default recipients','<textarea class="large-text" name="settings[recipients]">'.esc_textarea($s['recipients']).'</textarea>');
        self::row('Report heading','<input class="regular-text" name="settings[heading]" value="'.esc_attr($s['heading']).'">');
        self::row('Accent color','<input type="color" name="settings[accent]" value="'.esc_attr($s['accent']).'">');
        self::row('Logo','<input type="hidden" id="cws-clr-logo-id" name="settings[logo_id]" value="'.absint($s['logo_id']).'"><button type="button" class="button" data-cws-clr-logo>Choose Media Library image</button>');
        self::row('Footer','<textarea class="large-text" name="settings[footer]">'.esc_textarea($s['footer']).'</textarea>');
        self::row('Log retention','<input type="number" min="1" max="3650" name="settings[retention_days]" value="'.absint($s['retention_days']).'"> days');
        self::row('Cache duration','<input type="number" min="1" max="1440" name="settings[cache_minutes]" value="'.absint($s['cache_minutes']).'"> minutes');
        self::row('Delivery controls','<label><input type="checkbox" name="settings[global_pause]" value="1" '.checked($s['global_pause'],true,false).'> Pause all scheduled emails</label><br><label><input type="checkbox" name="settings[delete_on_uninstall]" value="1" '.checked($s['delete_on_uninstall'],true,false).'> Delete plugin data on uninstall</label>');
        echo '</table>'; submit_button('Save settings'); echo '</form>';
        echo '<div class="cws-clr-inline"><form method="post">'; wp_nonce_field('cws_clr_action'); echo '<input type="hidden" name="cws_clr_action" value="test_connection"><input type="hidden" name="return_page" value="custom-link-reports-settings"><label>Optional unsaved key for this test <input type="password" name="api_key" autocomplete="new-password"></label> '; submit_button('Test connection','secondary','submit',false); echo '</form>';
        if(Settings::has_key()){echo '<form method="post" data-cws-confirm="Remove the stored key and stop schedules?">';wp_nonce_field('cws_clr_action');echo '<input type="hidden" name="cws_clr_action" value="remove_key"><input type="hidden" name="return_page" value="custom-link-reports-settings">';submit_button('Remove key','delete','submit',false);echo '</form>';} echo '</div>'; self::footer();
    }

    public static function editor(): void {
        self::header(__('Edit Link Report', 'custom-link-reports')); $id=absint($_GET['id']??0); $post=$id?get_post($id):null; $c=$id?Reports::get($id):Reports::defaults(); $domains=Settings::has_key()?(new Api())->domains():array(); $available=array(); if($c['domain_id'] && Settings::has_key()){$page='';do{$batch=(new Api())->links($c['domain_id'],$page);if(is_wp_error($batch))break;$available=array_merge($available,$batch['links']);$page=$batch['next_page_token'];}while($page!==''&&count($available)<3000);} 
        echo '<form method="post" class="cws-clr-editor">';wp_nonce_field('cws_clr_action');echo '<input type="hidden" name="cws_clr_action" value="save_report"><input type="hidden" name="return_page" value="custom-link-reports-edit"><input type="hidden" name="report_id" value="'.$id.'">';
        echo '<section><h2>Links</h2>';self::row('Report name','<input class="regular-text" required name="report_name" value="'.esc_attr($post?$post->post_title:'').'">');$opts='<option value="">— Select —</option>';if(!is_wp_error($domains))foreach($domains as$d)$opts.='<option value="'.esc_attr($d['id']).'" '.selected($c['domain_id'],$d['id'],false).'>'.esc_html($d['hostname']).'</option>';self::row('Domain','<select name="config[domain_id]" data-cws-domain>'.$opts.'</select><p class="description">Save after changing the domain to load its links.</p>');
        $selected=array_column($c['links'],'id');$saved_links=array_column($c['links'],null,'id');echo '<fieldset><legend class="screen-reader-text">Choose report links</legend><p class="description">The field below only filters the links already loaded from Short.io. It does not perform a new API search. Check each link you want included in this report.</p><div class="cws-clr-link-tools" data-cws-link-tools><label class="screen-reader-text" for="cws-clr-search">Filter loaded links</label><input id="cws-clr-search" type="search" data-cws-link-search placeholder="Filter by link name or URL"><button type="button" class="button" data-cws-link-clear>Clear</button><span class="cws-clr-link-status" data-cws-link-status aria-live="polite"></span></div><div class="cws-clr-links">';foreach($available as$i=>$link){$checked=in_array($link['id'],$selected,true);$saved_link=$saved_links[$link['id']]??array();$label=$link['label'].' — '.$link['short_url'];echo '<label data-cws-link-row><input type="checkbox" name="config[links]['.$i.'][id]" value="'.esc_attr($link['id']).'" '.checked($checked,true,false).'> '.esc_html($label).'<span class="cws-clr-link-edit"> Display label <input name="config[links]['.$i.'][label]" value="'.esc_attr($saved_link['label']??$link['label']).'"> Order <input class="small-text" type="number" min="0" name="config[links]['.$i.'][order]" value="'.absint($saved_link['order']??$i).'"></span><input type="hidden" name="config[links]['.$i.'][domain_id]" value="'.esc_attr($link['domain_id']).'"><input type="hidden" name="config[links]['.$i.'][short_url]" value="'.esc_attr($link['short_url']).'"><input type="hidden" name="config[links]['.$i.'][destination_url]" value="'.esc_attr($link['destination_url']).'"></label>';}if(!$available)echo '<p class="cws-clr-empty">No links are loaded yet. Connect the API key, choose a domain above, and save the report. The links will appear when this page reloads.</p>';echo '</div><p class="description">Selected links reveal optional display-label and order fields. Up to 3,000 links can be loaded.</p></fieldset></section>';
        echo '<section><h2>Content</h2>';self::row('Sections',self::checks('config[sections]',$c['sections'],array('summary'=>'Click summary','daily'=>'Daily clicks','human'=>'Human clicks when available','referrers'=>'Referrers','countries'=>'Countries','browsers'=>'Browsers','os'=>'Operating systems')));self::row('Comparison','<label><input type="checkbox" name="config[compare]" value="1" '.checked($c['compare'],true,false).'> Compare with the immediately preceding equal-length period</label>');self::row('Top results','<select name="config[top_limit]">'.self::number_options(array(5,10,20),$c['top_limit']).'</select>');self::row('Heading override','<input class="regular-text" name="config[heading]" value="'.esc_attr($c['heading']).'">');self::row('Introduction','<textarea class="large-text" name="config[introduction]">'.esc_textarea($c['introduction']).'</textarea>');self::row('Footer override','<textarea class="large-text" name="config[footer]">'.esc_textarea($c['footer']).'</textarea>');self::row('Accent override','<input type="color" name="config[accent]" value="'.esc_attr($c['accent']?:Settings::get()['accent']).'">');self::row('Logo override','<input type="number" min="0" name="config[logo_id]" value="'.absint($c['logo_id']).'"><p class="description">Optional Media Library attachment ID; leave 0 for the global logo.</p>');echo '</section>';
        echo '<section><h2>Email</h2>';self::row('Recipients','<textarea class="large-text" name="config[recipients]">'.esc_textarea(implode(', ',$c['recipients'])).'</textarea><p class="description">Regular recipients. Each address receives its own message.</p>');self::row('BCC recipients','<textarea class="large-text" name="config[bcc_recipients]">'.esc_textarea(implode(', ',$c['bcc_recipients'])).'</textarea><p class="description">Optional private copies. Separate addresses with commas or new lines. These addresses are never shown to regular recipients or to one another.</p>');self::row('Subject','<input class="large-text" name="config[subject]" value="'.esc_attr($c['subject']).'"><p class="description">Tokens: {report_name}, {date_start}, {date_end}, {site_name}</p>');echo '</section>';
        echo '<section><h2>Schedule</h2>';self::row('Status','<label><input type="checkbox" name="config[schedule_enabled]" value="1" '.checked($c['schedule_enabled'],true,false).'> Enable weekly schedule</label> <label><input type="checkbox" name="config[paused]" value="1" '.checked($c['paused'],true,false).'> Paused</label>');self::row('Weekday','<select name="config[weekday]">'.self::weekday_options($c['weekday']).'</select>');self::row('Local time','<input type="time" name="config[time]" value="'.esc_attr($c['time']).'">');self::row('Timezone','<select name="config[timezone]">'.wp_timezone_choice($c['timezone'],get_user_locale()).'</select>');self::row('Reporting window','<select name="config[window]"><option value="previous_7" '.selected($c['window'],'previous_7',false).'>Previous seven complete days</option><option value="previous_week" '.selected($c['window'],'previous_week',false).'>Previous Monday–Sunday</option></select><p class="description">WP-Cron runs after the selected time when the site receives traffic.</p>');$next=Date_Windows::next_occurrence($c);[$ps,$pe]=Date_Windows::period($c['window'],(new \DateTimeImmutable('@'.$next))->setTimezone(new \DateTimeZone($c['timezone'])));echo '<p><strong>Next example:</strong> '.esc_html(wp_date('M, D j, Y g:i a T',$next,new \DateTimeZone($c['timezone']))).' reporting '.esc_html(Renderer::format_date($ps,$c['timezone']).' – '.Renderer::format_date($pe,$c['timezone'])).'.</p></section>';submit_button($id?'Save report':'Create report');echo '</form>';
        if($id){[$start,$end]=Date_Windows::period('previous_7',new \DateTimeImmutable('now',new \DateTimeZone($c['timezone'])));echo '<section><h2>Preview and delivery</h2><p>Period: '.esc_html(Renderer::format_date($start,$c['timezone']).' – '.Renderer::format_date($end,$c['timezone']).' · '.$c['timezone']).'</p><div class="cws-clr-inline">';self::preview_form($id,$start,$end);self::send_form($id,false,$start,$end);self::send_form($id,true,$start,$end);echo '</div><p class="description">Preview is live data and expires after 15 minutes. “Accepted” means wp_mail() accepted the message for processing, not confirmed inbox delivery.</p>';$preview=get_transient('cws_clr_preview_'.get_current_user_id().'_'.$id);if(is_array($preview)&&!empty($preview['html']))echo '<div class="cws-clr-preview"><h3>Live preview</h3>'.$preview['html'].'</div>';echo '</section>';}
        self::footer();
    }

    public static function activity(): void { self::header(__('Link Report Activity', 'custom-link-reports')); self::runs_table(Runs::recent(100)); self::footer(); }
    private static function runs_table(array $runs): void { echo '<table class="widefat striped"><thead><tr><th>Run</th><th>Report</th><th>Period</th><th>Status</th><th>Attempt</th><th>Created (UTC)</th><th>Error</th></tr></thead><tbody>';foreach($runs as$r){$created=strtotime($r->created_at.' UTC');echo '<tr><td>'.absint($r->id).'</td><td>'.esc_html(Reports::title((int)$r->report_id)?:('#'.$r->report_id)).'</td><td>'.esc_html(Renderer::format_date($r->period_start,$r->timezone).' – '.Renderer::format_date($r->period_end,$r->timezone).' '.$r->timezone).'</td><td>'.esc_html($r->status).'</td><td>'.absint($r->attempt).'</td><td>'.esc_html($created?wp_date('M, D j, Y g:i a T',$created,new \DateTimeZone('UTC')):$r->created_at).'</td><td>'.esc_html($r->error).'</td></tr>';}if(!$runs)echo '<tr><td colspan="7">No activity yet.</td></tr>';echo '</tbody></table>'; }
    private static function row(string $label,string $control):void{echo '<div class="cws-clr-field"><div class="cws-clr-label">'.esc_html($label).'</div><div>'.$control.'</div></div>';}
    private static function checks(string $name,array $selected,array $choices):string{$html='';foreach($choices as$k=>$v)$html.='<label><input type="checkbox" name="'.esc_attr($name).'[]" value="'.esc_attr($k).'" '.checked(in_array($k,$selected,true),true,false).'> '.esc_html($v).'</label><br>';return$html;}
    private static function number_options(array $items,int $selected):string{$out='';foreach($items as$i)$out.='<option value="'.$i.'" '.selected($selected,$i,false).'>'.$i.'</option>';return$out;}
    private static function weekday_options(int $selected):string{$out='';foreach(array(1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday')as$i=>$d)$out.='<option value="'.$i.'" '.selected($selected,$i,false).'>'.$d.'</option>';return$out;}
    private static function small_form(string $action,int $id,string $label,string $class=''):void{echo '<form method="post" class="cws-clr-small" '.($action==='delete_report'?'data-cws-confirm="Delete this report and cancel its jobs?"':'').'>';wp_nonce_field('cws_clr_action');echo '<input type="hidden" name="cws_clr_action" value="'.esc_attr($action).'"><input type="hidden" name="report_id" value="'.$id.'"><button class="button-link '.esc_attr($class).'">'.esc_html($label).'</button></form>';}
    private static function send_form(int$id,bool$test,string$start,string$end):void{echo '<form method="post">';wp_nonce_field('cws_clr_action');echo '<input type="hidden" name="cws_clr_action" value="'.($test?'test_report':'send_report').'"><input type="hidden" name="report_id" value="'.$id.'"><input type="hidden" name="date_start" value="'.esc_attr($start).'"><input type="hidden" name="date_end" value="'.esc_attr($end).'">';if($test)echo '<label>Test address <input type="email" required name="test_recipient"></label> ';submit_button($test?'Send Test':'Send Now',$test?'secondary':'primary','submit',false);echo '</form>';}
    private static function preview_form(int$id,string$start,string$end):void{echo '<form method="post">';wp_nonce_field('cws_clr_action');echo '<input type="hidden" name="cws_clr_action" value="preview_report"><input type="hidden" name="report_id" value="'.$id.'"><input type="hidden" name="date_start" value="'.esc_attr($start).'"><input type="hidden" name="date_end" value="'.esc_attr($end).'"><label><input type="checkbox" name="refresh" value="1"> Bypass cache</label> ';submit_button('Preview','secondary','submit',false);echo '</form>';}
    private static function load_links(string$domain){$all=[];$token='';$api=new Api();do{$batch=$api->links($domain,$token);if(is_wp_error($batch))return$batch;$all=array_merge($all,$batch['links']);$token=$batch['next_page_token'];}while($token!==''&&count($all)<3000);return$all;}
    private static function daily_chart(array$data):void{$max=max(array_merge(array(1),array_values($data['daily'])));echo '<h3>Daily clicks chart</h3><div class="cws-clr-chart" role="img" aria-label="Daily clicks bar chart">';foreach($data['daily']as$date=>$value)echo '<div><span style="height:'.esc_attr((string)max(2,round(($value/$max)*120))).'px" title="'.esc_attr(Renderer::format_date($date,$data['timezone']).': '.$value.' clicks').'"></span><small>'.esc_html(Renderer::format_date($date,$data['timezone'],'M j')).'</small></div>';echo '</div><table class="widefat striped"><thead><tr><th>Date</th><th>Clicks</th></tr></thead><tbody>';foreach($data['daily']as$date=>$value)echo '<tr><td>'.esc_html(Renderer::format_date($date,$data['timezone'])).'</td><td>'.absint($value).'</td></tr>';echo '</tbody></table>';}
}

