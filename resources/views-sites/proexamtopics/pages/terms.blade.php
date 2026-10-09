@extends('layouts.public')

@section('title', 'Terms and Conditions | ' . ($currentSite->name ?? ($globalSettings['site_name'] ?? config('app.name', 'ProExamTopics'))))
@section('meta_description', 'Read the Terms and Conditions of ' . ($currentSite->name ?? 'ProExamTopics') . '. Understand our educational service policies, user account guidelines, refund guarantee, and intellectual property terms.')

@section('content')
<!-- Legal Hero Header -->
<x-site-b.hero variant="legal" glowColor="cyan" padding="compact">
    <div class="max-w-4xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-4 backdrop-blur-sm shadow-sm">
            <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
            <span class="font-mono-code text-[#00D4AA] font-bold">LEGAL &amp; COMPLIANCE</span>
            <span class="text-slate-500">|</span>
            <span>Terms of Service</span>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-3">
            Terms &amp; <span class="bg-gradient-to-r from-[#00D4AA] via-cyan-300 to-blue-400 bg-clip-text text-transparent">Conditions</span>
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
            Effective Date: January 1, 2026 &bull; Last Revised: September 2026<br class="hidden sm:inline">
            Please read these terms carefully before accessing {{ $currentSite->name ?? 'ProExamTopics' }} services, study guides, and web simulator.
        </p>
    </div>
</x-site-b.hero>

