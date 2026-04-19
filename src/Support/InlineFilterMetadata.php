<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters\Support;

use Filament\Tables\Columns\Column;
use WeakMap;

final class InlineFilterMetadata
{
    private static ?WeakMap $map = null;

    public static function get(Column $column): array
    {
        self::boot();

        return self::$map[$column] ?? [];
    }

    public static function merge(Column $column, array $data): void
    {
        self::boot();
        $existing = self::$map[$column] ?? [];
        self::$map[$column] = array_merge($existing, $data);
    }

    private static function boot(): void
    {
        self::$map ??= new WeakMap;
    }
}
