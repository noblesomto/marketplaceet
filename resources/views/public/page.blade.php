<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Chat Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

</head>
<body class="bg-gray-50">
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t flex justify-around items-center py-2">

    <!-- Search -->
    <button class="flex flex-col items-center text-gray-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
        <span class="text-xs mt-1">Search</span>
    </button>

    <!-- Favourites -->
    <button class="flex flex-col items-center text-gray-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5
                   4.5 0 116.364 6.364L12 21.364 4.318 12.682a4.5 4.5
                   0 010-6.364z" />
        </svg>
        <span class="text-xs mt-1">Favourites</span>
    </button>

    <!-- CENTER BUTTON -->
    <button
        class="relative -mt-10 w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center shadow-xl">
        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902
                   0l1.07 3.292a1 1 0 00.95.69h3.462c.969
                   0 1.371 1.24.588 1.81l-2.8 2.034a1
                   1 0 00-.364 1.118l1.07 3.292c.3.921-.755
                   1.688-1.54 1.118l-2.8-2.034a1 1 0
                   00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1
                   1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1
                   1 0 00.951-.69l1.07-3.292z" />
        </svg>
    </button>

    <!-- Sell -->
    <button class="flex flex-col items-center text-gray-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 4v16m8-8H4" />
        </svg>
        <span class="text-xs mt-1">Sell</span>
    </button>

    <!-- Me -->
    <button class="flex flex-col items-center text-gray-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M5.121 17.804A4 4 0 019 15h6a4
                   4 0 013.879 2.804M15 10a3 3 0
                   11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="text-xs mt-1">Me</span>
    </button>

</div>

</body>
</html>
