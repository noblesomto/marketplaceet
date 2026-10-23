@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Ad Image Settings</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Ad Image Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @if(session('status'))
                    <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show">
                        {{ session('status')['text'] }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="bi bi-images me-2"></i>Ad Image Settings
                        </h5>
                    </div>
                    <div class="card-body pt-4">
                        <form action="/settings/ad-images" method="POST">
                            @csrf

                            {{-- Max Images --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Maximum Images Per Ad</label>
                                <p class="text-muted small mb-2">
                                    The most images a seller can upload for a single advert. Must be between 1 and 20.
                                </p>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="number"
                                           name="max_images"
                                           id="max_images"
                                           class="form-control @error('max_images') is-invalid @enderror"
                                           style="width: 120px;"
                                           value="{{ old('max_images', $settings['max_images']) }}"
                                           min="1" max="20">
                                    <span class="text-muted">images</span>
                                </div>
                                @error('max_images')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Min Images --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Minimum Images Required</label>
                                <p class="text-muted small mb-2">
                                    The fewest images a seller must upload before they can submit an ad.
                                </p>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="number"
                                           name="min_images"
                                           id="min_images"
                                           class="form-control @error('min_images') is-invalid @enderror"
                                           style="width: 120px;"
                                           value="{{ old('min_images', $settings['min_images']) }}"
                                           min="1" max="20">
                                    <span class="text-muted">images</span>
                                </div>
                                @error('min_images')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Dimensions --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Minimum Resolution</label>
                                <p class="text-muted small mb-2">
                                    Images narrower or shorter than this are rejected outright.
                                </p>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" name="image_min_width" id="image_min_width"
                                           class="form-control @error('image_min_width') is-invalid @enderror"
                                           style="width: 120px;"
                                           value="{{ old('image_min_width', $settings['image_min_width']) }}"
                                           min="100" max="10000">
                                    <span class="text-muted">×</span>
                                    <input type="number" name="image_min_height" id="image_min_height"
                                           class="form-control @error('image_min_height') is-invalid @enderror"
                                           style="width: 120px;"
                                           value="{{ old('image_min_height', $settings['image_min_height']) }}"
                                           min="100" max="10000">
                                    <span class="text-muted">px</span>
                                </div>
                                @error('image_min_width') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('image_min_height') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Recommended Resolution</label>
                                <p class="text-muted small mb-2">
                                    Images below this (but above the minimum) are accepted with a quality warning.
                                </p>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" name="image_recommended_width" id="image_recommended_width"
                                           class="form-control @error('image_recommended_width') is-invalid @enderror"
                                           style="width: 120px;"
                                           value="{{ old('image_recommended_width', $settings['image_recommended_width']) }}"
                                           min="100" max="10000">
                                    <span class="text-muted">×</span>
                                    <input type="number" name="image_recommended_height" id="image_recommended_height"
                                           class="form-control @error('image_recommended_height') is-invalid @enderror"
                                           style="width: 120px;"
                                           value="{{ old('image_recommended_height', $settings['image_recommended_height']) }}"
                                           min="100" max="10000">
                                    <span class="text-muted">px</span>
                                </div>
                                @error('image_recommended_width') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('image_recommended_height') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- File size --}}
                            <div class="mb-5">
                                <label class="form-label fw-semibold">File Size Limits</label>
                                <p class="text-muted small mb-2">
                                    Files outside this range are rejected — too small usually means a corrupted or low-quality image, too large slows down uploads.
                                </p>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" name="image_min_file_size_kb" id="image_min_file_size_kb"
                                           class="form-control @error('image_min_file_size_kb') is-invalid @enderror"
                                           style="width: 120px;"
                                           value="{{ old('image_min_file_size_kb', $settings['image_min_file_size_kb']) }}"
                                           min="1">
                                    <span class="text-muted">KB &mdash; &nbsp;min</span>
                                    <input type="number" name="image_max_file_size_mb" id="image_max_file_size_mb"
                                           class="form-control @error('image_max_file_size_mb') is-invalid @enderror"
                                           style="width: 120px;"
                                           value="{{ old('image_max_file_size_mb', $settings['image_max_file_size_mb']) }}"
                                           min="1" max="50">
                                    <span class="text-muted">MB &mdash; max</span>
                                </div>
                                @error('image_min_file_size_kb') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('image_max_file_size_mb') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Strictness --}}
                            <div class="mb-5">
                                <label class="form-label fw-semibold">
                                    Image Quality Strictness &mdash; <span id="strictness-value" class="text-primary">{{ $settings['image_strictness'] }}/10</span>
                                </label>
                                <p class="text-muted small mb-2">
                                    Controls how strictly uploaded images are assessed for quality. Higher values reject more images.
                                </p>
                                <input type="range"
                                       name="image_strictness"
                                       id="image_strictness"
                                       class="form-range"
                                       min="1" max="10" step="1"
                                       value="{{ old('image_strictness', $settings['image_strictness']) }}">

                                <div class="d-flex justify-content-between text-muted small mt-1 px-1">
                                    <span>1 — Accept all</span>
                                    <span>5 — Moderate</span>
                                    <span>10 — Very strict</span>
                                </div>

                                {{-- Strictness level descriptions --}}
                                <div class="mt-3 p-3 rounded bg-light border" id="strictness-description"
                                     data-min-res="{{ $settings['image_min_width'] }}×{{ $settings['image_min_height'] }}px">
                                    @php
                                        $level = $settings['image_strictness'];
                                        $minRes = $settings['image_min_width'] . '×' . $settings['image_min_height'] . 'px';
                                        if ($level <= 1)       $desc = ['label' => 'Accept All', 'color' => 'success', 'text' => 'No quality checks run. Any image file is accepted.'];
                                        elseif ($level <= 2)   $desc = ['label' => 'Minimal', 'color' => 'success', 'text' => "Only checks file size ({$settings['image_min_file_size_kb']}KB – {$settings['image_max_file_size_mb']}MB). No resolution or quality scoring."];
                                        elseif ($level <= 4)   $desc = ['label' => 'Basic', 'color' => 'info', 'text' => "Checks file size and minimum resolution ({$minRes})."];
                                        elseif ($level <= 6)   $desc = ['label' => 'Moderate', 'color' => 'info', 'text' => 'File size, minimum resolution, and overall quality score (min 40/100).'];
                                        elseif ($level <= 8)   $desc = ['label' => 'Strict', 'color' => 'warning', 'text' => 'All of the above plus sharpness/blur detection. Blurry images are flagged.'];
                                        else                   $desc = ['label' => 'Very Strict', 'color' => 'danger', 'text' => 'Full validation: file size, resolution, quality score (min 60/100), sharpness, and brightness.'];
                                    @endphp
                                    <span class="badge bg-{{ $desc['color'] }} me-2">{{ $desc['label'] }}</span>
                                    <span class="small">{{ $desc['text'] }}</span>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i> Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Reference card --}}
                <div class="card mt-4 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Strictness Level Reference</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Level</th>
                                    <th>Label</th>
                                    <th>Checks Performed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>1</td><td><span class="badge bg-success">Accept All</span></td><td>None — all images pass</td></tr>
                                <tr><td>2</td><td><span class="badge bg-success">Minimal</span></td><td>File size only ({{ $settings['image_min_file_size_kb'] }}KB–{{ $settings['image_max_file_size_mb'] }}MB)</td></tr>
                                <tr><td>3–4</td><td><span class="badge bg-info text-dark">Basic</span></td><td>File size + minimum resolution ({{ $settings['image_min_width'] }}×{{ $settings['image_min_height'] }}px)</td></tr>
                                <tr><td>5–6</td><td><span class="badge bg-info text-dark">Moderate</span></td><td>Above + quality score ≥ 40/100</td></tr>
                                <tr><td>7–8</td><td><span class="badge bg-warning text-dark">Strict</span></td><td>Above + sharpness / blur detection</td></tr>
                                <tr><td>9–10</td><td><span class="badge bg-danger">Very Strict</span></td><td>Above + brightness check + quality score ≥ 60/100</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<script>
