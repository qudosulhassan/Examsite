@extends('layouts.public')

@section('title', 'Contact Support - ExamsNinja')

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-16 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
            Get in Touch with ExamsNinja
        </h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Need help with a purchase? Have questions about study materials? Our support team is here 24/7.
        </p>
    </div>
</section>

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
                        <a href="mailto:support@examsninja.com" class="text-cyan hover:underline">support@examsninja.com</a>
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
