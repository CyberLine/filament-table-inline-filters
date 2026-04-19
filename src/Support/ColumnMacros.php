<?php

declare(strict_types=1);

namespace Cyberline\FilamentTableInlineFilters\Support;

use BackedEnum;
use Closure;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use LogicException;

final class ColumnMacros
{
    public static function boot(): void
    {
        TextColumn::macro('inlineFilter', function (?Closure $visibility = null) {
            ColumnMacros::assertPlainTextColumn($this);

            InlineFilterMetadata::merge($this, [
                'enabled' => true,
                'visibility' => $visibility,
            ]);

            return $this->extraCellAttributes(function (?Model $record = null) {
                $meta = InlineFilterMetadata::get($this);

                if (! ($meta['enabled'] ?? false) || ! $record instanceof Model) {
                    return [];
                }

                if (($meta['visibility'] ?? null) instanceof Closure) {
                    $visible = $this->evaluate($meta['visibility'], ['record' => $record]);
                    if (! $visible) {
                        return [];
                    }
                }

                $state = $this->record($record)->getState();
                $pair = IconResolver::svgPairForColumn($this);

                $label = (string) ($this->getLabel() ?? $this->getName());

                try {
                    $json = json_encode($state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                } catch (JsonException) {
                    $json = json_encode((string) $state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                }

                return [
                    'data-ilf-col' => $this->getName(),
                    'data-ilf-value' => htmlspecialchars($json, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'data-ilf-align' => ($meta['align'] ?? 'left'),
                    'data-ilf-label' => htmlspecialchars($label, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'data-ilf-plus-svg' => base64_encode($pair[0]),
                    'data-ilf-minus-svg' => base64_encode($pair[1]),
                ];
            }, merge: true);
        });

        TextColumn::macro('inlineFilterAlignRight', function () {
            ColumnMacros::assertPlainTextColumn($this);

            InlineFilterMetadata::merge($this, ['align' => 'right']);

            return $this;
        });

        TextColumn::macro('inlineFilterIconPlus', function (string|BackedEnum|Closure $icon) {
            ColumnMacros::assertPlainTextColumn($this);

            InlineFilterMetadata::merge($this, ['iconPlus' => $icon]);

            return $this;
        });

        TextColumn::macro('inlineFilterIconMinus', function (string|BackedEnum|Closure $icon) {
            ColumnMacros::assertPlainTextColumn($this);

            InlineFilterMetadata::merge($this, ['iconMinus' => $icon]);

            return $this;
        });
    }

    public static function assertPlainTextColumn(object $column): void
    {
        if (! ($column instanceof TextColumn) || $column::class !== TextColumn::class) {
            throw new LogicException(sprintf(
                'Inline filters are only supported on %s (got %s).',
                TextColumn::class,
                $column::class,
            ));
        }
    }
}
