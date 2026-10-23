<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">{{ config('global.site_name') }}</h1>
            <p class="text-gray-600">Admin Password Reset</p>
        </div>

        <!-- Forgot Password Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 text-center">
                <h2 class="text-2xl font-bold text-white">Forgot Password?</h2>
                <p class="text-purple-100 mt-2">Enter your email to reset your admin password</p>
            </div>

            <div class="p-8">
                @if(session('success'))
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                        <p class="text-green-700 text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <p class="text-red-700 text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.forgot.password') }}">
                    @csrf

                    <!-- Email Input -->
                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                            Admin Email Address
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="admin@example.com">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition duration-300 transform hover:scale-105 shadow-lg">
                        Send Reset Link
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <a href="{{ route('admin.login') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                        ← Back to Admin Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-4">
            <p class="text-gray-600 text-sm text-center">
                <strong>Note:</strong> The password reset link will be sent to your registered admin email address.
            </p>
        </div>
    </div>
</body>
</html>
