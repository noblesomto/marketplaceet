<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The taxonomy SEO fields (keywords, meta_title, meta_description) were
 * copied from the Nigeria site, so category/subcategory/brand pages still said
 * "… for Sale in Nigeria | Marketplace.ng". This rewrites them for Ethiopia:
 * site name, country, Nigerian cities and Nigerian-only terms (tokunbo,
 * agbada). Safe to re-run — rows with nothing to change are left alone.
 */
class LocalizeSeoText extends Command
{
    protected $signature = 'seo:localize-ethiopia {--dry-run} {--show=5 : Sample rewrites to print per table}';

    protected $description = 'Rewrite Nigeria-era SEO text on categories, subcategories, brands and models for Ethiopia';

    private const TABLES = ['categories', 'sub_categories', 'brands', 'models'];

    private const COLUMNS = ['keywords', 'meta_title', 'meta_description'];

    /**
     * Applied in order. Plain-word rules keep the case of what they replace
     * (nigeria → ethiopia, Nigeria → Ethiopia).
     */
    private const WORDS = [
        'marketplace.ng' => 'Marketplace Ethiopia',
        'marketplace ng' => 'marketplace ethiopia',
        'marketplace naija' => 'marketplace ethiopia',
        'nigerians' => 'ethiopians',
        'nigerian' => 'ethiopian',
        'nigeria' => 'ethiopia',
        'naija' => 'ethiopia',
        'lagos' => 'addis ababa',
        'abuja' => 'adama',
        'ogun' => 'bahir dar',
        'tokunbo' => 'foreign used',
        'agbada' => 'traditional wear',
        'naira' => 'birr',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $show = (int) $this->option('show');
        $total = 0;

        DB::transaction(function () use ($dryRun, $show, &$total) {
            foreach (self::TABLES as $table) {
                $changed = 0;
                $overLength = 0;

                DB::table($table)->select(array_merge(['id'], self::COLUMNS))->orderBy('id')
                    ->chunkById(500, function ($rows) use ($table, $dryRun, $show, &$changed, &$overLength) {
                        foreach ($rows as $row) {
                            $updates = [];
                            foreach (self::COLUMNS as $column) {
                                if ($row->$column === null) continue;
                                $new = $this->localize($row->$column);
                                if ($column === 'meta_description') {
                                    $new = $this->fitMetaDescription($new, $row->$column);
                                }
                                if ($new !== $row->$column) {
                                    $updates[$column] = $new;
                                }
                            }
                            if (!$updates) continue;

                            $changed++;
                            if (mb_strlen($updates['meta_description'] ?? '') > 160) $overLength++;

                            if ($changed <= $show) {
                                $this->line("<comment>{$table} #{$row->id}</comment>");
                                foreach ($updates as $column => $new) {
                                    $this->line("  {$column}: {$row->$column}");
                                    $this->line("  <info>→</info> {$new}");
                                }
                            }

                            if (!$dryRun) {
                                DB::table($table)->where('id', $row->id)->update($updates);
                            }
                        }
                    });

                $this->info("{$table}: {$changed} row(s) " . ($dryRun ? 'would change' : 'updated')
                    . ($overLength ? " ({$overLength} meta description(s) now over 160 chars)" : ''));
                $total += $changed;
            }
        });

        $this->info($dryRun ? "Dry run — {$total} row(s) would change." : "Done — {$total} row(s) updated.");

        return self::SUCCESS;
    }

    private function localize(string $text): string
    {
        // The Nigeria site's inventory claim doesn't hold here.
        $text = preg_replace('/\s*Over [\d,]+ \w+ in Nigeria for sale\.?/i', '', $text);

        foreach (self::WORDS as $from => $to) {
            $text = preg_replace_callback(
                '/(?<![\p{L}\d])' . preg_quote($from, '/') . '(?![\p{L}\d])/iu',
                fn ($m) => $this->matchCase($m[0], $to),
                $text
            );
        }

        // "foreign used and foreign used" after the tokunbo rewrite
        $text = preg_replace('/\b(foreign used)(,? (?:and|or) )foreign used\b/i', '$1', $text);
        $text = preg_replace('/\bforeign used, foreign used\b/i', 'foreign used', $text);
        $text = preg_replace('/\bforeign used,? and locally used\b/i', 'foreign and locally used', $text);

        return $text;
    }

    /**
     * "Marketplace Ethiopia" is longer than "Marketplace.ng", which pushes some
     * descriptions past 160 chars. The page title already names the site, so
     * drop a trailing "on Marketplace Ethiopia" before letting it run long.
     */
    private function fitMetaDescription(string $text, string $original): string
    {
        if (mb_strlen($text) <= 160 || mb_strlen($original) > 160) {
            return $text;
        }

        return preg_replace('/\s+(?:on|at|via) Marketplace Ethiopia( today)?(\.?)\s*$/u', '$1.', $text);
    }

    private function matchCase(string $original, string $replacement): string
    {
        if ($original === mb_strtoupper($original)) {
            return mb_strtoupper($replacement);
        }
        if ($original === mb_strtolower($original)) {
            return $replacement;
        }
        // Capitalised (or brand-styled like "Marketplace.ng"): title-case each word
        return $replacement === mb_strtolower($replacement)
            ? mb_convert_case($replacement, MB_CASE_TITLE)
            : $replacement;
    }
}
