@extends('layouts.public')

@section('title', $exam->meta_title ?? "{$exam->exam_code} Exam Dumps & Study Guide | ExamsNinja")
@section('meta_description', $exam->meta_description ?? "Get updated {$exam->exam_code} ({$exam->exam_name}) exam questions, answers, and study guides. Try our free demo or web-based test engine.")
@section('meta_keywords', $exam->meta_keywords ?? "{$exam->exam_code}, {$exam->exam_code} exam dumps, {$exam->exam_code} practice test, {$exam->vendor->name} certification")
@section('canonical_url', route('exams.show', $exam->slug))
@section('og_type', 'product')

@section('seo_tags')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Course",
  "name": "{{ $exam->exam_code }} - {{ $exam->exam_name }}",
  "description": "{{ strip_tags($exam->description) }}",
  "provider": {
    "@type": "Organization",
    "name": "{{ $exam->vendor->name }}",
    "sameAs": "{{ route('vendors.show', $exam->vendor->slug) }}"
  },
  "offers": {
    "@type": "Offer",
    "price": "{{ $exam->price_engine }}",
    "priceCurrency": "USD",
    "category": "Test Preparation"
  }
}
</script>
@endsection

@section('content')
<!-- Top Header / Intro -->
<section class="bg-navy text-white py-12" x-data="{ demoModalOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <div class="mb-4">
            <x-breadcrumbs :links="[
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Vendors', 'url' => '/vendors'],
                ['name' => $exam->vendor->name, 'url' => '/vendors/' . $exam->vendor->slug],
                ['name' => $exam->exam_code, 'url' => '']
            ]" />
        </div>

        <!-- Main Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: Details & Previews (70%) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Exam Intro Header -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <span class="bg-cyan bg-opacity-25 text-white font-bold text-xs px-3 py-1 rounded border border-cyan border-opacity-35">{{ $exam->exam_code }}</span>
                        <span class="bg-gray-800 text-gray-300 text-xs px-2.5 py-1 rounded">{{ $exam->difficulty }} Level</span>
                        <span class="text-xs text-gray-400 font-semibold">Updated: {{ $exam->last_updated_at ? $exam->last_updated_at->format('F d, Y') : 'June 19, 2026' }}</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight">{{ $exam->exam_name }} Study Guide & Practice Questions</h1>
                    
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-300 pt-2 font-medium">
                        <span class="flex items-center">❓ <strong class="ml-1 text-white">{{ $exam->question_count }}</strong>&nbsp;Practice Questions</span>
                        <span class="flex items-center">🎯 <strong class="ml-1 text-white">{{ $exam->passing_score }}%</strong>&nbsp;Passing Score</span>
                        <span class="flex items-center">⏱️ <strong class="ml-1 text-white">Interactive</strong>&nbsp;Test Engine Supported</span>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-gray-800 bg-opacity-40 border border-gray-700 rounded-lg p-6 space-y-4 text-gray-300 text-sm leading-relaxed">
                    <h3 class="text-white font-bold text-base">About the Certification Exam</h3>
                    <p>{{ $exam->description }}</p>
                    <p>Our expert certification guides include comprehensive questions and answers designed to mirror the actual exam environment. The full study package will prepare you for the variety of formats found on this test, including multiple choice, multi-select, and drag-and-drop questions.</p>
                </div>

                <!-- Collapsible Topics covered -->
                <div class="space-y-4" x-data="{ open: true }">
                    <button @click="open = !open" class="flex justify-between items-center w-full bg-white border border-gray-200 rounded-lg p-4 font-bold text-navy text-left focus:outline-none">
                        <span>Blueprinted Exam Topics Covered</span>
                        <svg class="h-5 w-5 transition duration-200" :class="open ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="bg-white border border-gray-200 rounded-lg p-6 space-y-3" style="display: none;">
                        <p class="text-sm text-gray-500 mb-4">The study guide addresses all core domains defined in the official vendor certification syllabus:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm font-semibold text-navy">
                            @if(is_array($exam->topics))
                                @foreach($exam->topics as $topic)
                                    <div class="flex items-start space-x-2">
                                        <span class="text-cyan text-base">✔</span>
                                        <span>{{ $topic }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex items-start space-x-2">
                                    <span class="text-cyan text-base">✔</span>
                                    <span>General Exam Concepts</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sample Questions -->
                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-white mb-2">Free Interactive Sample Questions (Try Solving!)</h3>
                    
                    @foreach($sampleQuestions as $index => $question)
                        <div class="bg-white border border-gray-200 rounded-lg p-6 text-navy space-y-4" x-data="{ selectedOption: null, checked: false }">
                            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                <span class="text-xs font-bold text-gray-400 font-mono">QUESTION {{ $index + 1 }}</span>
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded">{{ $question->topic }}</span>
                            </div>
                            <p class="text-sm font-bold leading-relaxed">{!! $question->question_text !!}</p>
                            
                            <!-- Options list styled -->
                            <div class="space-y-2 text-sm">
                                <!-- Option A -->
                                <button @click="if(!checked) selectedOption = 'A'"
                                        class="w-full text-left p-2.5 rounded border text-sm transition flex items-center justify-between"
                                        :class="[
                                            !checked && selectedOption === 'A' ? 'border-cyan bg-cyan/5 text-cyan font-semibold' : '',
                                            !checked && selectedOption !== 'A' ? 'border-gray-200 hover:bg-gray-50 text-navy' : '',
                                            checked && 'A' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500/10 text-green-600 font-semibold' : '',
                                            checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500/10 text-red-600' : '',
                                            checked && 'A' !== '{{ $question->correct_option }}' && selectedOption !== 'A' ? 'border-gray-100 opacity-60 text-gray-400' : ''
                                        ]"
                                        :disabled="checked">
                                    <div class="flex items-center space-x-3 pr-2">
                                        <input type="radio" :checked="selectedOption === 'A'" :disabled="checked" class="text-cyan focus:ring-cyan h-4 w-4">
                                        <span><strong>A.</strong> {{ $question->option_a }}</span>
                                    </div>
                                    <template x-if="checked && 'A' === '{{ $question->correct_option }}'">
                                        <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </template>
                                    <template x-if="checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}'">
                                        <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </template>
                                </button>

                                <!-- Option B -->
                                <button @click="if(!checked) selectedOption = 'B'"
                                        class="w-full text-left p-2.5 rounded border text-sm transition flex items-center justify-between"
                                        :class="[
                                            !checked && selectedOption === 'B' ? 'border-cyan bg-cyan/5 text-cyan font-semibold' : '',
                                            !checked && selectedOption !== 'B' ? 'border-gray-200 hover:bg-gray-50 text-navy' : '',
                                            checked && 'B' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500/10 text-green-600 font-semibold' : '',
                                            checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500/10 text-red-600' : '',
                                            checked && 'B' !== '{{ $question->correct_option }}' && selectedOption !== 'B' ? 'border-gray-100 opacity-60 text-gray-400' : ''
                                        ]"
                                        :disabled="checked">
                                    <div class="flex items-center space-x-3 pr-2">
                                        <input type="radio" :checked="selectedOption === 'B'" :disabled="checked" class="text-cyan focus:ring-cyan h-4 w-4">
                                        <span><strong>B.</strong> {{ $question->option_b }}</span>
                                    </div>
                                    <template x-if="checked && 'B' === '{{ $question->correct_option }}'">
                                        <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </template>
                                    <template x-if="checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}'">
                                        <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </template>
                                </button>

                                <!-- Option C -->
                                @if(!empty($question->option_c))
                                    <button @click="if(!checked) selectedOption = 'C'"
                                            class="w-full text-left p-2.5 rounded border text-sm transition flex items-center justify-between"
                                            :class="[
                                                !checked && selectedOption === 'C' ? 'border-cyan bg-cyan/5 text-cyan font-semibold' : '',
                                                !checked && selectedOption !== 'C' ? 'border-gray-200 hover:bg-gray-50 text-navy' : '',
                                                checked && 'C' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500/10 text-green-600 font-semibold' : '',
                                                checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500/10 text-red-600' : '',
                                                checked && 'C' !== '{{ $question->correct_option }}' && selectedOption !== 'C' ? 'border-gray-100 opacity-60 text-gray-400' : ''
                                            ]"
                                            :disabled="checked">
                                        <div class="flex items-center space-x-3 pr-2">
                                            <input type="radio" :checked="selectedOption === 'C'" :disabled="checked" class="text-cyan focus:ring-cyan h-4 w-4">
                                            <span><strong>C.</strong> {{ $question->option_c }}</span>
                                        </div>
                                        <template x-if="checked && 'C' === '{{ $question->correct_option }}'">
                                            <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </template>
                                        <template x-if="checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}'">
                                            <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </template>
                                    </button>
                                @endif

                                <!-- Option D -->
                                @if(!empty($question->option_d))
                                    <button @click="if(!checked) selectedOption = 'D'"
                                            class="w-full text-left p-2.5 rounded border text-sm transition flex items-center justify-between"
                                            :class="[
                                                !checked && selectedOption === 'D' ? 'border-cyan bg-cyan/5 text-cyan font-semibold' : '',
                                                !checked && selectedOption !== 'D' ? 'border-gray-200 hover:bg-gray-50 text-navy' : '',
                                                checked && 'D' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500/10 text-green-600 font-semibold' : '',
                                                checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500/10 text-red-600' : '',
                                                checked && 'D' !== '{{ $question->correct_option }}' && selectedOption !== 'D' ? 'border-gray-100 opacity-60 text-gray-400' : ''
                                            ]"
                                            :disabled="checked">
                                        <div class="flex items-center space-x-3 pr-2">
                                            <input type="radio" :checked="selectedOption === 'D'" :disabled="checked" class="text-cyan focus:ring-cyan h-4 w-4">
                                            <span><strong>D.</strong> {{ $question->option_d }}</span>
                                        </div>
                                        <template x-if="checked && 'D' === '{{ $question->correct_option }}'">
                                            <svg class="h-4 w-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </template>
                                        <template x-if="checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}'">
                                            <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </template>
                                    </button>
                                @endif
                            </div>

                            <!-- Grade actions -->
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-xs text-gray-400 italic">Select an option above to verify</span>
                                <template x-if="!checked">
                                    <button @click="checked = true"
                                            class="bg-navy text-white text-xs font-semibold px-4 py-1.5 rounded shadow transition hover:bg-opacity-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="!selectedOption">
                                        Check Answer
                                    </button>
                                </template>
                                <template x-if="checked">
                                    <span class="text-xs font-bold" :class="selectedOption === '{{ $question->correct_option }}' ? 'text-green-600' : 'text-red-600'">
                                        <span x-text="selectedOption === '{{ $question->correct_option }}' ? '✓ Correct Answer' : '✗ Incorrect Answer'"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Explanation slide down -->
                            <div x-show="checked" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform -translate-y-1"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 class="bg-gray-50 border border-gray-150 p-4 rounded text-xs space-y-1">
                                <div class="font-bold text-navy">Correct Option: {{ $question->correct_option }}</div>
                                <p class="text-gray-600 leading-relaxed">{{ $question->explanation }}</p>
                            </div>
                        </div>
                    @endforeach

                    <!-- CTA to buy full exam to unlock answers -->
                    <div class="bg-orange bg-opacity-10 border border-orange border-opacity-35 rounded-lg p-6 text-center space-y-4">
                        <h4 class="text-base font-bold text-white">Want to practice all {{ $exam->question_count }} questions under exam conditions?</h4>
                        <p class="text-sm text-gray-300">Unlock full access to the timed Test Engine database and downloadable PDF study guides by adding it to your cart above.</p>
                        <a href="#purchase-card" class="inline-block bg-orange hover-bg-orange text-white text-sm font-bold py-3 px-8 rounded shadow transition">
                            Unlock Full Access Simulator
                        </a>
                    </div>
                </div>

                <!-- Customer Reviews Section -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 text-navy space-y-6">
                    <h3 class="text-lg font-bold border-b border-gray-150 pb-4">Verified Customer Reviews</h3>
                    @if(count($reviews) > 0)
                        <div class="space-y-6">
                            @foreach($reviews as $review)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="h-8 w-8 rounded-full bg-navy text-white flex items-center justify-center font-bold text-xs">{{ substr($review->user->name, 0, 2) }}</div>
                                            <span class="text-sm font-bold text-navy">{{ $review->user->name }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1 text-yellow-400 text-xs">
                                            @for($i=1; $i<=5; $i++)
                                                <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 pl-10 italic">"{{ $review->review_text }}"</p>
                                    <span class="block text-[10px] text-gray-400 pl-10">Reviewed on: {{ $review->created_at->format('M d, Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-500 text-sm">
                            No reviews posted yet. Be the first to leave a review!
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right Column: Sticky Purchase Box (30%) -->
            <div id="purchase-card" class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-6 text-navy space-y-6">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-400 uppercase">Guides Package</span>
                            <!-- Rating -->
                            <div class="flex items-center space-x-1 text-yellow-400 text-xs">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                <span class="text-navy text-[10px] font-bold ml-1">4.9/5</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-navy">{{ $exam->exam_code }} Exam Study Guide</h3>
                    </div>

                    <!-- Option 1: PDF Guide -->
                    <div class="border border-gray-200 rounded-lg p-4 relative hover:border-cyan transition">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2 py-0.5 rounded">PDF ONLY</span>
                                <h4 class="font-bold text-sm mt-1">Printable PDF Guide</h4>
                            </div>
                            <span class="text-lg font-bold text-navy">${{ $exam->price_pdf }}</span>
                        </div>
                        <ul class="text-xs text-gray-500 space-y-1 mb-4">
                            <li>• Instant download, access forever</li>
                            <li>• Printable and mobile-friendly</li>
                            <li>• Verified questions with answers</li>
                            <li>• Free updates for 90 days</li>
                        </ul>
                        <form action="{{ url('/cart/add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                            <input type="hidden" name="type" value="pdf">
                            <button type="submit" class="w-full bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 rounded shadow transition">
                                Buy PDF Now
                            </button>
                        </form>
                    </div>

                    <!-- Option 2: Test Engine -->
                    <div class="border border-gray-200 rounded-lg p-4 relative hover:border-cyan transition">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="bg-cyan bg-opacity-15 text-navy text-[10px] font-bold px-2 py-0.5 rounded border border-cyan border-opacity-30">TEST ENGINE</span>
                                <h4 class="font-bold text-sm mt-1">Web-Based Test Engine</h4>
                            </div>
                            <span class="text-lg font-bold text-navy">${{ $exam->price_engine }}</span>
                        </div>
                        <ul class="text-xs text-gray-500 space-y-1 mb-4">
                            <li>• Timed practice & exam modes</li>
                            <li>• Review flagged/incorrect answers</li>
                            <li>• Detailed explanations for all</li>
                            <li>• Accessible on mobile & tablet</li>
                        </ul>
                        <form action="{{ url('/cart/add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                            <input type="hidden" name="type" value="engine_single">
                            <button type="submit" class="w-full bg-cyan hover-bg-cyan text-navy text-xs font-bold py-2.5 rounded shadow transition">
                                Add Test Engine to Cart
                            </button>
                        </form>
                    </div>

                    <!-- Free Demo Button Link -->
                    <button @click="demoModalOpen = true" class="w-full border border-gray-300 hover:border-cyan text-navy hover:bg-gray-50 text-xs font-bold py-3 rounded text-center transition">
                        Download Free Demo PDF
                    </button>

                    <!-- Trust info -->
                    <div class="border-t border-gray-100 pt-4 text-center space-y-2">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">🔒 256-Bit SSL Encrypted Checkout</p>
                        <p class="text-[10px] text-gray-400">Instant delivery to your email after verification.</p>
                    </div>
                </div>

                <!-- Guarantee card info -->
                <div class="bg-gray-800 border border-gray-700 text-gray-300 rounded-lg p-5 text-center space-y-3">
                    <p class="text-xs font-bold text-white uppercase tracking-wider">30-Day Money Back Guarantee</p>
                    <p class="text-xs">We guarantee you will pass your IT certification exam on your first attempt. If you do not pass within 30 days of purchase, email us your score report for a 100% refund immediately.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Free Demo Modal (Alpine.js) -->
    <div x-show="demoModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-navy bg-opacity-70" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        <div class="bg-white rounded-lg shadow-xl text-navy max-w-md w-full overflow-hidden p-6 relative" @click.away="demoModalOpen = false">
            <button @click="demoModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-navy focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <h3 class="text-xl font-bold mb-2">Request Free Demo Guide</h3>
            <p class="text-sm text-gray-500 mb-6">Enter your details below to receive a free sample guide containing the first 10 questions with verified answers for the {{ $exam->exam_code }} exam.</p>

            <form action="{{ url('/free-demo') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Your Full Name</label>
                    <input type="text" name="name" required placeholder="Enter name" class="w-full px-3.5 py-2.5 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="Enter email" class="w-full px-3.5 py-2.5 rounded border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-cyan">
                </div>

                <button type="submit" class="w-full bg-orange hover-bg-orange text-white font-bold py-3 rounded shadow transition text-sm">
                    Send My Free Demo Link
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Related Exams Section -->
@php
    $relatedExams = App\Models\Exam::where('vendor_id', $exam->vendor_id)
        ->where('id', '!=', $exam->id)
        ->where('is_active', true)
        ->inRandomOrder()
        ->take(6)
        ->get();
@endphp

@if($relatedExams->count() > 0)
<section class="py-16 bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-extrabold text-navy mb-8">Related Exams You Might Need</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach($relatedExams as $related)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow flex flex-col h-full bg-gray-50">
                <div class="text-xs text-gray-500 font-semibold mb-1 uppercase tracking-wide">{{ $exam->vendor->name }}</div>
                <h3 class="text-lg font-bold text-navy mb-2 leading-tight flex-grow">
                    <a href="{{ route('exams.show', $related->slug) }}" class="hover:text-cyan">{{ $related->exam_code }}</a>
                </h3>
                <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-200">
                    <span class="font-bold text-gray-900">${{ $related->price_pdf }}</span>
                    <a href="{{ route('exams.show', $related->slug) }}" class="text-sm font-semibold text-cyan hover:text-navy transition-colors">View &rarr;</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
