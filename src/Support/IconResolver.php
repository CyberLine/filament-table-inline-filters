<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters\Support;

use BackedEnum;
use Closure;
use Filament\Support\Enums\IconSize;

use function Filament\Support\generate_icon_html;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

final class IconResolver
{
    public static function svgPairForColumn(Column $column): array
    {
        $meta = InlineFilterMetadata::get($column);
        $plusIcon = self::normalizeIcon($column, $meta['iconPlus'] ?? null, Heroicon::OutlinedPlusCircle);
        $minusIcon = self::normalizeIcon($column, $meta['iconMinus'] ?? null, Heroicon::OutlinedMinusCircle);

        $size = config('filament-table-inline-filters.icons.size', IconSize::Small);
        if (! $size instanceof IconSize) {
            $size = IconSize::Small;
        }

        return [
            self::toSvgHtml($plusIcon, $size),
            self::toSvgHtml($minusIcon, $size),
        ];
    }

    public static function defaultPlus(): string|BackedEnum
    {
        $v = config('filament-table-inline-filters.icons.plus', Heroicon::OutlinedPlusCircle);

        return $v instanceof BackedEnum || is_string($v) ? $v : Heroicon::OutlinedPlusCircle;
    }

    public static function defaultMinus(): string|BackedEnum
    {
        $v = config('filament-table-inline-filters.icons.minus', Heroicon::OutlinedMinusCircle);

        return $v instanceof BackedEnum || is_string($v) ? $v : Heroicon::OutlinedMinusCircle;
    }

    public static function toSvgHtml(string|BackedEnum $icon, IconSize $size): string
    {
        $html = generate_icon_html($icon, size: $size);

        return $html instanceof Htmlable ? $html->toHtml() : (string) $html;
    }

    public static function chipLabelHtml(string|BackedEnum $icon, string $text, IconSize $size): HtmlString
    {
        $svg = self::toSvgHtml($icon, $size);

        return new HtmlString(
            '<span class="fi-ilf-chip-label">'
            . $svg
            . '<span class="fi-ilf-chip-text">' . e($text) . '</span>'
            . '</span>'
        );
    }

    private static function normalizeIcon(Column $column, mixed $icon, string|BackedEnum $fallback): string|BackedEnum
    {
        if ($icon instanceof Closure) {
            $icon = $column->evaluate($icon);
        }

        if ($icon instanceof BackedEnum || is_string($icon)) {
            return $icon;
        }

        return $fallback;
    }
}
