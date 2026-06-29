@extends('layouts.public')

@section('title', 'Online Test Engine & Certification Simulator - ExamsNinja')
@section('meta_description', 'Experience the ExamsNinja interactive timed test engine. Practice with real exam scenarios, exam simulation, immediate response grading, and detailed explanations.')

@section('content')
<!-- Hero Section -->
<section class="bg-navy text-white pt-16 pb-20 relative overflow-hidden">
    <div class="absolute right-0 top-0 opacity-10 transform translate-x-1/4 -translate-y-1/4 select-none pointer-events-none">
        <svg class="h-96 w-96 text-cyan" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Side Text -->
            <div class="lg:col-span-6 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center space-x-1 bg-gray-800 text-cyan text-xs font-semibold px-3 py-1.5 rounded-full border border-gray-700 shadow">
                    <span class="bg-cyan h-2 w-2 rounded-full animate-ping mr-1"></span>
                    <span>Interactive Practice Software</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                    The Ultimate <span class="text-cyan">Exam Simulator</span> & Test Engine
                </h1>
                
                <p class="text-lg text-gray-300 max-w-lg mx-auto lg:mx-0">
                    Prepare for certification success under real exam conditions. Our browser-based software simulates actual testing environments for AWS, Cisco, CompTIA, Microsoft, and more.
                </p>

                <!-- Stats Grid -->
                <div class="grid grid-cols-3 gap-4 pt-4 max-w-md mx-auto lg:mx-0">
                    <div class="bg-gray-800/50 p-3 rounded-lg border border-gray-700/50">
                        <div class="text-2xl font-bold text-cyan">{{ $totalQuestions }}</div>
                        <div class="text-xs text-gray-400">Total Questions</div>
                    </div>
                    <div class="bg-gray-800/50 p-3 rounded-lg border border-gray-700/50">
                        <div class="text-2xl font-bold text-white">{{ $totalExams }}</div>
                        <div class="text-xs text-gray-400">Exams Supported</div>
                    </div>
                    <div class="bg-gray-800/50 p-3 rounded-lg border border-gray-700/50">
                        <div class="text-2xl font-bold text-orange">99.4%</div>
                        <div class="text-xs text-gray-400">Pass Rate</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start space-y-3 sm:space-y-0 sm:space-x-4 pt-2">
                    <a href="{{ route('pricing') }}" class="w-full sm:w-auto bg-cyan hover-bg-cyan text-navy px-8 py-3.5 rounded-md font-bold text-center shadow transition transform hover:-translate-y-0.5">
                        Get All-Access Pass
                    </a>
                    <a href="#demo-simulator" class="w-full sm:w-auto bg-gray-850 hover:bg-gray-800 text-white border border-gray-700 px-8 py-3.5 rounded-md font-semibold text-center transition">
                        Try Live Demo Below
                    </a>
                </div>
            </div>

            <!-- Right Side Live Demo Mock Widget (Alpine.js) -->
            <div class="lg:col-span-6" id="demo-simulator">
                <div class="bg-gray-900 border border-gray-700 rounded-xl p-8 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="h-32 w-32 text-cyan" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" /></svg>
                    </div>
                    
                    <h3 class="text-2xl font-extrabold text-white mb-2">Experience the Real Engine</h3>
                    <p class="text-sm text-gray-400 mb-8">Access 10 free demo questions in our fully functional, interactive testing environment. No signup required.</p>
                    
                    <div class="space-y-4 relative z-10">
                        @foreach($compatibleExams->take(3) as $exam)
                            <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="block bg-gray-800 hover:bg-gray-750 border border-gray-700 hover:border-cyan rounded-lg p-4 transition duration-200 group">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="flex items-center space-x-2 mb-1">
                                            <span class="bg-gray-950 text-gray-300 text-[10px] font-bold px-2 py-0.5 rounded border border-gray-800">{{ $exam->vendor->name }}</span>
                                        </div>
                                        <h4 class="font-bold text-white group-hover:text-cyan transition">{{ $exam->exam_code }}</h4>
                                        <p class="text-xs text-gray-400 truncate max-w-[250px] sm:max-w-xs">{{ $exam->exam_name }}</p>
                                    </div>
                                    <div class="h-10 w-10 rounded-full bg-cyan/10 text-cyan flex items-center justify-center group-hover:bg-cyan group-hover:text-navy transition">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 text-center">
                        <a href="#compatible-exams" class="text-xs text-cyan hover:text-white transition underline">View all compatible exams &darr;</a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- Core Features Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-4">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-navy">
                Designed to Match Actual Exam Platforms
            </h2>
            <p class="text-gray-600">
                Forget simple static PDFs. Learn interactively with multiple practice settings designed to expose weak areas and lock in key concepts.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Practice Mode -->
            <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/60 shadow-sm hover:shadow-md transition">
                <div class="h-12 w-12 rounded-lg bg-cyan/10 flex items-center justify-center text-cyan mb-6">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-navy mb-3">1. Practice Mode</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Study at your own pace. Reveal the correct answer and read detailed, step-by-step explanations immediately after submitting each question. Perfect for learning new concepts.
                </p>
            </div>

            <!-- Exam Simulator -->
            <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/60 shadow-sm hover:shadow-md transition">
                <div class="h-12 w-12 rounded-lg bg-orange/10 flex items-center justify-center text-orange mb-6">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-navy mb-3">2. Exam Simulator Mode</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Test yourself under realistic conditions. Features strict time limits and randomized questions. Explanations and overall score results are locked until you submit the entire attempt.
                </p>
            </div>

            <!-- Review Mode -->
            <div class="bg-gray-50 p-8 rounded-xl border border-gray-200/60 shadow-sm hover:shadow-md transition">
                <div class="h-12 w-12 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-650 mb-6">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-navy mb-3">3. Focused Review Mode</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Study smarter, not longer. Adaptive logic filters and presents only questions you previously flagged or answered incorrectly in your last attempt. Drill down on weak spots.
                </p>
            </div>
        </div>

    </div>
