@extends('layouts.public')

@section('title', 'DMCA & Copyright Policy | ' . ($currentSite->name ?? ($globalSettings['site_name'] ?? config('app.name', 'ProExamTopics'))))
@section('meta_description', 'Review the DMCA and Copyright Infringement notification procedures for ' . ($currentSite->name ?? 'ProExamTopics') . '. Learn how to submit copyright takedown notices and counter-notifications.')

@section('content')
<!-- DMCA Hero Header -->
<x-site-b.hero variant="legal" glowColor="blue" padding="compact">
    <div class="max-w-4xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-4 backdrop-blur-sm shadow-sm">
            <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
            <span class="font-mono-code text-[#00D4AA] font-bold">INTELLECTUAL PROPERTY COMPLIANCE</span>
            <span class="text-slate-500">|</span>
            <span>17 U.S.C. &sect; 512</span>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-3">
            DMCA &amp; <span class="bg-gradient-to-r from-blue-400 via-cyan-300 to-[#00D4AA] bg-clip-text text-transparent">Copyright Policy</span>
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
            {{ $currentSite->name ?? 'ProExamTopics' }} respects intellectual property rights and adheres strictly to the 
            Digital Millennium Copyright Act (DMCA) and international copyright protection regulations.
        </p>
    </div>
</x-site-b.hero>

