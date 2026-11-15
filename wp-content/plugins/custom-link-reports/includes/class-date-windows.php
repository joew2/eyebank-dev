<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Date_Windows {
    public static function period(string $choice, \DateTimeImmutable $reference): array {
        $today = $reference->setTime(0, 0);
        if ($choice === 'previous_week') {
            $end = $today->modify('monday this week')->modify('-1 day');
            $start = $end->modify('-6 days');
        } else {
            $end = $today->modify('-1 day');
            $start = $end->modify('-6 days');
        }
        return array($start->format('Y-m-d'), $end->format('Y-m-d'));
    }

    public static function previous(string $start, string $end, \DateTimeZone $tz): array {
        $a = new \DateTimeImmutable($start, $tz);
        $b = new \DateTimeImmutable($end, $tz);
        $days = (int) $a->diff($b)->format('%a') + 1;
        $prev_end = $a->modify('-1 day');
        return array($prev_end->modify('-' . ($days - 1) . ' days')->format('Y-m-d'), $prev_end->format('Y-m-d'));
    }

    public static function next_occurrence(array $config, ?\DateTimeImmutable $now = null): int {
        $tz = new \DateTimeZone((string) $config['timezone']);
        $now = ($now ?: new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->setTimezone($tz);
        [$hour, $minute] = array_map('intval', explode(':', (string) $config['time']));
        $days = ((int) $config['weekday'] - (int) $now->format('N') + 7) % 7;
        $candidate = $now->modify('+' . $days . ' days')->setTime($hour, $minute);
        if ($candidate <= $now) $candidate = $candidate->modify('+7 days');
        // PHP advances nonexistent DST wall times to the next valid instant and selects
        // the first occurrence for ambiguous times; this is our deterministic policy.
        return $candidate->setTimezone(new \DateTimeZone('UTC'))->getTimestamp();
    }

    public static function most_recent_occurrence(array $config, ?\DateTimeImmutable $now = null): int {
        $tz = new \DateTimeZone((string) $config['timezone']);
        $now = ($now ?: new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->setTimezone($tz);
        [$hour, $minute] = array_map('intval', explode(':', (string) $config['time']));
        $days = ((int) $now->format('N') - (int) $config['weekday'] + 7) % 7;
        $candidate = $now->modify('-' . $days . ' days')->setTime($hour, $minute);
        if ($candidate > $now) $candidate = $candidate->modify('-7 days');
        return $candidate->setTimezone(new \DateTimeZone('UTC'))->getTimestamp();
    }
}

