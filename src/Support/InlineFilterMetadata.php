<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters\Support;

use Filament\Tables\Columns\Column;
use WeakMap;

final class InlineFilterMetadata
{
    /** @var WeakMap<Column, array<string, mixed>> */
    private static ?WeakMap $map = null;

    /**
     * @return array<string, mixed>
     */
    public static function get(Column $column): array
    {
        self::boot();

        return self::$map[$column] ?? [];
    }

    /**
     * @param array<string, mixed> $data
     */
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
