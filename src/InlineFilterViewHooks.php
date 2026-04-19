<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters;

use Cyberline\FilamentTableInlineFilters\Concerns\HasInlineFilters;
use Filament\Tables\Contracts\HasTable;
use Livewire\Livewire;

final class InlineFilterViewHooks
{
    public static function renderMergedFilterIndicators(array $data): string
    {
        $livewire = Livewire::current();

        if (! $livewire instanceof HasTable || ! self::usesInlineFilters($livewire)) {
            return '';
        }

        $inline = $livewire->inlineFilters ?? [];
        if ($inline === []) {
            return '';
        }

        $native = $data['filterIndicators'] ?? [];

        return view('filament-table-inline-filters::merged-filter-indicators', [
            'nativeIndicators' => $native,
            'inlineFilters' => $inline,
        ])->render();
    }

    public static function renderInlineOnlyToolbarStrip(): string
    {
        $livewire = Livewire::current();

        if (! $livewire instanceof HasTable || ! self::usesInlineFilters($livewire)) {
            return '';
        }

        $inline = $livewire->inlineFilters ?? [];
        if ($inline === []) {
            return '';
        }

        $native = $livewire->getTable()->getFilterIndicators();
        if ($native !== []) {
            return '';
        }

        return view('filament-table-inline-filters::merged-filter-indicators', [
            'nativeIndicators' => [],
            'inlineFilters' => $inline,
        ])->render();
    }

    private static function usesInlineFilters(object $livewire): bool
    {
        return in_array(HasInlineFilters::class, class_uses_recursive($livewire), true);
    }
}
