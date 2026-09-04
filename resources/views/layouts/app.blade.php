<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ filemtime(public_path('favicon-32x32.png')) }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-32x32.png') }}?v={{ filemtime(public_path('favicon-32x32.png')) }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ filemtime(public_path('favicon.png')) }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v={{ filemtime(public_path('apple-touch-icon.png')) }}">

    <title>{{ config('app.name', 'Exam Topics Base') }} - Dashboard</title>

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
    </style>
    @yield('styles')
</head>
<body class="h-full antialiased font-sans bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" x-data="{ sidebarOpen: false }">
    <div class="min-h-full">
        <!-- Sidebar Navigation (Desktop) -->
        <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
            <div class="flex flex-col flex-grow bg-navy pt-5 overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-4 mb-8">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'ExamTopicsBase') }}" class="h-10 w-auto max-w-[200px] object-contain drop-shadow-[0_2px_10px_rgba(0,212,170,0.2)]">
                    </a>
                </div>
                <div class="flex-grow flex flex-col">
                    <nav class="flex-1 px-2 space-y-1 pb-4">
                        @php
                            $route = request()->url();
                            $isActive = function($path) use ($route) {
                                return str_contains($route, $path) ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : 'text-gray-300 hover:bg-gray-800 hover:text-white';
                            };
                        @endphp
                        
                        <a href="{{ url('/dashboard') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition {{ request()->is('dashboard') ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Overview
                        </a>

                        <a href="{{ url('/dashboard/my-exams') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition {{ $isActive('/dashboard/my-exams') }}">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            My Purchased Guides
                        </a>

                        <a href="{{ url('/dashboard/test-engine') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition {{ $isActive('/dashboard/test-engine') }}">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Test Engine
                        </a>

                        <a href="{{ url('/dashboard/orders') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition {{ $isActive('/dashboard/orders') }}">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Billing & Orders
                        </a>

                        <a href="{{ url('/dashboard/profile') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition {{ $isActive('/dashboard/profile') }}">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile Settings
                        </a>
                    </nav>
                </div>
                <div class="flex-shrink-0 flex bg-gray-800 p-4 border-t border-gray-700">
                    <div class="flex items-center">
                        <img class="h-9 w-9 rounded-full border border-cyan" src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&color=00D4AA&background=0A1628' }}" alt="">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-cyan">Sign Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Body -->
        <div class="md:pl-64 flex flex-col flex-1">
            <!-- Top bar -->
            <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white dark:bg-gray-800 shadow">
                <button type="button" @click="sidebarOpen = true" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-cyan md:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        <span class="text-lg font-semibold md:hidden">Exams<span class="text-cyan">Ninja</span></span>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6 space-x-4">
                        <a href="{{ url('/') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:hover:text-white transition">Visit Site</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ url('/admin') }}" class="bg-orange text-white px-3 py-1.5 rounded-md text-xs font-bold transition hover:bg-opacity-90">Admin Panel</a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Page Main Content Area -->
            <main class="flex-1">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                        @if (session('status'))
                            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                                <p class="text-sm">{{ session('status') }}</p>
                            </div>
                        @endif
                        {{ $slot ?? '' }}
                        @yield('content')
                    </div>
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
                    <a href="{{ url('/') }}" class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'ExamTopicsBase') }}" class="h-8 w-auto object-contain">
                    </a>
                </div>
                <div class="mt-5 flex-1 h-0 overflow-y-auto">
                    <nav class="px-2 space-y-1">
                        <a href="{{ url('/dashboard') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ request()->is('dashboard') ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : '' }}">Overview</a>
                        <a href="{{ url('/dashboard/my-exams') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ str_contains(request()->url(), '/dashboard/my-exams') ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : '' }}">My Purchased Guides</a>
                        <a href="{{ url('/dashboard/test-engine') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ str_contains(request()->url(), '/dashboard/test-engine') ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : '' }}">Test Engine</a>
                        <a href="{{ url('/dashboard/orders') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ str_contains(request()->url(), '/dashboard/orders') ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : '' }}">Billing & Orders</a>
                        <a href="{{ url('/dashboard/profile') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white {{ str_contains(request()->url(), '/dashboard/profile') ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : '' }}">Profile Settings</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    @yield('scripts')
</body>
</html>
