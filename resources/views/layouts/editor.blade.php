<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ExamsNinja Editor</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">

    <!-- CSS / JS (Tailwind + Alpine + TomSelect) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            overflow: hidden; /* Prevent body scroll, layout handles it */
        }
        
        /* Premium Editor Typography */
        .editor-title-input {
            font-family: 'Merriweather', serif;
            font-weight: 900;
        }
        
        .ck-editor__editable_inline {
            font-family: 'Merriweather', serif !important;
            font-size: 1.125rem !important;
            line-height: 1.8 !important;
            color: #374151 !important;
            padding: 2rem 0 !important;
        }

        /* Borderless CKEditor to look like Notion/Gutenberg */
        .ck.ck-editor {
            border: none !important;
            box-shadow: none !important;
        }
        .ck.ck-toolbar {
            border: none !important;
            background: transparent !important;
            border-bottom: 1px solid #f3f4f6 !important;
            padding: 10px 0 !important;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .ck-editor__main {
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
        }
        .ck.ck-editor__editable.ck-focused:not(.ck-editor__nested-editable) {
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* TomSelect overrides for Sidebar */
        .ts-control {
            border-color: #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 0.625rem 0.875rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .ts-control.focus {
            border-color: #00D4AA !important;
            box-shadow: 0 0 0 2px rgba(0, 212, 170, 0.2) !important;
        }
    </style>
    @yield('styles')
</head>
<body class="h-full bg-white text-gray-900" x-data="{ sidebarOpen: true }">
    <div class="h-full flex flex-col">
        <!-- Top Navigation Bar -->
        <header class="flex-shrink-0 h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 z-20">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.blog.index') }}" class="w-10 h-10 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 transition" title="Back to Dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div class="h-6 w-px bg-gray-200"></div>
                <div class="text-sm text-gray-400 font-medium flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                    Draft
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="w-10 h-10 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 transition" :class="sidebarOpen ? 'bg-gray-100' : ''" title="Toggle Settings">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </button>
                <button type="button" onclick="document.getElementById('postForm').submit()" class="bg-[#0A1628] hover:bg-opacity-90 text-white text-sm font-semibold py-2 px-6 rounded-md shadow-sm transition transform active:scale-95">
                    Save & Publish
                </button>
            </div>
        </header>

        <!-- Main Workspace -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Canvas (Editor) -->
            <main class="flex-1 overflow-y-auto bg-white relative">
                @if ($errors->any())
                    <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-2xl">
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-lg flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <h3 class="text-sm font-bold">Please correct the errors:</h3>
                                <ul class="list-disc list-inside text-xs mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="max-w-4xl mx-auto px-8 py-12 lg:px-16 lg:py-16">
                    @yield('editor_content')
                </div>
            </main>

            <!-- Right Sidebar (Settings) -->
            <aside x-show="sidebarOpen" 
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="translate-x-full"
                   x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="translate-x-0"
                   x-transition:leave-end="translate-x-full"
                   class="w-80 flex-shrink-0 bg-gray-50 border-l border-gray-200 overflow-y-auto">
                <div class="p-6">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6">Post Settings</h2>
                    @yield('editor_sidebar')
                </div>
            </aside>
        </div>
    </div>
    
    @yield('scripts')
</body>
</html>
