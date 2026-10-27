<?php

namespace App\Support;

/**
 * Guesses which model an advert is for from its free-text title, given the
 * models that exist for its brand ("iPhone 12 Pro Max", "Galaxy S21 Ultra",
 * "Camon 40 Pro", "EliteBook", ...).
 *
 * Deliberately conservative — a wrong model files a listing on the wrong
 * model page, which is worse than leaving it unassigned:
 *  - spacing/punctuation is ignored ("12promax", "iphone11", "xs-max") and
 *    "+" reads as "plus" ("S10+");
 *  - a brand's series word ("iPhone", "Galaxy") may be left out only at the
 *    very start of the title or right after the brand name, before a number
 *    or a 2+ letter name ("11 pro max 64gb", "Samsung A14"), so stray numbers
 *    mid-title ("used 6 months") and single letters ("X") never match.
 *    Brands without a series word ("Camon 40", "Hot 40i") always need the
 *    full name;
 *  - a model followed by a variant word it doesn't contain ("iphone 12 max",
 *    "S21 FE") is skipped rather than downgraded to the base model;
 *  - a title naming two different models is ambiguous and matches nothing —
 *    including bare multi-word names mid-title ("xr to 17 pro max").
 */
class DeviceModelMatcher
{
    private const VARIANT_WORDS = 'pro|max|plus|mini|air|ultra|fe|lite|edge|neo|prime|premier|play|hd|power';

    private const SEPARATOR = '[\s\-_]*';

    private string $variantWords;

    /** @var array<int, string[]> model id => compiled patterns (one per name/alias) */
    private array $patterns = [];

    /** @var array<int, string[]> model id => patterns for a bare mention anywhere, only used to spot ambiguity */
    private array $hints = [];

    /**
     * @param array<int, string> $models       model id => model name
     * @param string|null        $seriesPrefix series word sellers may leave out ("iphone", "galaxy")
     * @param string[]           $brandWords   brand names that can stand in for the series word ("apple", "samsung")
     * @param array<string, string> $aliases   regex => replacement giving other ways a name is written
     * @param string|null        $variantWords words that make a different model ("pro|max|…"); '' for
     *                                         product lines (laptops: "EliteBook" covers "EliteBook 840 G5"),
     *                                         which also lets digits follow the name directly ("XPS13")
     */
    public function __construct(
        private array $models,
        private ?string $seriesPrefix = null,
        private array $brandWords = [],
        array $aliases = [],
        ?string $variantWords = null,
    ) {
        $this->variantWords = $variantWords ?? self::VARIANT_WORDS;

        foreach ($models as $id => $name) {
            $names = array_unique(array_merge([$name], array_map(
                fn ($pattern) => preg_replace($pattern, $aliases[$pattern], $name),
                array_keys($aliases),
            )));

            foreach ($names as $variant) {
                [$pattern, $hint] = $this->compile($variant);

                if ($pattern) {
                    $this->patterns[$id][] = $pattern;
                }
                if ($hint) {
                    $this->hints[$id][] = $hint;
                }
            }
        }
    }

    public function match(string $title): ?int
    {
        $title = $this->normalize($title);

        $hits = $this->hits($this->patterns, $title);

        // Drop hits that sit inside a longer hit for a different model.
        $hits = array_filter($hits, function ($hit) use ($hits) {
            foreach ($hits as $other) {
                if ($other['id'] !== $hit['id']
                    && $other['start'] <= $hit['start'] && $other['end'] >= $hit['end']
                    && ($other['end'] - $other['start']) > ($hit['end'] - $hit['start'])) {
                    return false;
                }
            }

            return true;
        });

        $ids = array_values(array_unique(array_column($hits, 'id')));

        if (count($ids) !== 1) {
            return null;
        }

        // A bare mention of another model outside the matched text makes it ambiguous.
        foreach ($this->hits($this->hints, $title) as $hint) {
            $insideMatch = false;
            foreach ($hits as $hit) {
                if ($hint['start'] >= $hit['start'] && $hint['end'] <= $hit['end']) {
                    $insideMatch = true;
                    break;
                }
            }

            if (!$insideMatch && $hint['id'] !== $ids[0]) {
                return null;
            }
        }

        return $ids[0];
    }

    private function normalize(string $text): string
    {
        return str_replace('+', ' plus ', strtolower($text));
    }

    private function hits(array $patterns, string $title): array
    {
        $hits = [];
        foreach ($patterns as $id => $idPatterns) {
            foreach ($idPatterns as $pattern) {
                if (preg_match_all($pattern, $title, $found, PREG_OFFSET_CAPTURE)) {
                    foreach ($found[0] as [$text, $offset]) {
                        $hits[] = ['id' => $id, 'start' => $offset, 'end' => $offset + strlen($text)];
                    }
                }
            }
        }

        return $hits;
    }

    /**
     * @return array{0: ?string, 1: ?string} [match pattern, ambiguity-hint pattern]
     */
    private function compile(string $name): array
    {
        // "iPhone 16e" → [iphone, 16, e]; "Galaxy S10+" → [galaxy, s, 10, plus]
        preg_match_all('/[a-z]+|[0-9]+/', $this->normalize($name), $m);
        $tokens = $m[0];

        if (!$tokens) {
            return [null, null];
        }

        $quote = fn (array $parts) => implode(self::SEPARATOR, array_map(fn ($t) => preg_quote($t, '/'), $parts));

        $full = $quote($tokens);

        $rest = array_slice($tokens, 1);
        $prefixOptional = $this->seriesPrefix !== null
            && $tokens[0] === strtolower($this->seriesPrefix)
            && $rest
            && (ctype_digit($rest[0]) || strlen($rest[0]) >= 2 || count($rest) >= 2);

        $alternatives = ['(?<![a-z0-9])' . $full];
        if ($prefixOptional) {
            $alternatives[] = '^\s*' . $quote($rest);
            if ($this->brandWords) {
                $brand = implode('|', array_map(fn ($w) => preg_quote(strtolower($w), '/'), $this->brandWords));
                $alternatives[] = '(?<![a-z0-9])(?:' . $brand . ')' . self::SEPARATOR . $quote($rest);
            }
        }
        $body = '(?:' . implode('|', $alternatives) . ')';

        // Don't let "iphone 12" match inside "iphone 12 max" (an unknown variant) — or
        // "12 prom max": anything starting with a variant word is treated as one.
        $tail = $this->variantWords === ''
            ? '(?![a-z])'
            : '(?![a-z0-9])(?!' . self::SEPARATOR . '(?:' . $this->variantWords . '))';

        // Bare "17 pro max" / "xr" / "a14" anywhere hints at another model; a lone number doesn't.
        $hint = $prefixOptional && (count($rest) >= 2 || !ctype_digit($rest[0]))
            ? '/(?<![a-z0-9])' . $quote($rest) . $tail . '/'
            : null;

        return ['/' . $body . $tail . '/', $hint];
    }
}
