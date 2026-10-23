<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-lg p-10 text-center max-w-md">
        <div class="text-green-500">
            <svg class="mx-auto w-16 h-16 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold mb-2">Payment Successful!</h2>
        <p class="text-gray-600 mb-6">Thank you. Your payment was processed successfully.</p>
        <a href="/user/index" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-full">
            Go to Dashboard
        </a>
    </div>
</body>
</html>
