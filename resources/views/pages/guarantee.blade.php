@extends('layouts.public')

@section('title', '100% Money Back Guarantee - ExamTopicsBase')

@section('content')
<!-- Header Hero -->
<section class="relative bg-navy text-white py-16 lg:py-24 border-b border-white/10 overflow-hidden" style="background: linear-gradient(180deg, #0A1628 0%, #0D1F38 50%, #0A1628 100%) !important; background-color: #0A1628 !important; color: #ffffff !important;">
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-cyan/15 rounded-full filter blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-orange/15 rounded-full filter blur-[130px] pointer-events-none"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-5">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-cyan/10 border border-cyan/30 text-cyan text-xs font-black uppercase tracking-widest shadow-inner">
            <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Risk-Free Certification Preparation
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-tight" style="color: #ffffff !important;">
            100% Money Back <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan via-teal-300 to-emerald-400" style="color: #00D4AA;">Guarantee</span>
        </h1>

        <p class="text-base sm:text-lg text-gray-300 max-w-2xl mx-auto leading-relaxed" style="color: #cbd5e1 !important;">
            Pass your official IT certification exam on your first attempt, or receive a <strong class="text-white" style="color: #ffffff !important;">full 100% refund</strong>. We stand behind the accuracy and rigor of our study materials.
        </p>

        <!-- Trust Badges -->
        <div class="pt-4 flex flex-wrap items-center justify-center gap-6 text-xs sm:text-sm font-semibold text-gray-300" style="color: #cbd5e1 !important;">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                99.6% First-Attempt Pass Rate
            </span>
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan"></span>
                30-Day Full Protection Window
            </span>
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-orange"></span>
                Instant 24hr Claim Verification
            </span>
        </div>
    </div>
</section>

<!-- 3-Step Process Flowchart -->
<section class="py-14 bg-white border-b border-gray-150">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center space-y-2 mb-12">
            <h2 class="text-2xl sm:text-3xl font-black text-navy tracking-tight">How Our Guarantee Works</h2>
            <p class="text-xs sm:text-sm text-gray-500 max-w-lg mx-auto">Three transparent steps designed to ensure your investment is completely protected.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- Step 1 -->
            <div class="relative p-6 rounded-2xl bg-slate-50 border border-gray-200 text-center space-y-3 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-navy text-cyan text-xl font-black flex items-center justify-center mx-auto shadow-md">
                    1
                </div>
                <h3 class="text-base font-bold text-navy">Study with Our Materials</h3>
                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                    Practice with our verified questions, in-depth explanations, and browser-based test engine for at least one week prior to your test date.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="relative p-6 rounded-2xl bg-slate-50 border border-gray-200 text-center space-y-3 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-cyan text-navy text-xl font-black flex items-center justify-center mx-auto shadow-md">
                    2
                </div>
                <h3 class="text-base font-bold text-navy">Take Your Official Exam</h3>
                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                    Sit for the official vendor certification exam (Pearson VUE, Kryterion, PSI, or online proctored) with confidence within 30 days of purchase.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="relative p-6 rounded-2xl bg-slate-50 border border-gray-200 text-center space-y-3 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-orange text-white text-xl font-black flex items-center justify-center mx-auto shadow-md">
                    3
                </div>
                <h3 class="text-base font-bold text-navy">Pass or Get 100% Refund</h3>
                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                    In the unlikely event you fail, email your official score report to our support desk. We will process your full refund immediately.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Guarantee Terms & Guidelines -->
