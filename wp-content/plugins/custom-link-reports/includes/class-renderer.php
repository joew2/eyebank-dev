<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Renderer {
    public static function aggregate(array $config, string $start, string $end, bool $refresh = false) {
        $api = new Api();
        $links = array(); $total = 0; $prior_total = 0; $human = 0; $human_available = true; $daily = array();
        $previous = $config['compare'] ? Date_Windows::previous($start, $end, new \DateTimeZone($config['timezone'])) : null;
        $breakdowns = array('referrers' => array(), 'countries' => array(), 'browsers' => array(), 'os' => array());
        foreach ($config['links'] as $link) {
            $stats = $api->statistics($link['id'], $start, $end, $config['timezone'], $refresh);
            if (is_wp_error($stats)) return $stats;
            $links[] = array_merge($link, array('statistics' => $stats));
            $total += (int) $stats['total_clicks'];
            if ($previous) {
                $prior = $api->statistics($link['id'], $previous[0], $previous[1], $config['timezone'], $refresh);
                if (is_wp_error($prior)) return $prior;
                $prior_total += (int) $prior['total_clicks'];
            }
            if ($stats['human_clicks'] === null) $human_available = false; else $human += (int) $stats['human_clicks'];
            foreach ($stats['daily'] as $date => $value) $daily[$date] = ($daily[$date] ?? 0) + (int) $value;
            foreach ($breakdowns as $section => &$values) {
                if ($stats['breakdowns'][$section] === null) { $values = null; continue; }
                if ($values === null) continue;
                foreach ($stats['breakdowns'][$section] as $row) $values[$row['label']] = ($values[$row['label']] ?? 0) + (int) $row['value'];
            }
        }
        unset($values); ksort($daily);
        foreach ($breakdowns as $section => $values) {
            if ($values === null) continue;
            arsort($values);
            $breakdowns[$section] = array_slice(array_map(static fn($label, $value) => array('label' => $label, 'value' => $value), array_keys($values), $values), 0, (int) $config['top_limit']);
        }
        return array('period_start' => $start, 'period_end' => $end, 'timezone' => $config['timezone'], 'fetched_at' => time(), 'total_clicks' => $total, 'human_clicks' => $human_available ? $human : null, 'prior_total_clicks' => $previous ? $prior_total : null, 'prior_period' => $previous, 'daily' => $daily, 'breakdowns' => $breakdowns, 'links' => $links);
    }

    public static function html(string $name, array $config, array $data): string {
        $settings = Settings::get();
        $accent = $config['accent'] ?: $settings['accent'];
        $heading = $config['heading'] ?: $settings['heading'];
        $footer = $config['footer'] ?: $settings['footer'];
        $logo_id = $config['logo_id'] ?: (int) $settings['logo_id'];
        $logo = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
        ob_start();
        ?>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:20px;font-family:Arial,sans-serif;color:#17213a"><tr><td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background:#fff;border:1px solid #dfe4ea">
        <tr><td style="padding:24px;border-top:6px solid <?php echo esc_attr($accent); ?>">
        <?php if ($logo) : ?><p style="margin:0 0 16px"><img src="<?php echo esc_url($logo); ?>" alt="" style="max-width:180px;height:auto"></p><?php endif; ?>
        <h1 style="margin:0;color:<?php echo esc_attr($accent); ?>;font-size:26px"><?php echo esc_html($heading); ?></h1>
        <h2 style="margin:8px 0 4px;font-size:20px"><?php echo esc_html($name); ?></h2>
        <p style="margin:0 0 18px;color:#526079"><?php echo esc_html(self::format_date($data['period_start'], $data['timezone']) . ' – ' . self::format_date($data['period_end'], $data['timezone']) . ' · ' . $data['timezone']); ?></p>
        <?php if ($config['introduction']) : ?><div><?php echo wp_kses($config['introduction'], Reports::allowed_html()); ?></div><?php endif; ?>
        <?php if (in_array('summary', $config['sections'], true)) : ?>
        <h3 style="border-bottom:2px solid <?php echo esc_attr($accent); ?>;padding-bottom:6px">Click summary</h3>
        <table width="100%" cellspacing="0" cellpadding="7" style="border-collapse:collapse"><thead><tr><th align="left" style="border-bottom:1px solid #ccd3dc">Link</th><th align="right" style="border-bottom:1px solid #ccd3dc">Clicks</th></tr></thead><tbody>
        <?php foreach ($data['links'] as $link) : ?><tr><td style="border-bottom:1px solid #e6e9ed"><strong><?php echo esc_html($link['label'] ?: $link['short_url']); ?></strong><br><a href="<?php echo esc_url($link['short_url']); ?>"><?php echo esc_html($link['short_url']); ?></a></td><td align="right" style="border-bottom:1px solid #e6e9ed"><?php echo esc_html(number_format_i18n($link['statistics']['total_clicks'])); ?></td></tr><?php endforeach; ?>
        <tr><td><strong>Clicks across selected links</strong></td><td align="right"><strong><?php echo esc_html(number_format_i18n($data['total_clicks'])); ?></strong></td></tr></tbody></table>
        <?php if ($data['prior_total_clicks'] !== null) : ?><p><strong>Previous equal period:</strong> <?php echo esc_html(number_format_i18n($data['prior_total_clicks'])); ?> clicks. <?php echo $data['prior_total_clicks'] === 0 ? esc_html__('No prior clicks.', 'custom-link-reports') : esc_html(number_format_i18n((($data['total_clicks'] - $data['prior_total_clicks']) / $data['prior_total_clicks']) * 100, 1) . '% change'); ?></p><?php endif; ?>
        <?php endif; ?>
        <?php if (in_array('human', $config['sections'], true)) : ?><p><strong>Human clicks:</strong> <?php echo $data['human_clicks'] === null ? esc_html__('Unavailable', 'custom-link-reports') : esc_html(number_format_i18n($data['human_clicks'])); ?></p><?php endif; ?>
        <?php if (in_array('daily', $config['sections'], true)) : self::daily($data['daily'], $accent, $data['timezone']); endif; ?>
        <?php foreach (array('referrers' => 'Top referrers', 'countries' => 'Top countries', 'browsers' => 'Top browsers', 'os' => 'Top operating systems') as $key => $label) { if (in_array($key, $config['sections'], true) && is_array($data['breakdowns'][$key])) self::table($label, $data['breakdowns'][$key], $accent); } ?>
        <?php if ($footer) : ?><div style="margin-top:24px;color:#526079;font-size:13px"><?php echo wp_kses($footer, Reports::allowed_html()); ?></div><?php endif; ?>
        </td></tr></table></td></tr></table>
        <?php
        return (string) ob_get_clean();
    }

    private static function table(string $title, array $rows, string $accent): void {
        echo '<h3 style="border-bottom:2px solid ' . esc_attr($accent) . ';padding-bottom:6px">' . esc_html($title) . '</h3>';
        if (!$rows) { echo '<p>Unavailable or no activity returned.</p>'; return; }
        echo '<table width="100%" cellspacing="0" cellpadding="7" style="border-collapse:collapse"><tbody>';
        foreach ($rows as $row) echo '<tr><td style="border-bottom:1px solid #e6e9ed">' . esc_html((string) $row['label']) . '</td><td align="right" style="border-bottom:1px solid #e6e9ed">' . esc_html(number_format_i18n((int) $row['value'])) . '</td></tr>';
        echo '</tbody></table>';
    }

    /** Render a chart using only inline HTML styles so it survives common email clients. */
    private static function daily(array $daily, string $accent, string $timezone): void {
        echo '<h3 style="border-bottom:2px solid ' . esc_attr($accent) . ';padding-bottom:6px">Daily clicks</h3>';
        if (!$daily) { echo '<p>Unavailable or no activity returned.</p>'; return; }
        $max = max(array_merge(array(1), array_map('intval', array_values($daily))));
        $count = max(1, count($daily));
        $width = max(8, (int) floor(100 / $count));
        echo '<p style="margin:14px 0 8px;font-weight:bold">Daily clicks chart</p>';
        echo '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border-bottom:1px solid #ccd3dc"><tr valign="bottom">';
        foreach ($daily as $date => $value) {
            $height = max(3, (int) round(((int) $value / $max) * 110));
            echo '<td width="' . esc_attr((string) $width) . '%" align="center" valign="bottom" style="padding:0 2px 5px;color:#526079;font-size:11px">';
            echo '<span style="display:block;margin:0 auto 4px;width:22px;max-width:100%;height:' . esc_attr((string) $height) . 'px;background:' . esc_attr($accent) . '">&nbsp;</span>';
            echo '<strong style="display:block;color:#17213a">' . esc_html(number_format_i18n((int) $value)) . '</strong>';
            echo '<span style="display:block;white-space:nowrap">' . esc_html(self::format_date($date, $timezone, 'M j')) . '</span></td>';
        }
        echo '</tr></table>';
        $rows = array_map(static fn($date, $value) => array('label' => self::format_date((string) $date, $timezone), 'value' => $value), array_keys($daily), $daily);
        echo '<table width="100%" cellspacing="0" cellpadding="7" style="border-collapse:collapse;margin-top:10px"><thead><tr><th align="left" style="border-bottom:1px solid #ccd3dc">Date</th><th align="right" style="border-bottom:1px solid #ccd3dc">Clicks</th></tr></thead><tbody>';
        foreach ($rows as $row) echo '<tr><td style="border-bottom:1px solid #e6e9ed">' . esc_html((string) $row['label']) . '</td><td align="right" style="border-bottom:1px solid #e6e9ed">' . esc_html(number_format_i18n((int) $row['value'])) . '</td></tr>';
        echo '</tbody></table>';
    }

    public static function format_date(string $date, string $timezone = 'UTC', string $format = 'M, D j, Y'): string {
        $tz = new \DateTimeZone($timezone ?: 'UTC');
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, $tz);
        return $parsed ? wp_date($format, $parsed->getTimestamp(), $tz) : $date;
    }

    public static function subject(string $template, string $name, string $start, string $end): string {
        $allowed = array('{report_name}' => $name, '{date_start}' => self::format_date($start), '{date_end}' => self::format_date($end), '{site_name}' => get_bloginfo('name'));
        return sanitize_text_field(strtr($template, $allowed));
    }

    public static function unknown_tokens(string $template): array {
        preg_match_all('/\{[^}]+\}/', $template, $matches);
        return array_values(array_diff(array_unique($matches[0] ?? array()), array('{report_name}', '{date_start}', '{date_end}', '{site_name}')));
    }
}

