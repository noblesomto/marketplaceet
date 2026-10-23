@php
    $flashTypes = [
        'success' => 'bg-green-100 text-green-900 border-green-400',
        'error'   => 'bg-red-100 text-red-900 border-red-400',
        'warning' => 'bg-yellow-100 text-yellow-900 border-yellow-400',
        'info'    => 'bg-blue-100 text-blue-900 border-blue-400',
    ];
@endphp

@foreach ($flashTypes as $type => $classes)
    @if (session($type))
        <div class="{{ $classes }} border px-4 py-3 rounded my-2">
            {{ session($type) }}
            @if (session('resend_email'))
                <a href="{{ route('activation.resend', ['email' => session('resend_email')]) }}"
                   class="text-blue-600 underline ml-1">
                    Resend activation email
                </a>
            @endif
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-900 px-4 py-3 rounded my-2">
        <h4 class="font-semibold mb-2">Please fix the following errors:</h4>
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
