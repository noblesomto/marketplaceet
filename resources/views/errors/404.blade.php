<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | 404 Error</title>
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
            <h1 class="text-8xl font-bold text-gray-800 mb-4">404</h1>

            <!-- Error Title -->
            <h2 class="text-3xl font-semibold text-gray-800 mb-4">Page Not Found</h2>

            <!-- Error Description -->
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                We're sorry, but the page you are looking for doesn't exist or has been moved.
                Please check the URL or navigate back to our homepage.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/"
                   class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Return to Homepage
                </a>
                <a href="javascript:history.back()"
                   class="px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Go Back
                </a>
            </div>

            <!-- Additional Help -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-gray-500 text-sm">
                    If you believe this is an error, please
                    <a href="/contact-us" class="text-green-600 hover:text-green-800 font-medium">contact our support team</a>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
