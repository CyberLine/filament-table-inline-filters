<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters\Support;

use Closure;
use Cyberline\FilamentTableInlineFilters\Concerns\HasInlineFilters;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

final class InlineFilterQueryApplier
{
    /**
     * @return Closure(Builder, bool, HasTable): void
     */
    public static function tableQueryScope(): Closure
    {
        return function (Builder $query, bool $isResolvingRecord, HasTable $livewire): void {
            if ($isResolvingRecord) {
                return;
            }

            if (! in_array(HasInlineFilters::class, class_uses_recursive($livewire), true)) {
                return;
            }

            $filters = $livewire->inlineFilters ?? [];
            if (! is_array($filters) || $filters === []) {
                return;
            }

            foreach ($filters as $filter) {
                $column = $filter['column'] ?? null;
                $operator = $filter['operator'] ?? null;
                $value = $filter['value'] ?? null;

                if (! is_string($column) || ! is_string($operator)) {
                    continue;
                }

                if (! self::isSafeColumnName($column)) {
                    continue;
                }

                if (! in_array($operator, ['=', '!='], true)) {
                    continue;
                }

                self::applyOne($query, $column, $operator, $value);
            }
        };
    }

    private static function isSafeColumnName(string $column): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_.]+$/', $column);
    }

    private static function applyOne(Builder $query, string $column, string $operator, mixed $value): void
    {
        if (! str_contains($column, '.')) {
            if ($operator === '=') {
                $query->where($column, '=', $value);

                return;
            }

            $query->where(function (Builder $q) use ($column, $value): void {
                $q->where($column, '!=', $value)
                    ->orWhereNull($column);
            });

            return;
        }

        $parts = explode('.', $column);
        $attribute = array_pop($parts);
        $relationPath = implode('.', $parts);

        if ($operator === '=') {
            $query->whereHas($relationPath, function (Builder $relationQuery) use ($attribute, $value): void {
                if ($value === null) {
                    $relationQuery->whereNull($attribute);
                } else {
                    $relationQuery->where($attribute, '=', $value);
                }
            });

            return;
        }

        $query->where(function (Builder $outer) use ($relationPath, $attribute, $value): void {
            $outer->whereDoesntHave($relationPath)
                ->orWhereHas($relationPath, function (Builder $relationQuery) use ($attribute, $value): void {
                    $relationQuery->where(function (Builder $q) use ($attribute, $value): void {
                        $q->where($attribute, '!=', $value)
                            ->orWhereNull($attribute);
                    });
                });
        });
    }
}
