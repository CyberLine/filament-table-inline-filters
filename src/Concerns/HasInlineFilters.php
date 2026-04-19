<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters\Concerns;

use Livewire\Attributes\Url;

trait HasInlineFilters
{
    /**
     * @var list<array{column: string, operator: string, value: mixed, label: ?string}>
     */
    #[Url(as: 'ilf', history: true)]
    public array $inlineFilters = [];

    public function addInlineFilter(string $column, string $operator, mixed $value, ?string $label = null): void
    {
        if (! $this->isValidInlineColumn($column) || ! in_array($operator, ['=', '!='], true)) {
            return;
        }

        foreach ($this->inlineFilters as $existing) {
            if (($existing['column'] ?? '') === $column
                && ($existing['operator'] ?? '') === $operator
                && ($existing['value'] ?? null) == $value) {
                return;
            }
        }

        $this->inlineFilters[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'label' => $label,
        ];

        $this->resetTable();
    }

    public function removeInlineFilter(int $index): void
    {
        if (! isset($this->inlineFilters[$index])) {
            return;
        }

        unset($this->inlineFilters[$index]);
        $this->inlineFilters = array_values($this->inlineFilters);

        $this->resetTable();
    }

    public function clearInlineFilters(): void
    {
        $this->inlineFilters = [];
        $this->resetTable();
    }

    public function removeTableFiltersWithInline(): void
    {
        $this->removeTableFilters();
        $this->inlineFilters = [];
        $this->resetTable();
    }

    private function isValidInlineColumn(string $column): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_.]+$/', $column);
    }
}