(function () {
    const slider = document.getElementById('image_strictness');
    const valueDisplay = document.getElementById('strictness-value');
    const descBox = document.getElementById('strictness-description');

    const minFileKb = {{ $settings['image_min_file_size_kb'] }};
    const maxFileMb = {{ $settings['image_max_file_size_mb'] }};
    const minRes = '{{ $settings['image_min_width'] }}×{{ $settings['image_min_height'] }}px';

    const levels = {
        1:  { label: 'Accept All',  color: 'success', text: 'No quality checks run. Any image file is accepted.' },
        2:  { label: 'Minimal',     color: 'success', text: `Only checks file size (${minFileKb}KB – ${maxFileMb}MB). No resolution or quality scoring.` },
        3:  { label: 'Basic',       color: 'info',    text: `Checks file size and minimum resolution (${minRes}).` },
        4:  { label: 'Basic',       color: 'info',    text: `Checks file size and minimum resolution (${minRes}).` },
        5:  { label: 'Moderate',    color: 'info',    text: 'File size, minimum resolution, and overall quality score (min 40/100).' },
        6:  { label: 'Moderate',    color: 'info',    text: 'File size, minimum resolution, and overall quality score (min 40/100).' },
        7:  { label: 'Strict',      color: 'warning', text: 'All of the above plus sharpness/blur detection. Blurry images are flagged.' },
        8:  { label: 'Strict',      color: 'warning', text: 'All of the above plus sharpness/blur detection. Blurry images are flagged.' },
        9:  { label: 'Very Strict', color: 'danger',  text: 'Full validation: file size, resolution, quality score (min 60/100), sharpness, and brightness.' },
        10: { label: 'Very Strict', color: 'danger',  text: 'Full validation: file size, resolution, quality score (min 60/100), sharpness, and brightness.' },
    };

    slider.addEventListener('input', function () {
        const val = parseInt(this.value);
        valueDisplay.textContent = val + '/10';
        const info = levels[val];
        descBox.innerHTML = `<span class="badge bg-${info.color} me-2">${info.label}</span><span class="small">${info.text}</span>`;
    });
})();
</script>

@include('admin.layouts.footer')
