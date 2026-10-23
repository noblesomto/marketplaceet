<?php

namespace App\Traits;

use App\Models\Lga;
use App\Models\State;

trait GeneratesSeoMeta
{
    /**
     * Generate SEO meta title, description, and on-page content (H1, intro,
     * FAQs, tips) for listing pages.
     *
     * Formula is driven by the category's seo_group:
     *   product  → "Buy {name} Online in {location} | {site}"
     *   property → "{name} for Rent & Sale in {location} | {site}"
     *   service  → "Find {name} in {location} | {site}"
     *
     * @param  string $name      Display name (category, subcategory, or "Brand Subcat")
     * @param  string $group     seo_group value from the categories table
     * @param  string $canonical Clean URL without query parameters or location prefix
     * @param  string $location  Filtered location — defaults to "Ethiopia"
     * @param  int    $count     Active listing count, used in on-page copy
     * @return array{seoTitle: string, seoDesc: string, seoCanonical: string, seoH1: string, seoIntro: string, seoFaqs: array, seoTips: array}
     */
    private function buildSeoMeta(string $name, string $group, string $canonical, string $location = 'Ethiopia', int $count = 0): array
    {
        $site = config('global.site_name');

        switch ($group) {
            case 'property':
                $title = "{$name} for Rent & Sale in {$location} | {$site}";
                $desc  = "Find the best {$name} in {$location} on {$site}. Browse verified listings from agents and landlords. Schedule an inspection today.";
                $h1    = "{$name} for Rent & Sale in {$location}";
                $intro = "Explore {$count} {$name} listings in {$location} on {$site}. Connect with agents and landlords, compare prices, and schedule inspections directly.";
                $faqs  = [
                    ["q" => "How much is {$name} in {$location}?", "a" => "Rent and sale prices vary by neighbourhood, size and finish. Browse the current listings above to compare {$name} prices in {$location}."],
                    ["q" => "Do I need an agent to rent or buy {$name} in {$location}?", "a" => "No, {$site} lets you contact landlords and agents directly, though working with a verified agent can help with paperwork and inspections."],
                    ["q" => "What documents should I ask for before paying?", "a" => "Always request proof of ownership, a valid tenancy or sale agreement, and verify the property in person before making any payment."],
                ];
                $tips = [
                    "Always inspect the property in person before making any payment.",
                    "Verify the landlord or agent's identity and ownership documents.",
                    "Avoid paying agency or caution fees before viewing the property.",
                    "Get all agreements in writing before moving in or completing a sale.",
                ];
                break;

            case 'service':
                $title = "Find {$name} in {$location} | {$site}";
                $desc  = "Need {$name}? Connect with trusted professionals in {$location} on {$site}. Compare profiles, read reviews, and hire the best.";
                $h1    = "Find {$name} in {$location}";
                $intro = "Connect with {$count} {$name} professionals in {$location} on {$site}. Compare profiles, read reviews and hire with confidence.";
                $faqs  = [
                    ["q" => "How much do {$name} services cost in {$location}?", "a" => "Rates vary by provider and job scope. Message a few professionals in {$location} above to compare quotes."],
                    ["q" => "How do I know a {$name} provider is trustworthy?", "a" => "Check their profile, reviews and verification status, and agree on scope and price in writing before work begins."],
                    ["q" => "Can I post a job instead of browsing profiles?", "a" => "Yes, you can post what you need and interested {$name} professionals in {$location} can reach out to you directly."],
                ];
                $tips = [
                    "Ask for references or past work examples before hiring.",
                    "Agree on price and scope of work in writing.",
                    "Avoid paying the full amount upfront — use milestone payments where possible.",
                    "Check reviews from previous customers in {$location}.",
                ];
                break;

            default: // product
                $title = "Buy {$name} Online in {$location} | {$site}";
                $desc  = "Looking for {$name}? Shop the widest selection of new and used {$name} on {$site}. Enjoy safe payments, verified sellers, and fast delivery to {$location}.";
                $h1    = "{$name} for Sale in {$location}";
                $intro = "Browse {$count} {$name} listings in {$location} on {$site}. Compare prices, connect directly with verified sellers, and enjoy safe transactions with Buyer Protection on every purchase.";
                $faqs  = [
                    ["q" => "How much does {$name} cost in {$location}?", "a" => "Prices vary by brand, condition and seller. Browse the current listings above to compare {$name} prices from sellers in {$location}."],
                    ["q" => "Is it safe to buy {$name} on {$site}?", "a" => "Yes. Look for verified seller badges, meet in safe public locations, and use Buyer Protection where available to keep your purchase secure."],
                    ["q" => "Can I sell my {$name} in {$location} for free?", "a" => "Yes, posting an ad on {$site} is free. Create an account and list your {$name} in minutes to reach buyers in {$location} and beyond."],
                ];
                $tips = [
                    "Inspect the item in person before paying, and test that it works as described.",
                    "Compare prices across multiple sellers in {$location} before committing.",
                    "Avoid sending money in advance for items you haven't seen — use Buyer Protection where possible.",
                    "Ask the seller for proof of ownership or purchase, especially for high-value items.",
                ];
                break;
        }

        return [
            'seoTitle'     => $title,
            'seoDesc'      => $desc,
            'seoCanonical' => $canonical,
            'seoH1'        => $h1,
            'seoIntro'     => $intro,
            'seoFaqs'      => $faqs,
            'seoTips'      => $tips,
        ];
    }

