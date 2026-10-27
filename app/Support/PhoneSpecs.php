<?php

namespace App\Support;

/**
 * Mobile Phones specs (phone_details.ram / storage): the options sellers pick
 * from, and reading them out of a listing's title and description when the
 * seller didn't pick them (the app, older listings, or a field left blank).
 *
 * Seller text is messy — "4+4GB RAM", "8GB RAM/128GB ROMWork", "6/128gb",
 * "256GIG rom n 16GIG ram", "12gb ram 128rom", "Samsung S10 124gig 5G" — so
 * storage is snapped to the nearest option (within 8%), and "5G"/"4G" (the
 * network) are never sizes. The title wins; the description fills the gaps,
 * since descriptions often list other variants ("128gb 420k 256gb 370k").
 */
class PhoneSpecs
{
    public const FIELDS = ['ram', 'storage'];

    public const RAM = ['1GB', '2GB', '3GB', '4GB', '6GB', '8GB', '12GB', '16GB', '24GB'];

    public const STORAGE = ['8GB', '16GB', '32GB', '64GB', '128GB', '256GB', '512GB', '1TB', '2TB'];

    public const LABELS = [
        'ram'     => 'RAM',
        'storage' => 'Storage',
    ];

    /** A size with an optional unit; a bare "g" only counts where a pattern allows it. */
    private const SIZE = '(\d+(?:\.\d+)?)\s*(tb|gb|gigs?|g)?';

    private const BARE_STORAGE = 'bare';

    private const STORAGE_WORD = '(?:internal\s*)?(?:rom|storage|memory)|internal';

    /**
     * Specs found in the title, then the description for any still missing.
     *
     * @return array<string, string>
     */
    public static function parse(string $title, string $description = ''): array
    {
        $specs = self::parseText($title);

        if (count($specs) < count(self::FIELDS) && trim($description) !== '') {
            $specs += self::parseText($description);
        }

        return array_intersect_key(array_merge(array_flip(self::FIELDS), $specs), $specs);
    }

    /**
     * The value to store for each field: the seller's choice when it is a
     * valid option, otherwise what the text says, otherwise null.
     *
     * @return array<string, ?string>
     */
    public static function resolve(array $input, string $title = '', string $description = ''): array
    {
        $parsed = null;
        $specs = [];

        foreach (self::FIELDS as $field) {
            $value = $input[$field] ?? null;
            if (is_string($value) && in_array($value, self::options($field), true)) {
                $specs[$field] = $value;
                continue;
            }

            $parsed ??= self::parse($title, $description);
            $specs[$field] = $parsed[$field] ?? null;
        }

        return $specs;
    }

    /** @return string[] */
    public static function options(string $field): array
    {
        return match ($field) {
            'ram'     => self::RAM,
            'storage' => self::STORAGE,
        };
    }

    /** @return array<string, string[]> field => options, for forms and the app */
    public static function allOptions(): array
    {
        return array_combine(self::FIELDS, array_map([self::class, 'options'], self::FIELDS));
    }

    /** @return array<string, string> */
    private static function parseText(string $text): array
    {
        // Line breaks separate facts: "12gb/16gb<br>Rom: 256gb" must not read as "16gb Rom"
        $text = html_entity_decode(preg_replace('/<[^>]*>|\R/', ' | ', $text), ENT_QUOTES | ENT_HTML5);
        $text = str_replace("\u{00A0}", ' ', $text);

        // RAM first, then blank it out so its sizes aren't read as storage
        [$ram, $text] = self::ram($text);
        $storage = self::storage($text);

        // "Xiaomi 13 12GB 512GB": a small bare size next to the storage is the RAM
        if (!$ram && $storage) {
            $ram = self::bareRam($text, $storage);
        }

        return array_filter(['ram' => $ram, 'storage' => $storage]);
    }

