<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>API SmartRanks - Technical Test</title>
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('favicon/favicon.ico') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}" />
        <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}" />

        
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-indigo-950 text-gray-800 dark:text-gray-200">
        <div class="min-h-screen">
            <!-- Navigation -->
            <nav class="bg-white dark:bg-gray-800 shadow-lg">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">API SmartRanks</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="https://github.com/afmurillo97" target="_blank" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <i class="fab fa-github text-2xl"></i>
                            </a>
                            <a href="https://linkedin.com/in/felipe-murillov" target="_blank" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <i class="fab fa-linkedin text-2xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="text-center">
                    <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                        Welcome to API SmartRanks
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">
                        A powerful and efficient API for managing products and categories with Swagger documentation.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <a href="/api/documentation" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition duration-300">
                            View Documentation
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="bg-white dark:bg-gray-800 py-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-bold text-center mb-12">API Features</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="bg-indigo-50 dark:bg-gray-700 p-6 rounded-lg">
                            <i class="fas fa-box text-4xl text-indigo-600 dark:text-indigo-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Product Management</h3>
                            <p class="text-gray-600 dark:text-gray-300">Efficiently manage your product catalog with our comprehensive API endpoints.</p>
                        </div>
                        <div class="bg-indigo-50 dark:bg-gray-700 p-6 rounded-lg">
                            <i class="fas fa-tags text-4xl text-indigo-600 dark:text-indigo-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Category Organization</h3>
                            <p class="text-gray-600 dark:text-gray-300">Organize products with a flexible category system.</p>
                        </div>
                        <div class="bg-indigo-50 dark:bg-gray-700 p-6 rounded-lg">
                            <i class="fas fa-shield-alt text-4xl text-indigo-600 dark:text-indigo-400 mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Secure Authentication</h3>
                            <p class="text-gray-600 dark:text-gray-300">Protected endpoints with robust authentication system.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Preview Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 products">
                <h2 class="text-3xl font-bold text-center mb-12">Featured Products</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($products as $product)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">{{ $product->name }}</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">{{ Str::limit($product->description, 100) }}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-indigo-600 dark:text-indigo-400 font-bold">${{ number_format($product->sale_price, 2) }}</span>
                                <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 px-3 py-1 rounded-full text-sm">
                                    {{ $product->category->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Categories Section -->
            <div class="bg-indigo-50 dark:bg-gray-800 py-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-bold text-center mb-12">Product Categories</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach($categories as $category)
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg text-center hover:shadow-lg transition duration-300">
                            <i class="fas fa-folder text-4xl text-indigo-600 dark:text-indigo-400 mb-4"></i>
                            <h3 class="text-lg font-semibold mb-2">{{ $category->name }}</h3>
                            <p class="text-gray-600 dark:text-gray-300">{{ $category->products_count }} Products</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-800 py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center">
                        <div class="text-gray-600 dark:text-gray-300">
                            © 2025 Andres Felipe Murillo. All rights reserved.
                        </div>
                        <div class="flex space-x-6">
                            <a href="https://github.com/afmurillo97" target="_blank" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <i class="fab fa-github text-2xl"></i>
                            </a>
                            <a href="https://linkedin.com/in/felipe-murillov" target="_blank" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                <i class="fab fa-linkedin text-2xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
