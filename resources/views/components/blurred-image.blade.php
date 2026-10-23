{{--
    Fills its box without stretching or cropping the source image.
    A blurred copy of the same image sits behind it at low opacity over a
    white surface, so narrow/tall uploads don't leave hard edges or get
    distorted by object-cover. Kept subtle by opacity alone (no grayscale/
    brightness tweaks) so the backdrop reads as a soft wash of the image's
    own colors rather than a stylized filter.

    Usage: <x-blurred-image src="..." alt="..." wrapperClass="w-full h-[200px] rounded-t-lg" loading="lazy" .../>
    Any extra attributes (loading, decoding, fetchpriority, srcset, sizes, onclick, onerror, id, width, height, class)
    are forwarded to the visible foreground <img>.
--}}
@props([
    'src',
    'alt' => '',
    'wrapperClass' => 'w-full h-full',
])

<div class="relative overflow-hidden {{ $wrapperClass }} bg-white">
    <div
        class="absolute -top-[5%] -left-[5%] w-[110%] h-[110%] bg-center bg-cover blur-[13px] opacity-40"
        style="background-image:url('{{ $src }}')"
        aria-hidden="true"
    ></div>

    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        {{ $attributes->merge(['class' => 'relative z-10 w-full h-full object-contain']) }}
    />
</div>
