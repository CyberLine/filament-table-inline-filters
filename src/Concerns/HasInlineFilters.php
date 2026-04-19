<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters\Concerns;

use Livewire\Attributes\Url;

trait HasInlineFilters
{
    #[Url(as: 'ilf', history: true)]
    public array $inlineFilters = [];

    public function updatedInlineFilters(): void
    {
        $this->refreshDatasetAfterInlineFiltersChanged();
    }

    public function addInlineFilter(string $column, string $operator, mixed $value, ?string $label = null): void
    {
        if (! $this->isValidInlineColumn($column) || ! in_array($operator, ['=', '!='], true)) {
            return;
        }

        $this->inlineFilters = array_values(array_filter(
            $this->inlineFilters,
            function (array $f) use ($column, $value): bool {
                if (($f['column'] ?? '') !== $column) {
                    return true;
                }

                return ($f['value'] ?? null) != $value;
            },
        ));

        $this->inlineFilters[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'label' => $label,
        ];

        $this->refreshDatasetAfterInlineFiltersChanged();
    }

    public function removeInlineFilter(int $index): void
    {
        if (! isset($this->inlineFilters[$index])) {
            return;
        }

        unset($this->inlineFilters[$index]);
        $this->inlineFilters = array_values($this->inlineFilters);

        $this->refreshDatasetAfterInlineFiltersChanged();
    }

    public function clearInlineFilters(): void
    {
        $this->inlineFilters = [];
        $this->refreshDatasetAfterInlineFiltersChanged();
    }

    public function removeTableFiltersWithInline(): void
    {
        $this->inlineFilters = [];
        $this->flushCachedTableRecords();
        $this->removeTableFilters();
    }

    protected function refreshDatasetAfterInlineFiltersChanged(): void
    {
        $this->flushCachedTableRecords();
        $this->resetPage();
    }

    private function isValidInlineColumn(string $column): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_.]+$/', $column);
    }
}
