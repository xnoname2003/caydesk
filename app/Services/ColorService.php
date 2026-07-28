<?php

namespace App\Services;

use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;

class ColorService
{
    public static function getHex(?string $color, int $defaultShade = 500): string
    {
        if (!$color) {
            return Color::Zinc[$defaultShade] ?? '#71717a'; 
        }

        $color = strtolower(trim($color));
        $shade = $defaultShade;

        if (str_contains($color, '-')) {
            $parts = explode('-', $color);
            $colorKey = $parts[0];
            $shade = (int) $parts[1];
        } else {
            $colorKey = $color;
        }

        $semanticColors = FilamentColor::getColors();
        if (isset($semanticColors[$colorKey][$shade])) {
            return $semanticColors[$colorKey][$shade];
        }

        $tailwindColors = Color::all();
        if (isset($tailwindColors[$colorKey][$shade])) {
            return $tailwindColors[$colorKey][$shade];
        }

        return $color;
    }
}