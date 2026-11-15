<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Mailer {
    public static function send(string $recipient, string $subject, string $html): bool {
        if (!is_email($recipient) || preg_match('/[\r\n]/', $recipient)) return false;
        return (bool) wp_mail($recipient, $subject, $html, array('Content-Type: text/html; charset=UTF-8'));
    }
}

