<?php
namespace App\Helpers;

class ContentHelper
{
    /**
     * Patterns that indicate a user is trying to share contact info (phone
     * number, email, "message me on WhatsApp", etc.) outside the platform.
     * Single source of truth for both sanitizeContent() (silent strip, kept
     * as a defensive backstop) and detectBannedContact() (used to reject
     * the submission with an explicit reason instead of silently saving
     * stripped text — see App\Http\Controllers\User\UserManageAdverts).
     *
     * The phone patterns allow an optional separator (space/dash/dot)
     * between *every* digit, not just at fixed grouping points — a
     * hard-coded grouping like "0912 345 678" only catches that one
     * grouping; a user typing "091 2345 678" (a different but equally
     * valid way to space out the same 10 digits) sailed straight through
     * the old fixed-grouping patterns.
     */
    protected static array $bannedPatterns = [
        '/\b(whatsapp|telegram|viber|imo)\b/i'              => 'contact_phrase',
        '/\b(call|contact|message|text|dm|inbox)\s+me\b/i'  => 'contact_phrase',
        '/\bphone\b/i'                                       => 'contact_phrase',
        '/\b\w+@\w+\.\w+\b/'                                 => 'email',
        // Ethiopian local format: 0 + 7/9 + 8 more digits (10 total), any spacing
        '/\b0[\s\-.]?[79](?:[\s\-.]?\d){8}\b/'               => 'phone',
        // Ethiopian international format: (+)251 + 9 digits, any spacing
        '/(?<!\w)\+?251(?:[\s\-.]?\d){9}\b/'                 => 'phone',
        // Fallback: any remaining 11–15 consecutive digits (unformatted foreign numbers, etc.)
        '/\b\d{11,15}\b/'                                     => 'phone',
    ];

    /**
     * Whether $content contains contact info that shouldn't be in a public
     * listing. Returns a human-readable reason for the rejection message,
     * or null if the content is clean. Callers should reject the
     * submission with this reason rather than silently stripping and
     * saving — see the class docblock on $bannedPatterns for why.
     */
    public static function detectBannedContact(?string $content): ?string
    {
        if (empty($content)) {
            return null;
        }

        foreach (self::$bannedPatterns as $pattern => $type) {
            if (preg_match($pattern, $content)) {
                return match ($type) {
                    'phone'          => 'a phone number',
                    'email'          => 'an email address',
                    'contact_phrase' => 'a request to contact you outside the app (e.g. "call me", "WhatsApp me")',
                    default          => 'contact information',
                };
            }
        }

        return null;
    }

