{{--
    Photo picker for the post/edit ad forms: one grid of square tiles, first
    photo is the cover, tap a photo to make it the cover or remove it, drag to
    reorder (touch too). Driven by dashboard/js/ad-photos.js.

    Tile markup lives in <template>s here (not in the JS) so Tailwind sees the
    classes when it builds.

    @param int   $minImages
    @param int   $maxImages
    @param array $savedPhotos  edit page: [['id' => media id, 'url' => thumb], …]
    @param array $tempPhotos   post page after a failed submit: session('temp_images')
--}}
@php
    $savedPhotos = $savedPhotos ?? [];
    $tempPhotos = $tempPhotos ?? [];
@endphp
<div id="adPhotos" data-min="{{ $minImages }}" data-max="{{ $maxImages }}" class="scroll-mt-24">
    <div class="flex items-baseline justify-between gap-3 mb-1">
        <label for="imageUpload" class="block text-sm font-semibold text-gray-700">Product Photos <span class="text-red-500">*</span></label>
        <span data-photo-count class="text-xs font-medium text-gray-500 whitespace-nowrap"></span>
    </div>
    <p class="text-xs text-gray-500 mb-3">Tip: bright, sharp photos sell faster. Show the front, back and inside. The first photo is your cover.</p>

    <div id="image-error" class="hidden mb-3 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm" role="alert">
        @error('images') {{ is_array($message) ? implode(' ', $message) : $message }} @enderror
        @error('deleted_images') {{ $message }} @enderror
    </div>

    @if (!empty($tempPhotos))
        <p class="mb-3 text-xs text-green-800 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
            Your {{ count($tempPhotos) }} photo{{ count($tempPhotos) === 1 ? '' : 's' }} from before {{ count($tempPhotos) === 1 ? 'is' : 'are' }} still here.
        </p>
    @endif

    <div id="photoGrid" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2 sm:gap-3">
        <label for="imageUpload" data-photo-add class="photo-add aspect-square flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 text-gray-500 cursor-pointer hover:border-dark_green hover:text-dark_green hover:bg-green-50 transition-colors select-none">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span class="text-xs font-semibold">Add photo</span>
        </label>
    </div>

    <p class="mt-2 text-xs text-gray-400">JPG, PNG or WebP · up to 20 MB each · drag or tap a photo to rearrange</p>

    <input id="imageUpload" name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="hidden">
    <div data-photo-inputs></div>
    <script type="application/json" data-photo-initial>{!! json_encode([
        'saved' => array_values($savedPhotos),
        'temp' => collect($tempPhotos)->map(fn ($t) => ['path' => $t['path'], 'url' => $t['url']])->values(),
    ]) !!}</script>

    <template data-tpl="tile">
        <div class="photo-tile relative aspect-square rounded-xl overflow-hidden bg-gray-100 border border-gray-200 cursor-grab active:cursor-grabbing select-none touch-manipulation">
            <img data-img alt="" class="w-full h-full object-cover pointer-events-none" draggable="false">
            <span data-cover class="hidden absolute left-1.5 bottom-1.5 rounded-md bg-dark_green/90 px-1.5 py-0.5 text-[11px] font-semibold text-white shadow">Cover</span>
            <button type="button" data-warn class="hidden absolute left-1.5 top-1.5 w-6 h-6 rounded-full bg-amber-400 text-white text-xs font-bold shadow flex items-center justify-center" aria-label="Photo quality tip">!</button>
            <div data-problem class="hidden absolute inset-x-0 bottom-0 bg-red-600/90 px-2 py-1 text-[11px] leading-tight font-medium text-white"></div>
            <div data-note class="hidden absolute inset-x-0 bottom-0 bg-gray-900/85 px-2 py-1.5 text-[11px] leading-tight text-white"></div>
            <button type="button" data-remove class="absolute right-1.5 top-1.5 w-8 h-8 rounded-full bg-white/95 text-gray-700 shadow-md flex items-center justify-center hover:bg-red-50 hover:text-red-600" aria-label="Remove photo">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
            <div data-menu class="hidden absolute inset-0 z-10 bg-black/55 flex flex-col items-center justify-center gap-2 p-2">
                <button type="button" data-make-cover class="w-full max-w-[8rem] rounded-lg bg-white px-2 py-1.5 text-xs font-semibold text-gray-900 shadow">Make cover</button>
                <button type="button" data-menu-remove class="w-full max-w-[8rem] rounded-lg bg-red-600 px-2 py-1.5 text-xs font-semibold text-white shadow">Remove</button>
            </div>
        </div>
    </template>
    <template data-tpl="loading">
        <div class="photo-loading aspect-square rounded-xl bg-gray-100 animate-pulse flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
        </div>
    </template>
</div>

<style>
    #photoGrid .sortable-ghost { opacity: .35; }
    #photoGrid .sortable-chosen { box-shadow: 0 0 0 3px #326916; }
    #photoGrid .photo-tile.has-problem::after { content: ''; position: absolute; inset: 0; border-radius: inherit; box-shadow: inset 0 0 0 3px #dc2626; pointer-events: none; }
</style>
