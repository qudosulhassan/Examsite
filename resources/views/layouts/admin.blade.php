<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ExamsNinja Admin Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">

    <!-- CSS / JS (Tailwind + Alpine) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])



    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Sora', sans-serif;
        }
        .bg-navy { background-color: #0A1628; }
        .text-cyan { color: #00D4AA; }
        .bg-cyan { background-color: #00D4AA; }
        .text-orange { color: #FF6B35; }
        .bg-orange { background-color: #FF6B35; }
        [x-cloak] { display: none !important; }
    </style>
    @yield('styles')
</head>
<body class="h-full antialiased font-sans bg-gray-100 text-gray-900" x-data="{ sidebarOpen: false }">
    <div class="min-h-full">
        <!-- Sidebar Navigation (Desktop) -->
        <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
            <div class="flex flex-col flex-grow bg-navy pt-5 overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-4 mb-6">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-orange" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" />
                        </svg>
                        <span class="text-xl font-bold tracking-tight text-white">Ninja<span class="text-orange">Admin</span></span>
                    </a>
                </div>
                <div class="flex-grow flex flex-col">
                    <nav class="flex-1 px-2 space-y-1 pb-4">
                        @php
                            $route = request()->url();
                            $isActive = function($path) use ($route) {
                                return str_contains($route, $path) ? 'bg-gray-800 text-orange border-l-4 border-orange' : 'text-gray-300 hover:bg-gray-800 hover:text-white';
                            };
                        @endphp
                        
                        <a href="{{ url('/admin') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ request()->is('admin') ? 'bg-gray-800 text-orange border-l-4 border-orange' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            Dashboard
                        </a>

                        <a href="{{ url('/admin/vendors') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/vendors') }}">
                            Vendors
                        </a>

                        <a href="{{ url('/admin/exams') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/exams') }}">
                            Exams
                        </a>

                        <!-- Questions Submenu -->
                        <div x-data="{ questionsOpen: {{ request()->is('admin/questions*') ? 'true' : 'false' }} }">
                            <button type="button" @click="questionsOpen = !questionsOpen" class="w-full group flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/questions') }}">
                                <span>Questions</span>
                                <svg class="h-4 w-4 transform transition-transform" :class="questionsOpen ? 'rotate-180 text-orange' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="questionsOpen" class="pl-3 pr-1 py-1 space-y-1">
                                <a href="{{ route('admin.questions.index') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.questions.index') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    All Questions
                                </a>
                                <a href="{{ route('admin.questions.create') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.questions.create') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    + Add Question
                                </a>
                                <a href="{{ route('admin.questions.import-form') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.questions.import-form') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Import JSON
                                </a>
                                <a href="{{ route('admin.questions.import-pdf-form') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.questions.import-pdf*') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Import PDF
                                </a>
                                <a href="{{ route('admin.questions.import-history') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.questions.import-history') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Import History
                                </a>
                            </div>
                        </div>

                        <a href="{{ url('/admin/users') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/users') }}">
                            Users
                        </a>

                        <a href="{{ url('/admin/orders') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/orders') }}">
                            Orders
                        </a>

                        <a href="{{ url('/admin/subscriptions') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/subscriptions') }}">
                            Subscriptions
                        </a>

                        <a href="{{ url('/admin/packages') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/packages') }}">
                            Vendor Bundles
                        </a>

                        <a href="{{ url('/admin/coupons') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/coupons') }}">
                            Coupons
                        </a>

                        <a href="{{ url('/admin/blog') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ request()->is('admin/blog*') && !request()->is('admin/blog-comments*') && !request()->is('admin/blog-subscribers*') ? 'bg-gray-800 text-orange border-l-4 border-orange' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            Blog Posts
                        </a>

                        <a href="{{ url('/admin/blog-comments') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/blog-comments') }}">
                            Blog Comments
                        </a>

                        <a href="{{ url('/admin/blog-subscribers') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/blog-subscribers') }}">
                            Blog Subscribers
                        </a>

                        <a href="{{ url('/admin/media') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/media') }}">
                            Media Gallery
                        </a>

                        <a href="{{ url('/admin/settings') }}" class="group flex items-center px-4 py-2.5 text-sm font-medium rounded-md transition {{ $isActive('/admin/settings') }}">
                            Settings
                        </a>
                    </nav>
                </div>
                <div class="flex-shrink-0 flex bg-gray-850 p-4 border-t border-gray-800">
                    <div class="flex items-center">
                        <img class="h-9 w-9 rounded-full border border-orange" src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&color=FF6B35&background=0A1628' }}" alt="">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-orange">Sign Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Body -->
        <div class="md:pl-64 flex flex-col flex-1">
            <!-- Top bar -->
            <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white shadow">
                <button type="button" @click="sidebarOpen = true" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-orange md:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        <h2 class="text-lg font-bold text-gray-800">Administrator Console</h2>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6 space-x-4">
                        <a href="{{ url('/') }}" class="text-sm font-medium text-gray-600 hover:text-navy transition">View Live Website</a>
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-navy transition">User Dashboard</a>
                    </div>
                </div>
            </div>

            <!-- Page Main Content Area -->
            <main class="flex-1 py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                            <p class="text-sm">{{ session('error') }}</p>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Backdrop & Menu -->
    <div x-show="sidebarOpen" class="relative z-50 md:hidden" role="dialog" aria-modal="true" style="display: none;">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"></div>
        <div class="fixed inset-0 flex z-40">
            <div class="relative flex-1 flex flex-col max-w-xs w-full bg-navy pt-5 pb-4">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button type="button" @click="sidebarOpen = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-shrink-0 flex items-center px-4">
                    <span class="text-xl font-bold tracking-tight text-white font-sora">Ninja<span class="text-orange">Admin</span></span>
                </div>
                <div class="mt-5 flex-1 h-0 overflow-y-auto">
                    <nav class="px-2 space-y-1">
                        <a href="{{ url('/admin') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Dashboard</a>
                        <a href="{{ url('/admin/vendors') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Vendors</a>
                        <a href="{{ url('/admin/exams') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Exams</a>
                        <a href="{{ url('/admin/questions') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Questions</a>
                        <div class="pl-6 space-y-1">
                            <a href="{{ route('admin.questions.create') }}" class="block text-xs text-gray-400 hover:text-white">+ Add Question</a>
                            <a href="{{ route('admin.questions.import-form') }}" class="block text-xs text-gray-400 hover:text-white">Import JSON</a>
                            <a href="{{ route('admin.questions.import-pdf-form') }}" class="block text-xs text-gray-400 hover:text-white">Import PDF</a>
                            <a href="{{ route('admin.questions.import-history') }}" class="block text-xs text-gray-400 hover:text-white">Import History</a>
                        </div>
                        <a href="{{ url('/admin/users') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Users</a>
                        <a href="{{ url('/admin/orders') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Orders</a>
                        <a href="{{ url('/admin/subscriptions') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Subscriptions</a>
                        <a href="{{ url('/admin/coupons') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Coupons</a>
                        <a href="{{ url('/admin/blog') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Blog Posts</a>
                        
                        <!-- Vendors -->
                        <a href="{{ route('admin.vendors.index') }}" class="{{ request()->routeIs('admin.vendors.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            Vendors
                        </a>

                        <!-- Certifications -->
                        <a href="{{ route('admin.certifications.index') }}" class="{{ request()->routeIs('admin.certifications.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                            Certifications
                        </a>

                        <a href="{{ url('/admin/media') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Media Gallery</a>
                        <a href="{{ url('/admin/settings') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Settings</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    @yield('scripts')
</body>
</html>