</section>

<!-- Compatible Exams Section -->
<section id="compatible-exams" class="py-20 bg-gray-50 border-t border-b border-gray-200/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
            <div>
                <h2 class="text-3xl font-extrabold text-navy mb-2">Compatible Practice Tests</h2>
                <p class="text-gray-655 max-w-lg">
                    These certifications are fully mapped and populated with active question banks in our online test engine:
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('vendors.index') }}" class="text-cyan font-bold hover:underline inline-flex items-center space-x-1">
                    <span>Browse All Compatibility List</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($compatibleExams as $exam)
                <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between hover:shadow transition">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-1 rounded">
                                {{ $exam->vendor->name }}
                            </span>
                            <span class="text-xs text-gray-500 font-mono">
                                {{ $exam->questions_count ?? $exam->questions()->count() }} Questions
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-navy mb-1">{{ $exam->exam_code }}</h3>
                        <p class="text-sm text-gray-600 mb-6 truncate">{{ $exam->exam_name }}</p>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <span class="text-xs text-gray-500">Passing Score: <strong class="text-navy">{{ $exam->passing_score }}%</strong></span>
                        <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="bg-navy hover:bg-gray-800 text-white text-xs font-semibold px-4 py-2 rounded transition">
                            Start Free Demo
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-8 text-gray-500">
                    No compatible exams found. Let's upload questions in the Admin panel!
                </div>
            @endforelse
        </div>

    </div>
</section>

<!-- Call to Action Section -->
<section class="py-16 bg-navy text-white text-center relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 mix-blend-overlay bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1517694712202-14dd9538aa97');"></div>
    <div class="max-w-4xl mx-auto px-4 relative z-10 space-y-6">
        <h2 class="text-3xl sm:text-4xl font-extrabold">Ready to Ace Your Exam?</h2>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Get unlimited access to ALL certification test engines by choosing one of our membership plans, or buy single exam simulator keys today.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
            <a href="{{ route('pricing') }}" class="w-full sm:w-auto bg-cyan hover-bg-cyan text-navy px-8 py-3.5 rounded-md font-bold transition shadow-lg">
                View Pricing Plans
            </a>
            @guest
                <a href="{{ route('register') }}" class="w-full sm:w-auto bg-transparent hover:bg-white/10 text-white border border-gray-600 px-8 py-3.5 rounded-md font-semibold transition">
                    Create Free Account
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto bg-transparent hover:bg-white/10 text-white border border-gray-600 px-8 py-3.5 rounded-md font-semibold transition">
                    Go to Dashboard
                </a>
            @endguest
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-navy">Test Engine FAQ</h2>
            <p class="text-gray-500 mt-2">Common questions about our online practice environment</p>
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            
            <!-- FAQ 1 -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="active = active === 1 ? null : 1" class="w-full flex justify-between items-center p-5 text-left font-semibold text-navy bg-gray-50 hover:bg-gray-100/70 transition">
                    <span>Is the Test Engine mobile friendly?</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="active === 1 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 1" class="p-5 border-t border-gray-200 text-sm text-gray-600 leading-relaxed bg-white">
                    Yes, absolutely! The Test Engine portal is built with a fully responsive layout. You can practice on your phone, tablet, or desktop computer anytime. Your progress, attempt history, and flagged questions sync automatically.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="active = active === 2 ? null : 2" class="w-full flex justify-between items-center p-5 text-left font-semibold text-navy bg-gray-50 hover:bg-gray-100/70 transition">
                    <span>How often are exam questions updated?</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="active === 2 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 2" class="p-5 border-t border-gray-200 text-sm text-gray-600 leading-relaxed bg-white">
                    Our database is continuously updated based on actual candidate feedback, vendor syllabus changes, and question updates. When a vendor updates their exam format (e.g., from CLF-C01 to CLF-C02), we sync our question database within 48 hours to ensure compatibility.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="active = active === 3 ? null : 3" class="w-full flex justify-between items-center p-5 text-left font-semibold text-navy bg-gray-50 hover:bg-gray-100/70 transition">
                    <span>What is your money-back guarantee?</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="active === 3 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 3" class="p-5 border-t border-gray-200 text-sm text-gray-600 leading-relaxed bg-white">
                    We offer a 100% money-back guarantee. If you purchase access to any Test Engine exam dump, complete at least 2 full-length simulated practice attempts scoring 80% or above in our software, and still fail the official certification exam within 30 days of purchase, just email us your official score report, and we will issue a full refund immediately.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="active = active === 4 ? null : 4" class="w-full flex justify-between items-center p-5 text-left font-semibold text-navy bg-gray-50 hover:bg-gray-100/70 transition">
                    <span>Can I use the Test Engine offline?</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="active === 4 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 4" class="p-5 border-t border-gray-200 text-sm text-gray-600 leading-relaxed bg-white">
                    No, the interactive Test Engine runs directly inside your browser and requires an active internet connection to retrieve questions, record attempts, track flagged items, and calculate performance metrics. However, you can download static study guides in PDF format to study offline.
                </div>
            </div>

        </div>

    </div>
</section>
@endsection