<section class="py-14 lg:py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 sm:p-12 space-y-10 text-gray-700 leading-relaxed">

            <!-- Section 1 -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">1</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Our Guarantee Promise</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    At <strong>ExamTopicsBase</strong>, our mission is to eliminate exam anxiety. We don't just sell question sets; we empower careers. Because our materials are meticulously maintained, verified by certified IT professionals, and refreshed continuously to reflect active vendor blueprints, we have the highest confidence in your passing outcome.
                </p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    If you prepare with our study guides and do not pass your official exam, you will not lose a single penny. You are entitled to your choice of either:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                        <strong class="block font-bold text-sm mb-1 text-emerald-950">Option A: 100% Money-Back Refund</strong>
                        <span class="text-xs text-emerald-800 leading-relaxed">Direct reimbursement back to your original payment card or PayPal account within 3-5 business days.</span>
                    </div>
                    <div class="p-4 rounded-xl bg-cyan/10 border border-cyan/30 text-navy">
                        <strong class="block font-bold text-sm mb-1">Option B: Free Exam Study Guide Swap</strong>
                        <span class="text-xs text-gray-600 leading-relaxed">Instant free enrollment in any other certification exam study guide and test engine in our entire catalog.</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Eligibility Rules -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">2</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Eligibility Guidelines</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    To prevent fraudulent abuse while ensuring genuine candidates are 100% protected, the following reasonable conditions apply:
                </p>

                <div class="space-y-2.5 text-xs sm:text-sm text-gray-600">
                    <div class="flex items-start gap-2.5">
                        <span class="text-emerald-500 font-bold">✔</span>
                        <div><strong>Matching Exam Code:</strong> The official test attempted must correspond to the exact exam code purchased (e.g. AWS SAA-C03, AZ-120, Cisco 200-301).</div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="text-emerald-500 font-bold">✔</span>
                        <div><strong>30-Day Window:</strong> The official test must be taken within <strong>30 days</strong> from the date of your purchase on ExamTopicsBase.</div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="text-emerald-500 font-bold">✔</span>
                        <div><strong>Official Score Report:</strong> Candidate must submit a valid digital score report or test center transcript showing candidate name, exam code, exam date, and official score.</div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="text-emerald-500 font-bold">✔</span>
                        <div><strong>Genuine Preparation:</strong> The candidate should have studied the material prior to the exam date (claims for exams sat before purchasing our product are ineligible).</div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Step-by-Step Claim Submission -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">3</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">How to Submit Your Claim</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Submitting a refund claim is straightforward. Send an email to our support team with the following details:
                </p>

                <div class="p-5 rounded-xl bg-slate-900 text-white space-y-3 font-mono text-xs">
                    <div class="text-cyan font-bold font-sans text-sm">Required Claim Submission Email Details:</div>
                    <div class="space-y-1.5 text-gray-300">
                        <div><strong>To:</strong> support@examtopicsbase.com</div>
                        <div><strong>Subject:</strong> Guarantee Refund Request - Order #[Your-Order-ID]</div>
                        <div><strong>Body:</strong></div>
                        <ul class="list-disc list-inside pl-2 space-y-1 text-gray-400">
                            <li>Your registered account name and email address</li>
                            <li>Your ExamTopicsBase Order Number or transaction ID</li>
                            <li>The exam code you attempted (e.g., AZ-120)</li>
                            <li>Attached copy (PDF, scan, or clear photo) of your official testing center score report</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Section 4: Fast Turnaround Timeline -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">4</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Processing Timeline</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                        <span class="block text-2xl font-black text-navy mb-1">&lt; 24h</span>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Claim Review</span>
                        <p class="text-xs text-gray-500 mt-1">Our support desk validates score reports within 24 business hours.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                        <span class="block text-2xl font-black text-cyan mb-1">Instant</span>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Refund Issued</span>
                        <p class="text-xs text-gray-500 mt-1">Reimbursement is executed through Stripe / PayPal gateway.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                        <span class="block text-2xl font-black text-emerald-600 mb-1">3–5 Days</span>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Bank Posting</span>
                        <p class="text-xs text-gray-500 mt-1">Funds reflect on your statement depending on your bank's schedule.</p>
                    </div>
                </div>
            </div>

            <!-- Section 5: FAQs -->
            <div class="space-y-4 pt-4 border-t border-gray-150" x-data="{ openFaq: null }">
                <h3 class="text-xl font-bold text-navy">Frequently Asked Guarantee Questions</h3>
                <div class="space-y-3">
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <button type="button" @click="openFaq = (openFaq === 1 ? null : 1)" class="w-full text-left p-4 bg-gray-50 hover:bg-gray-100 flex items-center justify-between font-bold text-xs sm:text-sm text-navy">
                            <span>What if I bought a bundle with multiple exams?</span>
                            <span x-text="openFaq === 1 ? '−' : '+'" class="text-base text-cyan font-black"></span>
                        </button>
                        <div x-show="openFaq === 1" x-cloak class="p-4 text-xs sm:text-sm text-gray-600 bg-white border-t border-gray-200">
                            If you purchased a discounted multi-exam bundle or unlimited subscription and failed one exam, we offer a proportional refund for the individual exam price or a free extension/replacement of study materials.
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <button type="button" @click="openFaq = (openFaq === 2 ? null : 2)" class="w-full text-left p-4 bg-gray-50 hover:bg-gray-100 flex items-center justify-between font-bold text-xs sm:text-sm text-navy">
                            <span>Can I swap for another exam instead of a refund?</span>
                            <span x-text="openFaq === 2 ? '−' : '+'" class="text-base text-cyan font-black"></span>
                        </button>
                        <div x-show="openFaq === 2" x-cloak class="p-4 text-xs sm:text-sm text-gray-600 bg-white border-t border-gray-200">
                            Yes! Many candidates prefer to switch to a different certification or re-take package. Just let us know in your email and we will activate your chosen replacement guide immediately at no extra cost.
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <button type="button" @click="openFaq = (openFaq === 3 ? null : 3)" class="w-full text-left p-4 bg-gray-50 hover:bg-gray-100 flex items-center justify-between font-bold text-xs sm:text-sm text-navy">
                            <span>What if my test center was delayed or rescheduled?</span>
                            <span x-text="openFaq === 3 ? '−' : '+'" class="text-base text-cyan font-black"></span>
                        </button>
                        <div x-show="openFaq === 3" x-cloak class="p-4 text-xs sm:text-sm text-gray-600 bg-white border-t border-gray-200">
                            If your vendor test was rescheduled by Pearson VUE, Kryterion, or due to illness, simply contact us before your 30-day window expires with proof of rescheduling, and we will happily extend your guarantee period.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Box -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-navy to-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h4 class="font-bold text-base text-white">Have a question about our guarantee?</h4>
                    <p class="text-xs text-gray-300 mt-0.5">Our support engineers are available 24/7 to assist candidates.</p>
                </div>
                <a href="mailto:support@examtopicsbase.com" class="px-5 py-2.5 rounded-xl bg-cyan text-navy font-bold text-xs uppercase tracking-wider hover:bg-white transition shadow-lg flex-shrink-0">
                    Email Support Desk
                </a>
            </div>

        </div>
    </div>
</section>
@endsection
