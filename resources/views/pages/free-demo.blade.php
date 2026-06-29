@extends('layouts.public')

@section('title', 'Download Free Exam PDF Sample - ExamsNinja')

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-16 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
            Try Before You Buy - Free Sample Guides
        </h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Get a free demo containing 10 verified practice questions with explanations delivered directly to your email instantly.
        </p>
    </div>
</section>

<!-- Demo Capture Form -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        
        <!-- Left: Branding & Info -->
        <div class="space-y-6">
            <h3 class="text-2xl font-bold text-navy">What is included in the Free Demo PDF?</h3>
            <p class="text-sm text-gray-600 leading-relaxed">Our free demo guide gives you a sneak peek into the quality of our premium dumps. Unlike other websites, we don't hide explanations behind watermarks. You will get:</p>
            
            <ul class="space-y-4 text-sm text-navy font-semibold">
                <li class="flex items-center space-x-2">
                    <span class="text-cyan text-lg">✔</span>
                    <span>10 real exam questions with multiple-choice options</span>
                </li>
                <li class="flex items-center space-x-2">
                    <span class="text-cyan text-lg">✔</span>
                    <span>Verified correct answers marked clearly</span>
                </li>
                <li class="flex items-center space-x-2">
                    <span class="text-cyan text-lg">✔</span>
                    <span>Full text explanations of core technical topics</span>
                </li>
                <li class="flex items-center space-x-2">
                    <span class="text-cyan text-lg">✔</span>
                    <span>100% formatted PDF compatible with mobile and print</span>
                </li>
            </ul>

            <div class="p-5 bg-gray-50 border border-gray-200 rounded-lg text-xs space-y-2">
                <p class="font-bold text-navy">No Credit Card Required</p>
                <p class="text-gray-500">We respect your privacy. We will never share your email address. You will receive one email containing the temporary R2 download link which remains active for 24 hours.</p>
            </div>
        </div>

        <!-- Right: Form -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 shadow-sm">
            <h3 class="text-lg font-bold text-navy mb-6">Request Your Free Demo Link</h3>
            
            @if(session('status'))
                <!-- We already have global banner but here is a nice custom block -->
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded text-sm font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ url('/free-demo') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Your Full Name</label>
                    <input type="text" name="name" required placeholder="Enter your name" class="w-full px-4 py-3 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email" class="w-full px-4 py-3 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Select Certification Exam</label>
                    <select name="exam_id" required class="w-full px-4 py-3 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan bg-white text-gray-700">
                        <option value="">Choose an exam...</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">
                                {{ $exam->exam_code }} - {{ $exam->exam_name }} ({{ $exam->vendor ? $exam->vendor->name : '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-orange hover-bg-orange text-white font-bold py-3.5 rounded shadow transition text-sm">
                    Submit Request
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
