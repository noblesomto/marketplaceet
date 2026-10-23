<!-- Sidebar Container -->
<aside class="w-full max-w-sm bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h3 class="text-gray-900 font-bold text-lg tracking-tight">Categories</h3>
        <a class="text-emerald-600 hover:text-emerald-700 text-xs font-bold uppercase tracking-wider transition-colors" href="/all-categories">
            View All
        </a>
    </div>

    <!-- Navigation List -->
    <nav class="p-2" aria-label="Sidebar Navigation">
        @foreach ($categories as $category)
            <div class="mb-1 group">
                <!-- Main Category -->
                <a href="{{ url('/category/' . $category->category_slug) }}"
                   class="flex items-center px-3 py-2.5 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition-all duration-200 group-active:scale-[0.98]">
                    <span class="font-semibold text-[15px] flex-1">{{ $category->category }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                @php
                    $subCategories = $category->subCategories;
                    $limit = 2;
                @endphp

                <!-- Subcategories List -->
                <ul class="mt-1 space-y-1" id="subcat-{{ $category->id }}">
                    @foreach ($subCategories->take($limit) as $subCategory)
                        <li class="ml-4 pl-3 border-l border-gray-200">
                            <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}"
                               class="block py-1.5 text-sm text-gray-500 hover:text-emerald-600 transition-colors font-bold">
                                {{ $subCategory->sub_category }}
                            </a>
                        </li>
                    @endforeach

                    @if ($subCategories->count() > $limit)
                        <!-- Hidden items for SEO (they are in DOM but hidden visually) -->
                        @foreach ($subCategories->slice($limit) as $subCategory)
                            <li class="ml-4 pl-3 border-l border-gray-200 hidden extra-{{ $category->id }}">
                                <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}"
                                   class="block py-1.5 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
                                    {{ $subCategory->sub_category }}
                                </a>
                            </li>
                        @endforeach

                        <!-- Professional Show More Toggle -->
                        <li class="ml-7 mt-1">
                            <button
                                onclick="toggleExtra('{{ $category->id }}', this)"
                                class="flex items-center gap-1 text-xs font-medium text-gray-400 hover:text-emerald-600 transition-colors focus:outline-none"
                            >
                                <span class="btn-text">Show more</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transform transition-transform icon-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </li>
                    @endif
                </ul>
            </div>
        @endforeach
    </nav>
</aside>

<script>
    function toggleExtra(categoryId, button) {
        const items = document.querySelectorAll('.extra-' + categoryId);
        const btnText = button.querySelector('.btn-text');
        const icon = button.querySelector('.icon-chevron');

        const isHidden = items[0]?.classList.contains('hidden');

        items.forEach(item => {
            item.classList.toggle('hidden');
            // Adding a small fade-in animation
            if (isHidden) {
                item.classList.add('animate-fadeIn');
            }
        });

        // Update Text and Rotate Icon
        if (isHidden) {
            btnText.innerText = 'Show less';
            icon.classList.add('rotate-180');
        } else {
            btnText.innerText = 'Show more';
            icon.classList.remove('rotate-180');
        }
    }
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>
