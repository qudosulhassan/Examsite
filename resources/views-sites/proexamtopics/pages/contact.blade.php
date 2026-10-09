@extends('layouts.public')

@section('title', 'Contact Support - ' . ($currentSite->name ?? ($globalSettings['site_name'] ?? config('app.name', 'ProExamTopics'))))

@section('content')
<!-- Premium Support Hero -->
<x-site-b.hero variant="contact" glowColor="blue" padding="compact">
    <div class="relative max-w-4xl mx-auto text-center">
        <!-- Floating Message Icon 1 (Left) -->
        <div class="cpm-float hidden md:flex absolute -left-10 top-6 items-center gap-2 px-3.5 py-2 rounded-2xl bg-slate-900/90 border border-slate-700/80 shadow-xl backdrop-blur-md">
            <span class="w-2.5 h-2.5 rounded-full bg-[#00D4AA] animate-ping"></span>
            <span class="text-xs text-white font-mono-code font-bold">24/7 Live Queue</span>
        </div>

        <!-- Floating Message Icon 2 (Right) -->
        <div class="cpm-float-reverse hidden md:flex absolute -right-10 top-8 items-center gap-2 px-3.5 py-2 rounded-2xl bg-slate-900/90 border border-slate-700/80 shadow-xl backdrop-blur-md">
            <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="text-xs text-white">4-6h Avg Reply</span>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-4 backdrop-blur-sm shadow-sm">
            <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
            <span class="font-mono-code text-[#00D4AA] font-bold">SUPPORT CONCIERGE</span>
            <span class="text-slate-500">|</span>
            <span>Always Online</span>
        </div>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-3">
            We're Here to <span class="bg-gradient-to-r from-blue-400 via-cyan-300 to-[#00D4AA] bg-clip-text text-transparent">Help</span>
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto mb-6 leading-relaxed">
            Have questions about study guides, order fulfillment, simulator access, or need exam advice? Reach out anytime.
        </p>

        <div class="flex items-center justify-center gap-4">
            <a href="#contact-form" class="bg-[#2563eb] hover:bg-blue-600 text-white font-bold px-6 py-2.5 rounded-xl text-xs sm:text-sm shadow-md transition">
                Send Support Ticket
            </a>
            @php
                $supportEmail = $currentSite->contact_email ?? ($globalSettings['support_email'] ?? ($globalSettings['contact_email'] ?? 'support@proexamtopics.com'));
            @endphp
            <a href="mailto:{{ $supportEmail }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold px-6 py-2.5 rounded-xl text-xs sm:text-sm transition">
                Email Directly
            </a>
        </div>
    </div>
</x-site-b.hero>

<!-- Contact Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        <!-- Left: Contact info details -->
        <div class="space-y-6 lg:col-span-1">
            <h3 class="text-xl font-bold text-navy">Support Channels</h3>
            <p class="text-sm text-gray-500 leading-relaxed">For general inquiries, business partnerships, or refunds, feel free to drop a message or reach out via email directly. Most replies are delivered within 4-6 hours.</p>
            
            <div class="space-y-4 text-sm text-navy">
                <div class="flex items-start space-x-3">
                    <span class="text-cyan text-lg">✉</span>
                    <div>
                        <strong class="block">Email Support</strong>
                        @php
                            $supportEmail = $currentSite->contact_email ?? ($globalSettings['support_email'] ?? ($globalSettings['contact_email'] ?? 'support@proexamtopics.com'));
                        @endphp
                        <a href="mailto:{{ $supportEmail }}" class="text-cyan hover:underline">{{ $supportEmail }}</a>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3">
                    <span class="text-cyan text-lg">🕒</span>
                    <div>
                        <strong class="block">Business Hours</strong>
                        <span class="text-gray-500">24/7 Ticket Coverage, Email response mon-fri</span>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-gray-50 border border-gray-200 rounded-lg text-xs space-y-2">
                <p class="font-bold text-navy">Looking for Refund?</p>
                <p class="text-gray-500">Please include your Order Number (e.g. EN-2025xxxx) and attach a clear PDF or screenshot of your official vendor failure score report. Refunds are processed back to your original payment method within 3 business days.</p>
            </div>
        </div>

        <!-- Right: Contact Form -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 shadow-sm lg:col-span-2">
            <h3 class="text-lg font-bold text-navy mb-6">Send an Online Message</h3>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ url('/contact') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Your Full Name</label>
                        <input type="text" name="name" required placeholder="Enter name" class="w-full px-4 py-3 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required placeholder="Enter email" class="w-full px-4 py-3 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Subject</label>
                    <input type="text" name="subject" required placeholder="Order inquiry, billing, refund request..." class="w-full px-4 py-3 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Message Content</label>
                    <textarea name="message" required rows="6" placeholder="Write details of your question here..." class="w-full px-4 py-3 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan bg-white"></textarea>
                </div>

                <button type="submit" class="bg-orange hover-bg-orange text-white font-bold py-3.5 px-8 rounded shadow transition text-sm">
                    Send Message
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
