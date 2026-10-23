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
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <div class="text-center mb-4">
                        <i class="bi bi-shield-check text-primary" style="font-size:2.5rem;"></i>
                        <h4 class="mt-2 mb-1">Two-Factor Verification</h4>
                        <p class="text-muted small">Enter the 6-digit code from your authenticator app to continue.</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('admin.2fa.verify.code') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Authentication Code</label>
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
                        <button type="submit" class="btn btn-primary w-100">Verify</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('admin.logout') }}" class="text-muted small">Sign out</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('backend/assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
