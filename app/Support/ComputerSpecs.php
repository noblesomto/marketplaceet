<?php

namespace App\Support;

/**
 * Laptops & Computers specs (computer_details.processor / ram / storage /
 * storage_type): the options sellers pick from, and reading them out of a
 * listing's title and description when the seller didn't pick them (the app,
 * older listings, or a field left blank).
 *
 * Seller text is messy — "Corei58GB RAM256GB SSD", "16/512", "8gb Ram 256 ssd",
 * "520gig rom", "4GB dedicated graphics" — so sizes are snapped to the nearest
 * option (within 8%) and graphics memory is ignored.
 */
class ComputerSpecs
{
    public const FIELDS = ['processor', 'ram', 'storage', 'storage_type'];

    public const PROCESSORS = [
        'Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'Intel Core i9',
        'Intel Core Ultra 5', 'Intel Core Ultra 7', 'Intel Core Ultra 9',
        'Intel Celeron', 'Intel Pentium', 'Intel Core M', 'Intel Core 2 Duo', 'Intel Xeon',
        'AMD Ryzen 3', 'AMD Ryzen 5', 'AMD Ryzen 7', 'AMD Ryzen 9', 'AMD A-Series', 'AMD Athlon',
        'Apple M1', 'Apple M2', 'Apple M3', 'Apple M4',
        'Other',
    ];

    public const RAM = ['2GB', '4GB', '6GB', '8GB', '12GB', '16GB', '24GB', '32GB', '48GB', '64GB'];

    public const STORAGE = ['16GB', '32GB', '64GB', '128GB', '256GB', '320GB', '500GB', '512GB', '750GB', '1TB', '2TB'];

    public const STORAGE_TYPES = ['SSD', 'HDD', 'SSD + HDD', 'eMMC'];

    public const LABELS = [
        'processor'    => 'Processor',
        'ram'          => 'RAM',
        'storage'      => 'Storage',
        'storage_type' => 'Storage Type',
    ];

    /** Patterns => processor, matched case-insensitively; the earliest in the text wins. */
    private const PROCESSOR_PATTERNS = [
        '/core\s*\(?(?:tm)?\)?\s*ultra\s*([579])\b/i'                                 => 'Intel Core Ultra $1',
        '/(?:core\s*\(?(?:tm)?\)?\s*|intel\s*|\b)i\s?([3579])(?![0-9a-z])/i'          => 'Intel Core i$1',
        '/(?:core\s*\(?(?:tm)?\)?\s*|intel\s*|\b)i([3579])-?\d{4,5}/i'                => 'Intel Core i$1',
        '/ryzen\s*([3579])\b/i'                                                         => 'AMD Ryzen $1',
        '/celeron/i'                                                                    => 'Intel Celeron',
        '/pentium/i'                                                                    => 'Intel Pentium',
        '/core\s*2\s*duo/i'                                                             => 'Intel Core 2 Duo',
        '/core\s*m\s?[357]?\b/i'                                                        => 'Intel Core M',
        '/xeon/i'                                                                       => 'Intel Xeon',
        '/athlon/i'                                                                     => 'AMD Athlon',
        '/\bamd\s*a\s?(?:4|6|8|9|10|12)\b|\ba(?:4|6|8|9|10|12)-\d{4}/i'                => 'AMD A-Series',
        '/(?:apple|mac\s*book(?:\s*(?:air|pro))?|mac\s*mini|imac|chip)\s*:?\s*m([1-4])\b|\bm([1-4])\s*(?:pro|max)?\s*chip/i' => 'Apple M$1$2',
    ];

    /** Memory that belongs to the graphics card (up to 24GB), not the computer. */
    private const GRAPHICS_MEMORY = '/(?:gtx|rtx|quadro|radeon|geforce|nvidia|graphics?|arc|mx)(?:(?!gb|ram|memory)[\w\s()-]){0,16}?(?<!\d)(?:[1-9]|1\d|2[0-4])\s*g(?:b|ig)?\b(?!\s*(?:of\s*)?(?:ddr\d\w*\s*)?(?:ram|memory))|(?<!\d)(?:[1-9]|1\d|2[0-4])\s*g(?:b|ig)?\s*(?:of\s*)?(?:(?:nvidia|amd|radeon|geforce)\s*)?(?:dedicated|graphics?|gddr\d*|vram|video)/i';

    private const SIZE = '(\d+(?:\.\s?\d+)?)\s*(tb|t|gb|gig|gigs|g)?';