<!-- DMCA Policy Content Section -->
<section class="py-16 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Informational Banner -->
        <div class="bg-emerald-50/80 border border-emerald-200/80 rounded-2xl p-6 mb-10 shadow-sm text-slate-700">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-1">Commitment to Copyright Protection</h2>
                    <p class="text-xs sm:text-sm leading-relaxed text-slate-600">
                        {{ $currentSite->name ?? 'ProExamTopics' }} operates in full compliance with the safe harbor provisions of 
                        the Digital Millennium Copyright Act (17 U.S.C. &sect; 512). It is our policy to respond promptly to clear, 
                        valid notices of alleged copyright infringement and, where appropriate, disable access to or remove infringing materials.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-10 space-y-10 text-slate-700 leading-relaxed text-sm sm:text-base">

            <!-- Section 1 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    1. Overview &amp; Community Contributions
                </h2>
                <p>
                    {{ $currentSite->name ?? 'ProExamTopics' }} is an online educational resource and testing simulator platform. 
                    Portions of our platform—such as forum discussions, community answer votes, candidate study notes, and feedback comments—are 
                    contributed by independent users and candidates worldwide.
                </p>
                <p>
                    While we maintain proactive quality filters and strict terms against publishing unauthorized content, 
                    if you believe that any material residing on or accessible through our website infringes upon a copyright you own or control, 
                    please notify our Designated Copyright Agent following the statutory procedure outlined below.
                </p>
            </div>

            <!-- Section 2 -->
            <div class="space-y-4">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    2. Requirements for Filing a DMCA Notice (17 U.S.C. &sect; 512(c)(3))
                </h2>
                <p>
                    To ensure your notice is valid and actionable under federal law, the notification must be a written communication 
                    containing all of the following elements:
                </p>
                
                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Physical or Electronic Signature</h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">A physical or electronic signature of a person authorized to act on behalf of the owner of the exclusive copyright right that is allegedly infringed.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Identification of Copyrighted Work</h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">Clear identification of the copyrighted work claimed to have been infringed, or, if multiple works at a single site are covered by a single notification, a representative list of such works.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Identification of the Infringing Material &amp; URLs</h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">Identification of the material that is claimed to be infringing or to be the subject of infringing activity, including sufficient detail (exact URLs, exam code, question number, or screenshot) to permit us to locate the material.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">4</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Complainant's Contact Information</h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">Information reasonably sufficient to permit us to contact you, such as your full legal name, company/organization, physical mailing address, telephone number, and active corporate email address.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">5</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Good Faith Belief Statement</h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">A statement that you have a <em>good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law</em>.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">6</span>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Accuracy &amp; Penalty of Perjury Statement</h3>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">A statement that <em>the information in the notification is accurate, and under penalty of perjury, that you are authorized to act on behalf of the owner of an exclusive right that is allegedly infringed</em>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    3. Takedown &amp; Notification Protocol
                </h2>
                <p>
                    Upon receipt of a valid and complete DMCA notice meeting all statutory requirements:
                </p>
                <ul class="list-disc pl-6 space-y-1.5 text-slate-600">
                    <li>Our compliance team will expeditiously investigate the claim and disable access to or remove the identified material.</li>
                    <li>We will take reasonable steps to promptly notify the user who posted or contributed the disputed material that access has been disabled.</li>
                    <li>Under appropriate circumstances, we will provide the affected user with a copy of your notice (excluding confidential contact details where requested) to enable the user to respond via a formal Counter-Notification.</li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    4. Counter-Notification Procedure (17 U.S.C. &sect; 512(g)(3))
                </h2>
                <p>
                    If a content contributor believes that their material was removed or disabled by mistake or misidentification, 
                    the contributor may submit a written Counter-Notification to our Designated Agent containing:
                </p>
                <ul class="list-disc pl-6 space-y-1 text-slate-600">
                    <li>The contributor's physical or electronic signature.</li>
                    <li>Identification of the material that has been removed or disabled and the location where it appeared before it was removed.</li>
                    <li>A statement under penalty of perjury that the contributor has a good faith belief that the material was removed or disabled as a result of mistake or misidentification.</li>
                    <li>The contributor's legal name, physical address, and telephone number, and a statement consenting to the jurisdiction of the Federal District Court for the judicial district in which the address is located (or if outside the United States, for any judicial district in which the service provider may be found), and agreeing to accept service of process from the person who provided the original notification.</li>
                </ul>
                <p>
                    If a valid Counter-Notification is received, we will forward a copy to the original complaining party. 
                    Unless the copyright owner files a court action seeking an order against the contributor within 10–14 business days, 
                    the removed material may be restored at our discretion.
                </p>
            </div>

            <!-- Section 5 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    5. Repeat Infringer Policy
                </h2>
                <p>
                    In accordance with Section 512(i)(1)(A) of the DMCA, {{ $currentSite->name ?? 'ProExamTopics' }} maintains a strict 
                    policy that provides for the termination, in appropriate circumstances, of account holders or subscribers who are deemed 
                    to be repeat infringers. We reserve the right to limit access to the platform and immediately terminate the accounts of any 
                    users who infringe upon the intellectual property rights of others.
                </p>
            </div>

            <!-- Section 6 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    6. Nominative Fair Use &amp; Trademark Clarification
                </h2>
                <p>
                    Certification codes, exam names, and provider trademarks (such as Microsoft&reg;, AWS&reg;, Cisco&reg;, CompTIA&reg;, PMI&reg;, etc.) 
                    are mentioned on {{ $currentSite->name ?? 'ProExamTopics' }} solely to describe the scope, subject matter, and curriculum of our 
                    independent practice question guides under the doctrine of nominative fair use. We do not claim any proprietary rights to 
                    third-party trademarks.
                </p>
            </div>

            <!-- Section 7 -->
            <div class="space-y-3">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 border-b border-slate-100 pb-2">
                    7. Designated Copyright Agent Contact
                </h2>
                <p>
                    Please send all formal DMCA notices, counter-notices, and copyright compliance inquiries to our Designated Agent:
                </p>

                @php
                    $supportEmail = $currentSite->contact_email ?? ($globalSettings['support_email'] ?? ($globalSettings['contact_email'] ?? 'support@proexamtopics.com'));
                @endphp

                <div class="rounded-2xl p-6 bg-slate-900 text-white border border-slate-800 shadow-md space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#00D4AA]/20 text-[#00D4AA] flex items-center justify-center font-bold">
                            &copy;
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-white">{{ $currentSite->name ?? 'ProExamTopics' }} Legal &amp; DMCA Agent</h3>
                            <p class="text-xs text-slate-400">Intellectual Property Compliance Desk</p>
                        </div>
                    </div>
                    <div class="text-xs sm:text-sm font-mono-code text-slate-300 space-y-1.5 pt-2 border-t border-slate-800">
                        <p><strong>Department:</strong> Copyright &amp; DMCA Legal Compliance</p>
                        <p><strong>Platform:</strong> {{ $currentSite->name ?? 'ProExamTopics' }} ({{ url('/') }})</p>
                        <p><strong>Email:</strong> <a href="mailto:{{ $supportEmail }}?subject=DMCA%20Notice" class="text-cyan-400 hover:underline font-bold">{{ $supportEmail }}</a></p>
                        <p><strong>Subject Line:</strong> <span class="text-amber-300 font-bold">DMCA Notice - [Exam Code / URL]</span></p>
                        <p><strong>Average Response Window:</strong> 24 to 48 business hours</p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>
@endsection
