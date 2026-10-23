<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Unavailable | 503 Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
            <!-- Error Code -->
            <h1 class="text-8xl font-bold text-purple-600 mb-4">503</h1>

            <!-- Error Title -->
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">Service Unavailable</h2>

            <!-- Error Description -->
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                We're currently performing scheduled maintenance or experiencing high traffic.
                We'll be back shortly. Thank you for your patience.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="location.reload()"
                   class="px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    Refresh Page
                </button>
                <a href="/"
                   class="px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    Go to Homepage
                </a>
            </div>

            <!-- Additional Help -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-gray-500 text-sm">
                    For updates on our service status, please check our
                    <a href="/contact-us" class="text-purple-600 hover:text-purple-800 font-medium">status page</a>
                    or contact support.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
