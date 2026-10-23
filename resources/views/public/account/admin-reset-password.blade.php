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

        <!-- Reset Password Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 text-center">
                <h2 class="text-2xl font-bold text-white">Reset Password</h2>
                <p class="text-purple-100 mt-2">Create a new secure password for your admin account</p>
            </div>

            <div class="p-8">
                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <p class="text-red-700 text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.reset.password', ['admin_id' => $admin_id, 'token' => $token]) }}">
                    @csrf

                    <!-- Password Input -->
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                            New Password
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               minlength="8"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="Enter new password (minimum 8 characters)">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">
                            Confirm New Password
                        </label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               minlength="8"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="Confirm your new password">
                    </div>

                    <!-- Password Requirements -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Password Requirements:</p>
                        <ul class="text-xs text-gray-600 space-y-1">
                            <li>• Minimum 8 characters</li>
                            <li>• Include uppercase and lowercase letters</li>
                            <li>• Include at least one number</li>
                            <li>• Include at least one special character</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition duration-300 transform hover:scale-105 shadow-lg">
                        Reset Password
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

        <!-- Security Note -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-4">
            <p class="text-gray-600 text-sm text-center">
                <strong>Security Tip:</strong> Choose a strong password that you don't use for any other accounts.
            </p>
        </div>
    </div>

    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            const isLongEnough = password.length >= 8;

            // You can add visual feedback here if needed
        });

        // Confirm password match check
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
