<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters;

use Cyberline\FilamentTableInlineFilters\Support\ColumnMacros;
use Cyberline\FilamentTableInlineFilters\Support\InlineFilterQueryApplier;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\ServiceProvider;

class FilamentTableInlineFiltersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-table-inline-filters.php',
            'filament-table-inline-filters',
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-table-inline-filters');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-table-inline-filters');

        ColumnMacros::boot();

        Table::configureUsing(function (Table $table): void {
            $table->modifyQueryUsing(InlineFilterQueryApplier::tableQueryScope());
        });

        FilamentView::registerRenderHook(
            TablesRenderHook::FILTER_INDICATORS,
            fn (array $data, array $scopes): string => InlineFilterViewHooks::renderMergedFilterIndicators($data),
        );

        FilamentView::registerRenderHook(
            TablesRenderHook::TOOLBAR_AFTER,
            fn (array $data, array $scopes): string => InlineFilterViewHooks::renderInlineOnlyToolbarStrip(),
        );

        FilamentAsset::register(
            [
                Js::make('cyberline-inline-filters', __DIR__ . '/../resources/js/inline-filters.js'),
                Css::make('cyberline-inline-filters', __DIR__ . '/../resources/css/inline-filters.css'),
            ],
            'cyberline/filament-table-inline-filters',
        );

        $this->publishes([
            __DIR__ . '/../config/filament-table-inline-filters.php' => config_path('filament-table-inline-filters.php'),
        ], 'filament-table-inline-filters-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-table-inline-filters'),
        ], 'filament-table-inline-filters-views');

        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/filament-table-inline-filters'),
        ], 'filament-table-inline-filters-lang');
    }
}
