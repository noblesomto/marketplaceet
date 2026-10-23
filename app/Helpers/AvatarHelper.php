<?php

namespace App\Helpers;

class AvatarHelper
{
    public static function generateInitials(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '?';
        }

        $words = explode(' ', $name);

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public static function generateColor(string $name): string
    {
        $colors = [
            '#3b82f6', // blue
            '#10b981', // green
            '#f59e0b', // amber
            '#ef4444', // red
            '#8b5cf6', // purple
            '#ec4899', // pink
            '#14b8a6', // teal
        ];

        $name = trim($name);
        if ($name === '') {
            return $colors[0];
        }

        $index = ord(strtolower($name[0])) % count($colors);
        return $colors[$index];
    }
}