    /**
     * Generate SEO meta/content for a pure location page (all categories in
     * one state/LGA, no category filter). Resolves the slug to a readable
     * place name via the Lga/State lookup tables when possible.
     *
     * @return array{seoTitle: string, seoDesc: string, seoCanonical: string, seoH1: string, seoIntro: string, seoFaqs: array, seoTips: array}
     */
    private function buildLocationOnlySeoMeta(string $locationSlug, string $canonical, int $count = 0): array
    {
        $site = config('global.site_name');
        $displayLocation = $this->resolveLocationDisplayName($locationSlug);

        $title = "Buy and Sell in {$displayLocation} | {$site}";
        $desc  = "Discover {$count} active ads for cars, phones, property, jobs and more in {$displayLocation} on {$site} — Ethiopia's trusted classifieds platform. Post free ads and connect with sellers near you.";
        $h1    = "Ads For Sale in {$displayLocation}";
        $intro = "Browse {$count} active listings from sellers in {$displayLocation} on {$site}. Find great deals on vehicles, phones, electronics, property, and more — all in one place.";

        $faqs = [
            ["q" => "What can I buy in {$displayLocation} on {$site}?", "a" => "You'll find ads across categories including vehicles, phones & tablets, property, fashion, jobs and services from sellers based in {$displayLocation}."],
            ["q" => "Is {$site} free to use in {$displayLocation}?", "a" => "Yes, browsing and posting ads is completely free. Create an account to start buying or selling in {$displayLocation} today."],
            ["q" => "How do I stay safe when buying or selling in {$displayLocation}?", "a" => "Meet in public places, inspect items before paying, and use Buyer Protection on eligible purchases for extra peace of mind."],
        ];

        $tips = [
            "Meet sellers in a safe, public location whenever possible.",
            "Inspect items in person and test that they work before paying.",
            "Compare a few listings in {$displayLocation} before committing to a price.",
            "Avoid sending money in advance for items you haven't seen.",
        ];

        return [
            'seoTitle'     => $title,
            'seoDesc'      => $desc,
            'seoCanonical' => $canonical,
            'seoH1'        => $h1,
            'seoIntro'     => $intro,
            'seoFaqs'      => $faqs,
            'seoTips'      => $tips,
        ];
    }

    /**
     * Resolve a location slug (LGA or state) to a human-readable display name.
     * Falls back to a title-cased version of the slug when no match is found.
     */
    private function resolveLocationDisplayName(string $slug): string
    {
        $lga = Lga::where('slug', $slug)->with('state')->first();

        if ($lga) {
            return $lga->state ? "{$lga->name}, {$lga->state->name}" : $lga->name;
        }

        $state = State::where('slug', $slug)->first();

        if ($state) {
            return $state->name;
        }

        return ucwords(str_replace('-', ' ', $slug));
    }

    /**
     * Decide the robots directive for a listing page based on how much active
     * inventory it has. Thin pages are noindexed to avoid diluting crawl budget.
     *
     * @param  int $activeListingCount
     * @return string
     */
    private function seoRobotsForCount(int $activeListingCount): string
    {
        return $activeListingCount < 5 ? 'noindex, follow' : 'index, follow';
    }
}