    private const STORAGE_WORD = '(ssd|nvme|m\.2|emmc|hdd|hard\s*(?:disk|drive)|hd\b|storage|rom\b|flash)';

    /**
     * Specs found in the text, by field (only the ones found).
     *
     * @return array<string, string>
     */
    public static function parse(string $text): array
    {
        $text = html_entity_decode(strip_tags($text));
        // "Corei58GB", "Ryzen 516GB", "core i52. 30Ghz": split the processor from what's glued to it
        $text = preg_replace('/((?:core|\b)\s*i[3579]|ryzen\s*[3579]|ultra\s*[579])(?=\d{1,4}\s*(?:gb|tb|g\b|ssd|hdd)|\d\.\s?\d+\s*ghz)/i', '$1 ', $text);
        $text = preg_replace(self::GRAPHICS_MEMORY, ' ', $text);
        $specs = array_filter([
            'processor' => self::processor($text),
            'ram'       => self::ram($text),
        ]);

        return $specs + self::storage($text, $specs['ram'] ?? null);
    }

    /**
     * The value to store for each field: the seller's choice when it is a
     * valid option, otherwise what the text says, otherwise null.
     *
     * @return array<string, ?string>
     */
    public static function resolve(array $input, string $text = ''): array
    {
        $parsed = null;
        $specs = [];

        foreach (self::FIELDS as $field) {
            $value = $input[$field] ?? null;
            if (is_string($value) && in_array($value, self::options($field), true)) {
                $specs[$field] = $value;
                continue;
            }

            $parsed ??= $text !== '' ? self::parse($text) : [];
            $specs[$field] = $parsed[$field] ?? null;
        }

        return $specs;
    }

    /** @return string[] */
    public static function options(string $field): array
    {
        return match ($field) {
            'processor'    => self::PROCESSORS,
            'ram'          => self::RAM,
            'storage'      => self::STORAGE,
            'storage_type' => self::STORAGE_TYPES,
        };
    }

    /** @return array<string, string[]> field => options, for forms and the app */
    public static function allOptions(): array
    {
        return array_combine(self::FIELDS, array_map([self::class, 'options'], self::FIELDS));
    }

    /** "512GB SSD", "8GB", … — the storage line shown on a listing. */
    public static function storageLabel(?string $storage, ?string $type): ?string
    {
        return trim(($storage ?? '') . ' ' . ($type ?? '')) ?: null;
    }

    private static function processor(string $text): ?string
    {
        $best = null;
        foreach (self::PROCESSOR_PATTERNS as $pattern => $name) {
            if (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE) && ($best === null || $m[0][1] < $best[0])) {
                $value = preg_replace_callback('/\$(\d)/', fn ($ref) => $m[(int) $ref[1]][0] ?? '', $name);
                $best = [$m[0][1], $value];
            }
        }

