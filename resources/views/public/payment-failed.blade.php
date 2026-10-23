<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Failed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-red-50 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-lg p-10 text-center max-w-md">
        <div class="text-red-500">
            <svg class="mx-auto w-16 h-16 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Payment Failed</h2>
        <p class="text-gray-600 mb-6">Oops! Something went wrong. Please try again or contact support.</p>
        <a href="/" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-full">
            Try Again
        </a>
    </div>
</body>
</html>
