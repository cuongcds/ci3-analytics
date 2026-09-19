<?php

namespace Open\Analytics\Support;

/**
 * Best-effort device/browser classification from a User-Agent string.
 * Deliberately coarse (desktop/mobile/tablet, five browser buckets) —
 * this is for dashboard breakdowns, not fingerprinting or feature detection.
 */
class UserAgent
{
    /** @return array{0: string, 1: string} [device, browser] */
    public static function parse(?string $userAgent): array
    {
        $userAgent = (string) $userAgent;

        if (preg_match('/Mobi|Android|iPhone/i', $userAgent)) {
            $device = 'mobile';
        } elseif (preg_match('/iPad|Tablet/i', $userAgent)) {
            $device = 'tablet';
        } else {
            $device = 'desktop';
        }

        $browsers = ['Edg' => 'Edge', 'OPR' => 'Opera', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'];
        $browser = 'other';
        foreach ($browsers as $needle => $name) {
            if (strpos($userAgent, $needle) !== false) {
                $browser = $name;
                break;
            }
        }

        return [$device, $browser];
    }
}
