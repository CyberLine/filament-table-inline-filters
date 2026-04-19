@php
    use Cyberline\FilamentTableInlineFilters\Support\IconResolver;
    use Filament\Support\Enums\IconSize;
    use Filament\Tables\View\TablesIconAlias;

    $size = config('filament-table-inline-filters.icons.size');
    if (! $size instanceof IconSize) {
        $size = IconSize::Small;
    }

    $chipColor = config('filament-table-inline-filters.chip.color', 'primary');
    $equalsTpl = (string) config('filament-table-inline-filters.chip.equals_format', ':label: :value');
    $notEqualsTpl = (string) config('filament-table-inline-filters.chip.not_equals_format', ':label: ! :value');

    $nativeRemovable = collect($nativeIndicators)->contains(fn (\Filament\Tables\Filters\Indicator $indicator): bool => $indicator->isRemovable());
    $inlineRemovable = $inlineFilters !== [];
    $showRemoveAll = $nativeRemovable || $inlineRemovable;
@endphp

<div class="fi-ta-filter-indicators">
    <div>
        <span class="fi-ta-filter-indicators-label">
            {{ __('filament-tables::table.filters.indicator') }}
        </span>

        <div class="fi-ta-filter-indicators-badges-ctn">
            @foreach ($nativeIndicators as $indicator)
                @php
                    $indicatorColor = $indicator->getColor();
                @endphp

                <x-filament::badge :color="$indicatorColor">
                    {{ $indicator->getLabel() }}

                    @if ($indicator->isRemovable())
                        @php
                            $indicatorRemoveLivewireClickHandler = $indicator->getRemoveLivewireClickHandler();
                        @endphp

                        <x-slot
                            name="deleteButton"
                            :label="__('filament-tables::table.filters.actions.remove.label')"
                            :wire:click="$indicatorRemoveLivewireClickHandler"
                            wire:loading.attr="disabled"
                            wire:target="removeTableFilter"
                        ></x-slot>
                    @endif
                </x-filament::badge>
            @endforeach

            @foreach ($inlineFilters as $index => $filter)
                @php
                    $operator = $filter['operator'] ?? '=';
                    $lbl = (string) ($filter['label'] ?? $filter['column'] ?? '');
                    $val = (string) ($filter['value'] ?? '');
                    $tpl = $operator === '!=' ? $notEqualsTpl : $equalsTpl;
                    $text = str_replace([':label', ':value'], [$lbl, $val], $tpl);
                    $icon = $operator === '!=' ? IconResolver::defaultMinus() : IconResolver::defaultPlus();
                    $chipLabel = IconResolver::chipLabelHtml($icon, $text, $size);
                @endphp

                <x-filament::badge :color="$chipColor">
                    {!! $chipLabel !!}

                    <x-slot
                        name="deleteButton"
                        :label="__('filament-table-inline-filters::inline-filters.remove')"
                        wire:click="removeInlineFilter({{ $index }})"
                        wire:loading.attr="disabled"
                        wire:target="removeInlineFilter"
                    ></x-slot>
                </x-filament::badge>
            @endforeach
        </div>
    </div>

    @if ($showRemoveAll)
        <button
            type="button"
            x-tooltip="{
                content: @js(__('filament-tables::table.filters.actions.remove_all.tooltip')),
                theme: $store.theme,
            }"
            wire:click="removeTableFiltersWithInline"
            wire:loading.attr="disabled"
            wire:target="removeTableFiltersWithInline,removeTableFilters,removeTableFilter,removeInlineFilter"
            class="fi-icon-btn fi-size-sm"
        >
            {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::XMark, alias: TablesIconAlias::FILTERS_REMOVE_ALL_BUTTON, size: \Filament\Support\Enums\IconSize::Small) }}
        </button>
    @endif
</div>