<!-- Legal Content Section -->
<section class="py-16 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Summary Notice Card -->
        <div class="bg-blue-50/80 border border-blue-200/80 rounded-2xl p-6 mb-10 shadow-sm text-slate-700">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-1">Notice to Candidates and Users</h2>
                    <p class="text-xs sm:text-sm leading-relaxed text-slate-600">
                        By accessing, browsing, registering on, or purchasing digital preparation materials from 
                        <strong>{{ $currentSite->name ?? 'ProExamTopics' }}</strong> ("Website", "we", "us", "our"), 
                        you ("Customer", "User", "You") agree to be legally bound by these Terms and Conditions. 
                        If you do not agree to these terms, please discontinue your use of the website and services immediately.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-10 space-y-10 text-slate-700 leading-relaxed text-sm sm:text-base">
            
            <!-- Section 1 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    1. Educational Purpose &amp; Scope of Services
                </h2>
                <p>
                    {{ $currentSite->name ?? 'ProExamTopics' }} provides supplementary IT certification exam preparation materials, 
                    including downloadable PDF study guides, interactive web-based simulator engines, and community discussion boards. 
                    All materials on this website are designed strictly for educational review, self-assessment, and conceptual study purposes.
                </p>
                <p>
                    Our practice tests and simulator engines simulate real testing environments to help candidates test their knowledge, 
                    identify weak topic domains, and build time-management confidence. We make no guarantee that the exact questions on any live 
                    certification exam will mirror the materials provided, as vendor exams are refreshed frequently.
                </p>
            </div>

            <!-- Section 2 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    2. Third-Party Vendor Trademark Disclaimer
                </h2>
                <p>
                    {{ $currentSite->name ?? 'ProExamTopics' }} is an independent educational publishing and exam preparation platform. 
                    We are <strong>not affiliated with, associated with, sponsored by, or endorsed by</strong> Microsoft, Amazon Web Services (AWS), 
                    Cisco Systems, CompTIA, Google Cloud, Project Management Institute (PMI), ISACA, Salesforce, VMware, or any other certification 
                    authority, testing vendor, or sponsor.
                </p>
                <p>
                    All vendor names, brand emblems, certification titles, and exam codes (e.g., "AZ-900", "AWS Certified Solutions Architect", "Security+", "CCNA") 
                    referenced on this site are the registered trademarks and property of their respective owners. Their mention on this website 
                    is strictly for descriptive identification purposes under <strong>nominative fair use</strong> to identify which certification exam syllabus our study guides correspond to.
                </p>
            </div>

            <!-- Section 3 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    3. User Accounts &amp; Security
                </h2>
                <p>
                    To access purchased digital downloads, subscription updates, or the web simulator, you must create a registered account. 
                    You agree to:
                </p>
                <ul class="list-disc pl-6 space-y-1 text-slate-600">
                    <li>Provide accurate, current, and truthful registration information.</li>
                    <li>Maintain the strict confidentiality of your account login credentials and password.</li>
                    <li>Assume full responsibility for all activities conducted under your user credentials.</li>
                    <li>Notify us immediately of any unauthorized access, security breach, or compromised account credentials.</li>
                </ul>
                <p>
                    Account sharing, account resale, and simultaneous concurrent logins from unauthorized third parties are strictly prohibited. 
                    {{ $currentSite->name ?? 'ProExamTopics' }} reserves the right to suspend or terminate accounts that exhibit abnormal sharing patterns or security violations without prior notice or refund.
                </p>
            </div>

            <!-- Section 4 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    4. Intellectual Property &amp; Acceptable Use
                </h2>
                <p>
                    All original software code, proprietary question explanations, detailed answer rationales, web simulator technology, 
                    graphics, stylesheets, and editorial articles are the exclusive intellectual property of {{ $currentSite->name ?? 'ProExamTopics' }}.
                </p>
                <p><strong>Permitted Use:</strong> You are granted a limited, personal, non-exclusive, non-transferable, revocable license to access, view, and print study materials solely for your own individual, non-commercial exam preparation.</p>
                <p><strong>Prohibited Actions:</strong> You expressly agree that you will not:</p>
                <ul class="list-disc pl-6 space-y-1 text-slate-600">
                    <li>Distribute, sell, license, lease, upload to file-sharing networks, or publicly share any purchased PDF or simulator question set.</li>
                    <li>Use automated web crawlers, bots, scrapers, or data-extraction tools to harvest questions, images, or metadata from this platform.</li>
                    <li>Decompile, reverse-engineer, disassemble, or attempt to derive the source code of our online test engine software.</li>
                    <li>Post abusive, defamatory, harassing, or unlawful content in our community discussion forums.</li>
                </ul>
            </div>

            <!-- Section 5 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    5. Product Fulfillment &amp; 100% Pass Guarantee
                </h2>
                <p>
                    <strong>Instant Digital Delivery:</strong> All purchases are delivered digitally. Upon successful checkout confirmation, 
                    your study PDF files and simulator access are activated immediately in your student workspace.
                </p>
                <p>
                    <strong>Update Cycles:</strong> Depending on the license plan chosen (3 Months, 6 Months, or 12 Months Updates), 
                    your account will receive free access to any revised questions, syllabus updates, and errata corrections published during that active subscription period.
                </p>
                <p>
                    <strong>Money-Back Guarantee Policy:</strong> We stand firmly behind the quality of our exam materials with a 
                    <strong>100% Money-Back Guarantee</strong>. If you study our verified materials thoroughly and do not pass your official vendor certification exam on your first attempt:
                </p>
                <ul class="list-disc pl-6 space-y-1 text-slate-600">
                    <li>Contact our support team within 30 days of taking your official exam.</li>
                    <li>Provide your order number and an official copy of your vendor score report showing the candidate name matching your account.</li>
                    <li>Upon quick review, our billing team will issue a 100% refund or provide an alternate exam preparation bundle of your choice.</li>
                </ul>
            </div>

            <!-- Section 6 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    6. Disclaimer of Warranties &amp; Limitation of Liability
                </h2>
                <p>
                    This website, along with all study guides, software simulators, and digital content, is provided on an 
                    <strong>"AS IS"</strong> and <strong>"AS AVAILABLE"</strong> basis without warranties of any kind, whether express, implied, statutory, or otherwise, 
                    including but not limited to the implied warranties of merchantability, fitness for a particular purpose, and non-infringement.
                </p>
                <p>
                    Under no circumstances shall {{ $currentSite->name ?? 'ProExamTopics' }}, its founders, directors, employees, or technical partners be liable for any indirect, 
                    incidental, consequential, special, or punitive damages (including loss of exam fees, lost employment opportunities, or data loss) 
                    arising out of or related to your use of this platform. In any event, our total aggregate liability shall not exceed the total amount 
                    actually paid by you to {{ $currentSite->name ?? 'ProExamTopics' }} in the six (6) months preceding the claim.
                </p>
            </div>

            <!-- Section 7 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    7. External Links &amp; Third-Party Services
                </h2>
                <p>
                    Our site may contain links to external third-party websites or services (such as payment gateways, vendor syllabus docs, or community resources). 
                    These links are provided for your convenience only. We have no control over the content, security, or privacy practices of third-party websites 
                    and accept no responsibility for them.
                </p>
            </div>

            <!-- Section 8 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    8. Governing Law &amp; Severability
                </h2>
                <p>
                    These Terms and Conditions shall be governed by and construed in accordance with applicable laws without regard to conflict of law principles. 
                    If any provision of these Terms is deemed unlawful, void, or unenforceable by a court of competent jurisdiction, that specific provision shall 
                    be deemed severable and shall not affect the validity and enforceability of any remaining provisions.
                </p>
            </div>

            <!-- Section 9 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    9. Contact &amp; Legal Inquiries
                </h2>
                <p>
                    If you have questions, comments, or legal inquiries regarding these Terms and Conditions, please contact our compliance desk:
                </p>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs sm:text-sm font-mono-code space-y-1 text-slate-700">
                    <p><strong>Entity:</strong> {{ $currentSite->name ?? 'ProExamTopics' }} Legal Compliance</p>
                    @php
                        $supportEmail = $currentSite->contact_email ?? ($globalSettings['support_email'] ?? ($globalSettings['contact_email'] ?? 'support@proexamtopics.com'));
                    @endphp
                    <p><strong>Email:</strong> <a href="mailto:{{ $supportEmail }}" class="text-blue-600 hover:underline">{{ $supportEmail }}</a></p>
                    <p><strong>Desk:</strong> <a href="{{ url('/contact') }}" class="text-blue-600 hover:underline">{{ url('/contact') }}</a></p>
                </div>
            </div>

        </div>

    </div>
</section>
@endsection
