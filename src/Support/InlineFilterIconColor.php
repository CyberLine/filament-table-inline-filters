<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters\Support;

use BackedEnum;

final class InlineFilterIconColor
{
    public const ALLOWED = ['primary', 'success', 'danger', 'warning', 'info', 'gray'];

    public static function normalize(mixed $color, string $fallback): string
    {
        if ($color instanceof BackedEnum) {
            $color = (string) $color->value;
        }

        if (! is_string($color) || $color === '') {
            return self::coerceFallback($fallback);
        }

        $color = strtolower(trim($color));

        return in_array($color, self::ALLOWED, true) ? $color : self::coerceFallback($fallback);
    }

    private static function coerceFallback(string $fallback): string
    {
        $fallback = strtolower(trim($fallback));

        return in_array($fallback, self::ALLOWED, true) ? $fallback : 'gray';
    }
}
