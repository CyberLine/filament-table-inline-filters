# Filament Table Inline Filters

Add per-cell **include** (`=`) and **exclude** (`!=`) filters to Filament tables, similar to Grafana-style column filters. Active filters appear as **badges** next to native Filament filter indicators, support **URL persistence** (`?ilf=…`), and ship with **Filament assets** (JS/CSS) registered automatically.

## Requirements

- PHP **8.2+**
- **Filament** v4 or v5 (`filament/filament`)
- **Laravel** 11 / 12 / 13 (as supported by your Filament version)
- **Livewire** v3 or v4

## Installation

Install via Composer (after the package is published on Packagist, or via a VCS/repository entry in your `composer.json`):

```bash
composer require cyberline/filament-table-inline-filters
```

Laravel **auto-discovers** `FilamentTableInlineFiltersServiceProvider`. No manual registration is required unless you disabled package discovery.

Filament **loads plugin assets** when your panel uses the default Filament asset pipeline. If you customize asset loading, ensure Filament’s asset registration for third-party packages is unchanged.

### Optional publishes

```bash
php artisan vendor:publish --tag=filament-table-inline-filters-config
php artisan vendor:publish --tag=filament-table-inline-filters-views
php artisan vendor:publish --tag=filament-table-inline-filters-lang
```

## Quick start

### 1. Use the trait on your table Livewire page

Your **ListRecords** (or any page that uses `HasTable`) must use `HasInlineFilters`:

```php
<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Cyberline\FilamentTableInlineFilters\Concerns\HasInlineFilters;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    use HasInlineFilters;

    protected static string $resource = UserResource::class;
}
```

### 2. Enable inline filters on columns

In your resource’s `table()` definition, chain `->inlineFilter()` on any column that should show **+ / −** actions:

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('firstname')
                ->label('First name')
                ->inlineFilter(),

            TextColumn::make('lastname')
                ->label('Last name')
                ->inlineFilter(),
        ]);
}
```

The **column name** passed to the query is Filament’s column **name** (e.g. `firstname`), which maps to your model attribute or accessor.

Inline filters are implemented only for **`TextColumn`** (not `BadgeColumn`, `TagsColumn`, or other column types). Subclasses of `TextColumn` throw a clear `LogicException` at runtime.

### 3. Clear all filters (native + inline)

The package registers a `removeTableFiltersWithInline()` method on components that use `HasInlineFilters`. The default chip UI calls it for **“remove all filters”** so inline filters are cleared together with table filters.

If you add a custom action, you can call:

```php
$this->removeTableFiltersWithInline();
```

## Column API

### Basic

```php
TextColumn::make('email')->inlineFilter();
```

### Optional visibility (per row)

```php
TextColumn::make('role')
    ->inlineFilter(fn () => auth()->user()?->can('filterByRole'));
```

Or use a closure with the **record**:

```php
TextColumn::make('status')
    ->inlineFilter(fn ($record) => $record->status !== 'archived');
```

### Align actions to the right

```php
TextColumn::make('amount')
    ->inlineFilter()
    ->inlineFilterAlignRight();
```

### Custom icons (plus / minus)

Uses Filament icon names or `BackedEnum` icon enums:

```php
use Filament\Support\Icons\Heroicon;

TextColumn::make('city')
    ->inlineFilter()
    ->inlineFilterIconPlus(Heroicon::OutlinedPlusCircle)
    ->inlineFilterIconMinus(Heroicon::OutlinedMinusCircle);
```

Icons can also be **closures** evaluated in column context (same as Filament column `evaluate()`).

### Icon button colors (plus / minus)

Defaults come from `config('filament-table-inline-filters.icons.plus_color')` and `minus_color` (`success` and `danger`). Allowed values: `primary`, `success`, `danger`, `warning`, `info`, `gray`.

```php
TextColumn::make('name')
    ->inlineFilter()
    ->inlineFilterPlusColor('primary')
    ->inlineFilterMinusColor('gray');
```

Passing no argument (or `null`) uses the config default for that side. `BackedEnum` values are cast to string (same as icon enums).

## Relationship columns

Use **dot notation** for a single relation path. The package applies `whereHas` / `whereDoesntHave` with null-safe `!=` on the leaf attribute:

```php
TextColumn::make('team.name')
    ->label('Team')
    ->inlineFilter();
```

## Configuration

After publishing `config/filament-table-inline-filters.php`:

| Key | Purpose |
|-----|---------|
| `icons.plus` / `icons.minus` | Default Heroicons for chip badges |
| `icons.plus_color` / `icons.minus_color` | Semantic colors for **table** +/- buttons (`success` / `danger` by default) |
| `icons.size` | `IconSize` for chips |
| `chip.color` | Filament badge color |
| `chip.equals_format` / `chip.not_equals_format` | Placeholders `:label` and `:value` |

Example:

```php
'chip' => [
    'equals_format' => ':label is :value',
    'not_equals_format' => ':label is not :value',
],
```

## URL persistence

`HasInlineFilters` exposes a public `$inlineFilters` array serialized to the query string as **`ilf`** (Livewire `#[Url]`). Users can bookmark or share filtered table URLs; browser back/forward updates filters when history is enabled.

## Security notes

- Only column names matching `^[a-zA-Z0-9_.]+$` are applied to the query.
- Operators are restricted to **`=`** and **`!=`**.
- Values come from the rendered cell state (JSON-encoded in the DOM); treat this like any client-supplied filter: rely on **authorization** and **policies** on the listing query.

## License

MIT. See `composer.json` for package metadata.
