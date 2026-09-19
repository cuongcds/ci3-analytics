<?php

namespace Open\Analytics\Support;

class Config
{
    /**
     * Recursively merges $overrides onto $defaults for associative (keyed)
     * sub-arrays only — a list value (e.g. date_range.presets) in
     * $overrides fully replaces the default list rather than being merged
     * index-by-index, which is what array_replace_recursive() would do
     * (and would silently corrupt a shorter override list).
     */
    public static function merge(array $defaults, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key]) && self::isAssoc($value)) {
                $defaults[$key] = self::merge($defaults[$key], $value);
            } else {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    /** PHP 7.2-compatible array_is_list() polyfill, negated. */
    private static function isAssoc(array $array): bool
    {
        if ($array === []) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