    public static function sanitizeContent($content)
    {
        if (empty($content)) {
            return '';
        }

        // Step 1: Remove banned patterns (contact info, etc.) — defensive
        // backstop; callers should already have rejected this via
        // detectBannedContact() before reaching here.
        foreach (array_keys(self::$bannedPatterns) as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        // Step 2: Replace literal HTML entity strings FIRST (before decoding)
        $content = str_replace(
            ['&nbsp;', '&amp;', '&lt;', '&gt;', '&quot;', '&#39;', '&apos;', '&ndash;', '&mdash;', '&rsquo;', '&lsquo;', '&rdquo;', '&ldquo;'],
            [' ', '&', '<', '>', '"', "'", "'", '-', '-', "'", "'", '"', '"'],
            $content
        );

        // Step 3: Decode HTML entities multiple times (handles double/triple encoding)
        $iterations = 0;
        $previousContent = '';
        while ($content !== $previousContent && $iterations < 5) {
            $previousContent = $content;
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $iterations++;
        }

        // Step 4: Strip all HTML/XML tags
        $content = strip_tags($content);

        // Step 5: Replace common HTML tag strings that might be literal text
        $content = str_replace(
            ['<br>', '<br/>', '<br />', '</br>', '<p>', '</p>', '<div>', '</div>', '<span>', '</span>', '&lt;br&gt;', '&lt;/br&gt;'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
            $content
        );

        // Step 6: Remove any remaining HTML entity patterns (both &#xxx; and &name;)
        $content = preg_replace('/&[a-zA-Z0-9#]+;/', ' ', $content);

        // Step 7: Remove special characters and symbols (keep letters, numbers, basic punctuation)
        $content = preg_replace('/[^\p{L}\p{N}\s\-.,!?$€£¥&@()\'\"\/]/u', '', $content);

        // Step 8: Clean up spacing around punctuation
        $content = preg_replace('/\s*([.,!?;:])\s*/', '$1 ', $content);  // Add single space after punctuation
        $content = preg_replace('/\s+([.,!?;:])/', '$1', $content);  // Remove space before punctuation

        // Step 9: Replace multiple whitespace with single space
        $content = preg_replace('/\s+/', ' ', $content);

        // Step 10: Fix common spacing issues
        $content = preg_replace([
            '/\s*[,:;]\s*[,:;]\s*/',  // Multiple punctuation: ": ," becomes ","
            '/\s*[,:;]\s*$/',         // Trailing punctuation
            '/^\s*[,:;]\s*/',         // Leading punctuation
            '/([.,!?])\1+/',          // Repeated punctuation: "..." becomes "."
        ], [
            ', ',
            '',
            '',
            '$1'
        ], $content);

        // Step 11: Trim and clean up final result
        $content = trim($content);

        // Step 12: Remove empty parentheses and brackets
        $content = preg_replace('/\(\s*\)|\[\s*\]|\{\s*\}/', '', $content);

        // Step 13: Final trim
        return trim($content);
    }

    /**
     * Sanitize title (stricter rules, no line breaks allowed)
     */
    public static function sanitizeTitle($title)
    {
        $title = self::sanitizeContent($title);

        // Remove any remaining newlines or tabs from titles
        $title = preg_replace('/[\r\n\t]+/', ' ', $title);

        $title = self::normalizeShoutingCase($title);

        // Limit title length if needed (optional)
        $title = mb_substr($title, 0, 200);

        return trim($title);
    }

    /**
     * Sanitize description (allows more formatting)
     */
    public static function sanitizeDescription($description)
    {
        $description = self::sanitizeContent($description);

        // Preserve paragraph breaks (convert multiple newlines to double space)
        $description = preg_replace('/[\r\n]{2,}/', "\n\n", $description);

        $description = self::normalizeShoutingCase($description);

        return trim($description);
    }

    /**
     * Public entry point for backfilling case on already-stored text
     * (e.g. existing ads), without re-running the rest of sanitizeContent()
     * against text that's already clean.
     */
    public static function normalizeCaseOnly(?string $text): string
    {
        return self::normalizeShoutingCase((string) $text);
    }

    /**
     * Known product/brand names with non-standard internal capitalization
     * that word-by-word title-casing can't derive on its own (e.g. "iphone"
     * -> "Iphone" is wrong; it needs to become "iPhone"). Keyed lowercase.
     */
    protected static array $brandExceptions = [
        'i'           => 'I',
        'iphone'      => 'iPhone',
        'ipad'        => 'iPad',
        'ipod'        => 'iPod',
        'imac'        => 'iMac',
        'ios'         => 'iOS',
        'macbook'     => 'MacBook',
        'airpods'     => 'AirPods',
        'whatsapp'    => 'WhatsApp',
        'playstation' => 'PlayStation',
        'youtube'     => 'YouTube',
        'paypal'      => 'PayPal',
        'ebay'        => 'eBay',
        'tiktok'      => 'TikTok',
    ];

    /**
     * Acronyms/units/initialisms that must stay fully uppercase rather than
     * being sentence-cased like ordinary words. Deliberately explicit
     * (rather than "any short word") — common short English words (THE, IS,
     * ARE, NOT, WHY...) are exactly as short as real acronyms and must NOT
     * be preserved uppercase, or the "fix" still reads as half-shouting.
     */
    protected static array $acronymWhitelist = [
        'TV', 'UK', 'US', 'USA', 'ID', 'CV', 'AC', 'DC', 'PC', 'TB', 'GB', 'MB', 'KB', 'SD',
        'SIM', 'POS', 'ATM', 'GPS', 'VIP', 'PDF', 'PNG', 'GIF', 'SUV', 'DIY', 'CEO', 'CFO', 'CTO',
        'LED', 'LCD', 'USB', 'FM', 'AM', 'ETB', 'USD',
        'HDTV', 'WIFI', 'HDMI', 'JPEG', 'OLED', 'QLED', 'DSLR',
    ];

    /**
     * If $text is "shouting" (overwhelmingly uppercase), convert it to
     * sentence case. Runs on title/description at save time so every
     * surface that reads them back (cards, search, meta tags, SMS/email
     * notifications) is consistently clean — a CSS text-transform only
     * fixes one view and leaves the stored text (and everything else that
     * reads it raw) still shouting.
     *
     * Word-by-word rather than a blanket lowercase, so meaningful ALL-CAPS
     * content isn't destroyed along with the shouting: a letters-only token
     * touching a digit (model/spec codes like "64GB", "RC350", "RJ45TF") is
     * left completely untouched, and known brand names / acronyms are
     * restored via the maps above regardless of length. Everything else
     * gets normal sentence case: capitalized at the start of the text and
     * after ./!/? or a line break, lowercase elsewhere — including short
     * words that aren't real acronyms (THE, IS, ARE, SO...), since treating
     * "short" as a proxy for "acronym" just leaves half the sentence
     * shouting.
     *
     * Left alone entirely otherwise, since normal mixed-case text
     * shouldn't be touched.
     */
    protected static function normalizeShoutingCase(string $text): string
    {
        if (! self::isShouting($text)) {
            return $text;
        }

        preg_match_all('/\p{L}+/u', $text, $matches, PREG_OFFSET_CAPTURE);

        $result = '';
        $cursor = 0;
        $sentenceStart = true;

        foreach ($matches[0] as [$token, $byteOffset]) {
            $gap = substr($text, $cursor, $byteOffset - $cursor);
            $result .= $gap;

            if (preg_match('/[.!?]\s*$/u', $gap) || str_contains($gap, "\n")) {
                $sentenceStart = true;
            }

            $tokenEnd = $byteOffset + strlen($token);
            $before = $byteOffset > 0 ? substr($text, $byteOffset - 1, 1) : '';
            $after = substr($text, $tokenEnd, 1);
            $touchesDigit = ($before !== '' && ctype_digit($before)) || ($after !== '' && ctype_digit($after));

            $lower = mb_strtolower($token, 'UTF-8');
            $upper = mb_strtoupper($token, 'UTF-8');

            if ($touchesDigit) {
                $result .= $token; // spec/model code, e.g. "64GB", "RC350" — leave untouched
            } elseif (isset(self::$brandExceptions[$lower])) {
                $result .= self::$brandExceptions[$lower];
            } elseif (in_array($upper, self::$acronymWhitelist, true)) {
                $result .= $upper;
            } elseif ($sentenceStart) {
                $result .= mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($lower, 1, null, 'UTF-8');
            } else {
                $result .= $lower;
            }

            $sentenceStart = false;
            $cursor = $byteOffset + strlen($token);
        }

        $result .= substr($text, $cursor);

        return $result;
    }

    /**
     * Whether $text is overwhelmingly uppercase (i.e. the user had caps
     * lock on), as opposed to normal text with a few capitalized words.
     */
    protected static function isShouting(string $text): bool
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $text);

        // Too short to judge reliably (e.g. "TV", "PS5").
        if (mb_strlen($letters, 'UTF-8') < 8) {
            return false;
        }

        $upper = preg_replace('/[^\p{Lu}]/u', '', $text);

        return (mb_strlen($upper, 'UTF-8') / mb_strlen($letters, 'UTF-8')) > 0.7;
    }

