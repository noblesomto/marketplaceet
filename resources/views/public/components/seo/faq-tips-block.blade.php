@if(!empty($seoTips) || !empty($seoFaqs))
<section class="w-full max-w-[95rem] mx-auto mt-4 px-1">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @if(!empty($seoTips))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-5">
            <h2 class="text-base font-bold text-gray-900 mb-3">Buying Tips</h2>
            <ul class="space-y-2">
                @foreach($seoTips as $tip)
                <li class="flex items-start gap-2 text-sm text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $tip }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!empty($seoFaqs))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-5">
            <h2 class="text-base font-bold text-gray-900 mb-3">Frequently Asked Questions</h2>
            <div class="divide-y divide-gray-100">
                @foreach($seoFaqs as $faq)
                <details class="group py-2">
                    <summary class="flex items-center justify-between cursor-pointer text-sm font-semibold text-gray-800 list-none">
                        {{ $faq['q'] }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $faq['a'] }}</p>
                </details>
                @endforeach
            </div>
        </div>

        @php
        $faqSchema = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => array_map(fn ($faq) => [
                "@type" => "Question",
                "name" => $faq['q'],
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => $faq['a'],
                ],
            ], $seoFaqs),
        ];
        @endphp
        <script type="application/ld+json">
        {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
        @endif
    </div>
</section>
@endif