    /** @return array{0: ?string, 1: string} the RAM and the text with it blanked out */
    private static function ram(string $text): array
    {
        $patterns = [
            // "4GB RAM", "4+4GB RAM", "8gRAM", "12GB ramblack"
            '/(?<![\d.])(\d{1,2})\s*(?:gb|gigs?|g)\s*(?:\+\s*\d{1,2}\s*(?:gb|gigs?|g)?\s*)?(?:of\s*)?ram/i',
            '/(?<![\d.])(\d{1,2})\s*\+\s*\d{1,2}\s*(?:gb|gigs?|g)\s*(?:of\s*)?ram/i',
            // "RAM 4 Gb + 8GB", "Ram: 8gb", "RAM is 8GB+5GB"
            '/(?<![a-z])ram\s*(?:is\s*|size\s*)?[-:=]?\s*(?:of\s*)?(\d{1,2})\s*(?:gb|gigs?|g)?(?:\s*\+\s*\d{1,2}\s*(?:gb|gigs?|g)?)?(?!\.?\d)/i',
        ];

        // The first mention wins: "RAM 6GB expandable 16GB RAM" is 6GB
        $first = null;
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($matches as $m) {
                if (($ram = self::snapRam($m[1][0])) && ($first === null || $m[0][1] < $first[1][1])) {
                    $first = [$ram, $m[0]];
                }
            }
        }
        if ($first) {
            return [$first[0], self::blank($text, $first[1])];
        }

        // "6/128gb", "4G/64G", "8+256": RAM / storage pair (not "Iphone16/128gb")
        if (preg_match('/(?<![\w.])(\d{1,2})\s*(?:gb|g)?\s*[\/+]\s*(\d{1,4})\s*(tb|gb|g)?(?!\.?\d)/i', $text, $m)
            && ($ram = self::snapRam($m[1])) && self::snapStorage($m[2], $m[3] ?? '')) {
            return [$ram, $text];
        }

        return [null, $text];
    }

    private static function storage(string $text): ?string
    {
        $patterns = [
            // "128GB ROM", "128rom", "64gb Storage", "128gb internal memory"
            '/(?<![\d.])' . self::SIZE . '\s*(?:of\s*)?(?:' . self::STORAGE_WORD . ')/i',
            // "Storage: 128gb", "STORAGE 64", "ROM 256GB"
            '/(?<![a-z])(?:' . self::STORAGE_WORD . ')\s*(?:capacity\s*)?[-:=]?\s*(?:of\s*)?' . self::SIZE . '(?!\.?\d)/i',
            // "6/128gb", "4G/64G": the second half of a RAM / storage pair
            '/(?<![\w.])\d{1,2}\s*(?:gb|g)?\s*[\/+]\s*' . self::SIZE . '(?!\.?\d)/i',
            // A bare size with a unit: "iPhone 12 64GB", "64GBNo cracks", "256 gig"
            self::BARE_STORAGE => '/(?<![\d.])(\d{1,4}(?:\.\d+)?)\s*(tb|gb|gigs?|g)(?![a-z]*\s*ram)/i',
        ];

        foreach ($patterns as $kind => $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $storage = self::snapStorage($m[1], $m[2] ?? '');
                    // A bare size under 16GB is RAM ("8gb 128gb"), not storage
                    if ($storage && ($kind !== self::BARE_STORAGE || self::gigabytes($storage) >= 16)) {
                        return $storage;
                    }
                }
            }
        }

        return null;
    }

    /** The smallest bare size, when it is a RAM size and smaller than the storage. */
    private static function bareRam(string $text, string $storage): ?string
    {
        preg_match_all('/(?<![\d.])(\d{1,4})\s*gb/i', $text, $matches);
        $sizes = array_map('intval', $matches[1]);

        if (count(array_unique($sizes)) < 2) {
            return null;
        }

        $ram = self::snapRam((string) min($sizes));

        return $ram && (int) $ram < self::gigabytes($storage) && (int) $ram <= 16 ? $ram : null;
    }

    private static function snapRam(string $number): ?string
    {
        $value = (int) $number . 'GB';

        return in_array($value, self::RAM, true) ? $value : null;
    }

    /**
     * The nearest storage option within 8%. Without a unit (or with a bare
     * "g", which is also the network in "5G") only an exact size of 16GB or
     * more counts.
     */
    private static function snapStorage(string $number, string $unit): ?string
    {
        $unit = strtolower($unit);

        if ($unit === '' || $unit === 'g') {
            $value = (int) $number . 'GB';
            return (float) $number === (float) (int) $number && (int) $number >= 16 && in_array($value, self::STORAGE, true) ? $value : null;
        }

        $gb = (float) $number * ($unit === 'tb' ? 1000 : 1);
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

    /** @param array{0: string, 1: int} $match */
    private static function blank(string $text, array $match): string
    {
        return substr_replace($text, str_repeat(' ', strlen($match[0])), $match[1], strlen($match[0]));
    }
}