    /**
     * Sanitize for meta tags (most strict)
     */
    public static function sanitizeForMeta($content, $maxLength = 160)
    {
        $content = self::sanitizeContent($content);

        // Remove all newlines for meta tags
        $content = preg_replace('/[\r\n\t]+/', ' ', $content);

        // Trim to max length
        if (mb_strlen($content) > $maxLength) {
            $content = mb_substr($content, 0, $maxLength);
            // Try to cut at last complete word
            $lastSpace = mb_strrpos($content, ' ');
            if ($lastSpace !== false && $lastSpace > $maxLength * 0.8) {
                $content = mb_substr($content, 0, $lastSpace);
            }
            $content .= '...';
        }

        return trim($content);
    }

    /**
     * Sanitize keywords
     */
    public static function sanitizeKeywords($keywords)
    {
        $keywords = self::sanitizeContent($keywords);

        // Convert to lowercase
        $keywords = mb_strtolower($keywords);

        // Remove extra commas
        $keywords = preg_replace('/,+/', ',', $keywords);
        $keywords = trim($keywords, ',');

        return $keywords;
    }

    public static function sanitizeName($name)
    {
        if (empty($name)) {
            return '';
        }

        // Step 1: Remove emojis (all emoji Unicode ranges)
        $name = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $name); // Emoticons
        $name = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $name); // Misc Symbols and Pictographs
        $name = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $name); // Transport and Map
        $name = preg_replace('/[\x{1F1E0}-\x{1F1FF}]/u', '', $name); // Flags
        $name = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $name);   // Misc symbols
        $name = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $name);   // Dingbats
        $name = preg_replace('/[\x{1F900}-\x{1F9FF}]/u', '', $name); // Supplemental Symbols and Pictographs
        $name = preg_replace('/[\x{1FA00}-\x{1FA6F}]/u', '', $name); // Chess Symbols
        $name = preg_replace('/[\x{1FA70}-\x{1FAFF}]/u', '', $name); // Symbols and Pictographs Extended-A
        $name = preg_replace('/[\x{FE00}-\x{FE0F}]/u', '', $name);   // Variation Selectors
        $name = preg_replace('/[\x{1F000}-\x{1F02F}]/u', '', $name); // Mahjong Tiles
        $name = preg_replace('/[\x{1F0A0}-\x{1F0FF}]/u', '', $name); // Playing Cards

        // Step 2: Check for banned names/words (case-insensitive)
        $bannedNames = [
            'market',
            'marketplace',
            'marketplace naija',
            'marketplace ng',
            'marketplace ethiopia',
            'marketplace et',
            'admin',
            'administrator',
            'moderator',
            'support',
            'system',
            'official',
        ];

        foreach ($bannedNames as $banned) {
            // Replace whole words or the entire string
            $name = preg_replace('/\b' . preg_quote($banned, '/') . '\b/i', '', $name);
        }

        // Step 3: Apply general content sanitization
        $name = self::sanitizeContent($name);

        // Step 4: Remove any remaining special characters that shouldn't be in names
        $name = preg_replace('/[^\p{L}\p{N}\s\-\'\.]/u', '', $name);

        // Step 5: Clean up extra spaces
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        // Step 6: Limit name length
        $name = mb_substr($name, 0, 100);

        return $name;
    }
}