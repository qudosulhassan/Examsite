<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    @php
        $adminFavicon = !empty($globalSettings['site_favicon']) ? asset($globalSettings['site_favicon']) : asset('favicon-32x32.png');
        $adminAppleIcon = !empty($globalSettings['apple_touch_icon']) ? asset($globalSettings['apple_touch_icon']) : asset('apple-touch-icon.png');
    @endphp
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $adminFavicon }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $adminFavicon }}">
    <link rel="shortcut icon" href="{{ $adminFavicon }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $adminAppleIcon }}">

    <title>Exam Topics Base Admin Portal</title>

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
                    <a href="{{ url('/') }}" class="flex items-center">
                        <img src="{{ !empty($globalSettings['site_logo']) ? asset($globalSettings['site_logo']) : asset('images/logo.png') }}" alt="{{ $globalSettings['site_name'] ?? config('app.name', 'ExamTopicsBase') }}" class="h-10 w-auto max-w-[200px] object-contain drop-shadow-[0_2px_10px_rgba(0,212,170,0.2)]">
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

                        <!-- Users & RBAC Submenu -->
                        <div x-data="{ usersOpen: {{ (request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/audit-logs*')) ? 'true' : 'false' }} }">
                            <button type="button" @click="usersOpen = !usersOpen" class="w-full group flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-md transition {{ (request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/audit-logs*')) ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                <span>Users</span>
                                <svg class="h-4 w-4 transform transition-transform" :class="usersOpen ? 'rotate-180 text-cyan' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="usersOpen" class="pl-3 pr-1 py-1 space-y-1">
                                <a href="{{ route('admin.users.index') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.users.index') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    All Users
                                </a>
                                <a href="{{ route('admin.users.create') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.users.create') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    + Add User
                                </a>
                                <a href="{{ route('admin.roles.index') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->is('admin/roles*') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Roles & Permissions
                                </a>
                                <a href="{{ route('admin.audit-logs.index') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.audit-logs.index') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Audit Logs
                                </a>
                            </div>
                        </div>

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

                        <!-- Blog CMS Submenu -->
                        <div x-data="{ blogOpen: {{ (request()->is('admin/blog*') || request()->is('admin/blog-categories*') || request()->is('admin/blog-tags*') || request()->is('admin/blog-comments*') || request()->is('admin/blog-subscribers*')) ? 'true' : 'false' }} }">
                            <button type="button" @click="blogOpen = !blogOpen" class="w-full group flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-md transition {{ (request()->is('admin/blog*') || request()->is('admin/blog-categories*') || request()->is('admin/blog-tags*') || request()->is('admin/blog-comments*') || request()->is('admin/blog-subscribers*')) ? 'bg-gray-800 text-cyan border-l-4 border-cyan' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                <div class="flex items-center space-x-2">
                                    <span>Blog CMS</span>
                                    @php
                                        $pendingCommentsCount = \App\Models\BlogComment::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingCommentsCount > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400">
                                            {{ $pendingCommentsCount }}
                                        </span>
                                    @endif
                                </div>
                                <svg class="h-4 w-4 transform transition-transform" :class="blogOpen ? 'rotate-180 text-cyan' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="blogOpen" class="pl-3 pr-1 py-1 space-y-1">
                                <a href="{{ route('admin.blog.dashboard') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.blog.dashboard') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Dashboard
                                </a>
                                <a href="{{ route('admin.blog.index') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.blog.index') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    All Posts
                                </a>
                                <a href="{{ route('admin.blog.create') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.blog.create') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    + New Post
                                </a>
                                <a href="{{ route('admin.blog-categories.index') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.blog-categories.*') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Categories
                                </a>
                                <a href="{{ route('admin.blog-tags.index') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.blog-tags.*') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Tags
                                </a>
                                <a href="{{ route('admin.blog-comments.index') }}" class="group flex items-center justify-between px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.blog-comments.*') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    <span>Comments</span>
                                    @if($pendingCommentsCount > 0)
                                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-500 text-slate-900">
                                            {{ $pendingCommentsCount }}
                                        </span>
                                    @endif
                                </a>
                                <a href="{{ route('admin.blog-subscribers.index') }}" class="group flex items-center px-3 py-1.5 text-xs font-medium rounded-md transition {{ request()->routeIs('admin.blog-subscribers.*') ? 'text-cyan font-bold bg-gray-800' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                                    Subscribers
                                </a>
                            </div>
                        </div>

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
            <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white shadow-sm border-b border-gray-150">
                <button type="button" @click="sidebarOpen = true" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-cyan md:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <div class="flex-1 px-4 sm:px-6 flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <h2 class="text-base sm:text-lg font-extrabold text-navy tracking-tight">ExamTopicsBase <span class="text-xs font-semibold text-gray-400 font-sans hidden sm:inline">| Admin Console</span></h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hidden md:inline-flex">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                            Live System
                        </span>
                    </div>
                    <div class="ml-4 flex items-center md:ml-6 space-x-3 sm:space-x-4">
                        <a href="{{ url('/') }}" target="_blank" class="text-xs font-bold text-gray-600 hover:text-navy transition flex items-center gap-1">
                            <span>Live Website</span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <a href="{{ url('/dashboard') }}" class="text-xs font-bold text-gray-600 hover:text-navy transition hidden sm:inline">User Dashboard</a>

                        <!-- Admin Profile Pill -->
                        <div class="flex items-center space-x-2 pl-2 border-l border-gray-200">
                            <div class="w-7 h-7 rounded-lg bg-navy/10 text-navy font-bold flex items-center justify-center text-xs uppercase">
                                {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                            </div>
                            <span class="text-xs font-bold text-navy hidden md:inline">{{ auth()->user()->name }}</span>
                        </div>
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
                    <a href="{{ url('/') }}" class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'ExamTopicsBase') }}" class="h-8 w-auto object-contain">
                    </a>
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
                        <div x-data="{ mUsersOpen: {{ (request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/audit-logs*')) ? 'true' : 'false' }} }">
                            <button type="button" @click="mUsersOpen = !mUsersOpen" class="w-full group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                                <span>Users</span>
                                <svg class="h-4 w-4 transform transition-transform" :class="mUsersOpen ? 'rotate-180 text-cyan' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="mUsersOpen" class="pl-6 space-y-1">
                                <a href="{{ route('admin.users.index') }}" class="block text-xs text-gray-400 hover:text-white">All Users</a>
                                <a href="{{ route('admin.users.create') }}" class="block text-xs text-gray-400 hover:text-white">+ Add User</a>
                                <a href="{{ route('admin.roles.index') }}" class="block text-xs text-gray-400 hover:text-white">Roles & Permissions</a>
                                <a href="{{ route('admin.audit-logs.index') }}" class="block text-xs text-gray-400 hover:text-white">Audit Logs</a>
                            </div>
                        </div>
                        <a href="{{ url('/admin/orders') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Orders</a>
                        <a href="{{ url('/admin/subscriptions') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Subscriptions</a>
                        <a href="{{ url('/admin/coupons') }}" class="group flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">Coupons</a>
                        <div x-data="{ mBlogOpen: {{ (request()->is('admin/blog*') || request()->is('admin/blog-categories*') || request()->is('admin/blog-tags*') || request()->is('admin/blog-comments*') || request()->is('admin/blog-subscribers*')) ? 'true' : 'false' }} }">
                            <button type="button" @click="mBlogOpen = !mBlogOpen" class="w-full group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-800 hover:text-white">
                                <span>Blog CMS</span>
                                <svg class="h-4 w-4 transform transition-transform" :class="mBlogOpen ? 'rotate-180 text-cyan' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="mBlogOpen" class="pl-6 space-y-1">
                                <a href="{{ route('admin.blog.dashboard') }}" class="block text-xs text-gray-400 hover:text-white">Dashboard</a>
                                <a href="{{ route('admin.blog.index') }}" class="block text-xs text-gray-400 hover:text-white">All Posts</a>
                                <a href="{{ route('admin.blog.create') }}" class="block text-xs text-gray-400 hover:text-white">+ New Post</a>
                                <a href="{{ route('admin.blog-categories.index') }}" class="block text-xs text-gray-400 hover:text-white">Categories</a>
                                <a href="{{ route('admin.blog-tags.index') }}" class="block text-xs text-gray-400 hover:text-white">Tags</a>
                                <a href="{{ route('admin.blog-comments.index') }}" class="block text-xs text-gray-400 hover:text-white">Comments</a>
                                <a href="{{ route('admin.blog-subscribers.index') }}" class="block text-xs text-gray-400 hover:text-white">Subscribers</a>
                            </div>
                        </div>
                        
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
    @stack('scripts')
</body>
</html>
