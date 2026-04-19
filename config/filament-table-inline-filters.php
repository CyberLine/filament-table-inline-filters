<?php

declare(strict_types=1);

use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;

return [
    'icons' => [
        'plus' => Heroicon::OutlinedPlusCircle,
        'minus' => Heroicon::OutlinedMinusCircle,
        'size' => IconSize::Medium,
        'plus_color' => 'success',
        'minus_color' => 'danger',
    ],

    'chip' => [
        'color' => 'primary',
        'equals_format' => ':label: :value',
        'not_equals_format' => ':label: ! :value',
    ],
];
