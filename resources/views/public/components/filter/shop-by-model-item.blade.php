@php $isCurrentModel = $currentModelId == $catModel->id; @endphp
<li>
    <a href="{{ $modelUrl($catModel->model_slug) }}"
       @if($isCurrentModel) aria-current="page" @endif
       class="group flex items-center justify-between p-2 rounded-md transition-all duration-200 {{ $isCurrentModel ? 'bg-green-50' : 'hover:bg-gray-50' }}">
        <span class="text-[14px] leading-tight font-bold transition-colors {{ $isCurrentModel ? 'text-secondary_dark' : 'text-gray-600 group-hover:text-secondary_dark' }}">
            {{ $catModel->model }}
        </span>
        <span class="text-[11px] font-medium transition-colors {{ $isCurrentModel ? 'text-secondary_dark' : 'text-gray-500 group-hover:text-secondary_dark' }}">
            ({{ $catModel->advert_count }})
        </span>
    </a>
</li>
