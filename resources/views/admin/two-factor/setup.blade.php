<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link href="{{ asset('frontend/images/favicon.png') }}" rel="icon">
    <link href="{{ asset('backend/assets/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { background: #f5f5f5; }
        .card { border-radius: 12px; }
        code { font-size: 1rem; letter-spacing: 2px; word-break: break-all; }
        .qr-wrap { display: inline-block; padding: 12px; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; }
        .qr-wrap svg { display: block; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock-fill text-primary" style="font-size:2.5rem;"></i>
                        <h4 class="mt-2 mb-1">Set Up Two-Factor Authentication</h4>
                        <p class="text-muted small">Scan the QR code with your authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code below to confirm.</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- QR Code — server-side SVG, inlined directly --}}
                    <div class="text-center mb-3">
                        <div class="qr-wrap d-inline-block">
                            {!! $qrCodeSvg !!}
                        </div>
                    </div>

                    {{-- Manual key --}}
                    <div class="mb-4 text-center">
                        <p class="small text-muted mb-1">Can't scan? Enter this key manually in your app:</p>
                        <code class="d-block bg-light border rounded px-3 py-2">{{ $secret }}</code>
                    </div>

                    <form action="{{ route('admin.2fa.confirm') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Verification Code</label>
                            <input type="text"
                                   name="code"
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                   placeholder="000000"
                                   maxlength="6"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   autofocus>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Confirm &amp; Enable 2FA</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('backend/assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
