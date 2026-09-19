<?php

namespace Open\Analytics\Support;

/**
 * Anonymous, cookie-backed visitor identifier. Not tied to any auth
 * system — a fresh browser gets a fresh id, kept only for as long as the
 * cookie survives (see the CI3 adapter for the actual cookie read/write,
 * which is a framework concern this class stays out of).
 */
class VisitorId
{
    private const PATTERN = '/^[a-f0-9-]{36}$/';

    public static function isValid(?string $value): bool
    {
        return !empty($value) && (bool) preg_match(self::PATTERN, $value);
    }

    public static function generate(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