        return $best[1] ?? null;
    }

    private static function ram(string $text): ?string
    {
        $patterns = [
            '/' . self::SIZE . '\s*(?:of\s*)?(?:ddr\d\w*\s*)?(?:\d{4}\s*mhz\s*)?(?:rgb\s*)?(?:ram|memory|unified)/i',
            '/(?:ram|memory)\s*[-:=]?\s*(?:of\s*)?' . self::SIZE . '/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    if ($ram = self::snapRam($m[1], $m[2] ?? '')) {
                        return $ram;
                    }
                }
            }
        }

        // "8GB/256GB", "16/512"
        if (preg_match('/(?<![\w.])(\d{1,2})\s*(?:gb|g)?\s*\/\s*(?:\d{3,4}|\d\s*tb)(?!\d)/i', $text, $m)) {
            return self::snapRam($m[1], 'gb');
        }

        // A bare small size: "Core i5 8GB 256GB SSD"
        if (preg_match_all('/' . self::SIZE . '(?![a-z0-9])/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $unit = strtolower($m[2] ?? '');
                if ($unit !== '' && $unit[0] === 'g' && ($ram = self::snapRam($m[1], $unit)) && (int) $ram <= 32) {
                    return $ram;
                }
            }
        }

        return null;
    }

    /** @return array{storage?: string, storage_type?: string} */
    private static function storage(string $text, ?string $ram): array
    {
        $found = [];
        $patterns = [
            '/' . self::SIZE . '\s*(?:of\s*)?(?:(?:pcie|sata|gen\s?\d\w*|m\.2)\s*)*' . self::STORAGE_WORD . '/i' => [1, 2, 3],
            // "Storage: 512GB", "HDD 500GB" — but not "Hard drive: 256 GB SSD", which the line above reads
            '/(?:\b|(?<=gb))' . self::STORAGE_WORD . '\s*(?:capacity\s*)?(?:drive\s*)?[-:=]?\s*(?:of\s*)?(?>' . self::SIZE . ')(?!\s*(?:pcie\s*|sata\s*)*(?:ssd|nvme|m\.2|emmc|hdd|hard))/i' => [2, 3, 1],
        ];

        foreach ($patterns as $pattern => [$size, $unit, $word]) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($matches as $m) {
                $type = self::storageType($m[$word][0]);
                $value = self::snapStorage($m[$size][0], $m[$unit][0] ?? '');
                // "SSD 1080 screen": a number after the word needs a unit unless it is an exact size
                if ($word === 1 && ($m[$unit][0] ?? '') === '' && !in_array((int) $m[$size][0] . 'GB', self::STORAGE, true)) {
                    continue;
                }
                // "RAM 16GB Storage 256GB": the 16GB before "Storage" is the RAM
                $isRam = $word === 3 && $type === null && in_array($value, self::RAM, true)
                    && ($value === $ram || preg_match('/(?:ram|memory)\s*[-:=]?\s*$/i', substr($text, max(0, $m[0][1] - 10), min(10, $m[0][1]))));
                if ($value && !$isRam) {
                    $found[] = [$m[0][1], $value, $type];
                }
            }
        }

        // "8GB/256GB", "16/512"
        if (!$found && preg_match('/(?<![\w.])\d{1,2}\s*(?:gb|g)?\s*\/\s*(\d{3,4}|\d)(?!\d)\s*(tb|t|gb|g)?/i', $text, $m, PREG_OFFSET_CAPTURE)
            && ($value = self::snapStorage($m[1][0], $m[2][0] ?? ($m[1][0] < 10 ? 'tb' : 'gb')))) {
            $found[] = [$m[0][1], $value, null];
        }

        // A bare large size: "Core i5 8GB 256GB"
        if (!$found && preg_match_all('/' . self::SIZE . '(?![a-z0-9])/i', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches as $m) {
                $unit = strtolower($m[2][0] ?? '');
                $value = $unit !== '' ? self::snapStorage($m[1][0], $unit) : null;
                if ($value && $value !== $ram && self::gigabytes($value) >= 120) {
                    $found[] = [$m[0][1], $value, null];
                    break;
                }
            }
        }

        if (!$found) {
            return [];
        }

        usort($found, fn ($a, $b) => $a[0] <=> $b[0]);
        $types = array_values(array_unique(array_filter(array_column($found, 2))));
        $type = count($types) > 1 && !array_diff(['SSD', 'HDD'], $types) ? 'SSD + HDD' : ($found[0][2] ?? $types[0] ?? null);

        return array_filter(['storage' => $found[0][1], 'storage_type' => $type]);
    }

    private static function storageType(string $word): ?string
    {
        $word = strtolower($word);

        return match (true) {
            in_array($word, ['ssd', 'nvme', 'm.2'], true) => 'SSD',
            $word === 'emmc'                               => 'eMMC',
            $word === 'hdd' || $word === 'hd' || str_starts_with($word, 'hard') => 'HDD',
            default                                        => null,
        };
    }

    private static function snapRam(string $number, string $unit): ?string
    {
        if (str_starts_with(strtolower($unit), 't')) {
            return null;
        }

        $value = (int) round((float) str_replace(' ', '', $number)) . 'GB';

        return in_array($value, self::RAM, true) ? $value : null;
    }

    private static function snapStorage(string $number, string $unit): ?string
    {
        $gb = (float) str_replace(' ', '', $number) * (str_starts_with(strtolower($unit), 't') ? 1000 : 1);
        $nearest = null;

        foreach (self::STORAGE as $option) {
            $off = abs($gb - self::gigabytes($option)) / self::gigabytes($option);
            if ($off <= 0.08 && ($nearest === null || $off < $nearest[0])) {
                $nearest = [$off, $option];
            }
        }

        return $nearest[1] ?? null;
    }

    private static function gigabytes(string $option): int
    {
        return str_ends_with($option, 'TB') ? (int) $option * 1000 : (int) $option;
    }
}
