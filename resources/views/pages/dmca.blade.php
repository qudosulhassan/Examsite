@extends('layouts.public')

@section('title', 'DMCA Copyright Policy - ExamTopicsBase')

@section('content')
<!-- Header Hero -->
<section class="relative bg-gradient-to-b from-[#0A1628] via-[#0D1F38] to-[#0A1628] text-white py-16 lg:py-20 border-b border-white/10 overflow-hidden">
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-cyan/10 rounded-full filter blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-purple-600/10 rounded-full filter blur-[120px] pointer-events-none"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan/10 border border-cyan/30 text-cyan text-xs font-bold uppercase tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Intellectual Property &amp; Legal Compliance
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white">
            Digital Millennium Copyright Act (DMCA) Policy
        </h1>
        <p class="text-sm sm:text-base text-gray-300 max-w-2xl mx-auto leading-relaxed">
            ExamTopicsBase respects intellectual property rights and strictly adheres to Title 17, United States Code, Section 512.
        </p>
        <div class="flex items-center justify-center gap-4 text-xs text-gray-400 pt-2 font-medium">
            <span>Designated Agent: dmca@examtopicsbase.com</span>
            <span>&bull;</span>
            <span>Fast Review Turnaround (24-48 Business Hours)</span>
        </div>
    </div>
</section>

<!-- Main Policy Body -->
<section class="py-14 lg:py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 sm:p-12 space-y-12 text-gray-700 leading-relaxed">

            <!-- Educational Fair Use Disclaimer Banner -->
            <div class="p-6 rounded-xl bg-slate-900 text-white border-l-4 border-cyan space-y-3">
                <div class="flex items-center gap-2 text-cyan font-bold text-sm uppercase tracking-wider">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Important Nominative Fair Use &amp; Trademark Disclaimer
                </div>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    <strong>ExamTopicsBase</strong> is an independent educational prep platform developed by certified engineers. All third-party certification brand names, exam codes, and trade names (including but not limited to <em>Amazon Web Services (AWS), Microsoft, Cisco, CompTIA, Google Cloud, VMware, ISACA, PMI, ITIL, Salesforce</em>) referenced on this platform are the sole trademarks of their respective owners.
                </p>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    ExamTopicsBase is not affiliated with, endorsed by, or sponsored by any certification vendor. Any use of vendor trademarks or exam identifiers is strictly for nominative, educational, and descriptive fair-use purposes to identify the subject matter of our independent practice materials.
                </p>
            </div>

            <!-- Section 1: Copyright Commitment -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">1</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Our Intellectual Property Commitment</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    ExamTopicsBase operates in full compliance with the Digital Millennium Copyright Act of 1998 (17 U.S.C. § 512). It is our strict policy to respond promptly to clear, formal notices of alleged copyright infringement and, where appropriate, expeditiously remove or disable access to materials claimed to be infringing.
                </p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    We maintain a repeat-infringer policy under which registered accounts, forum contributors, or subscribers repeatedly associated with infringing activity will have their access terminated without refund.
                </p>
            </div>

            <!-- Section 2: How to File a Valid DMCA Notice -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">2</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Submitting a DMCA Takedown Notice</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    If you are a copyright owner or an agent authorized to act on behalf of one, you may submit a formal notification pursuant to 17 U.S.C. § 512(c)(3). To ensure prompt processing, your written notice must include all of the following six statutory elements:
                </p>

                <div class="space-y-3 pt-2">
                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-start gap-3 text-xs sm:text-sm">
                        <span class="font-bold text-cyan text-base">01</span>
                        <div>
                            <strong class="text-navy block">Physical or Electronic Signature:</strong>
                            A physical or electronic signature of the copyright owner or a person authorized to act on their behalf.
                        </div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-start gap-3 text-xs sm:text-sm">
                        <span class="font-bold text-cyan text-base">02</span>
                        <div>
                            <strong class="text-navy block">Identification of the Copyrighted Work:</strong>
                            A clear description or copy of the copyrighted work that you claim has been infringed, or a representative list if multiple works are covered.
                        </div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-start gap-3 text-xs sm:text-sm">
                        <span class="font-bold text-cyan text-base">03</span>
                        <div>
                            <strong class="text-navy block">Specific Location on ExamTopicsBase:</strong>
                            The exact URL(s) or page locations where the allegedly infringing material appears, enabling us to locate and review it immediately.
                        </div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-start gap-3 text-xs sm:text-sm">
                        <span class="font-bold text-cyan text-base">04</span>
                        <div>
                            <strong class="text-navy block">Complainant Contact Information:</strong>
                            Your legal name, mailing address, telephone number, and official email address.
                        </div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-start gap-3 text-xs sm:text-sm">
                        <span class="font-bold text-cyan text-base">05</span>
                        <div>
                            <strong class="text-navy block">Good Faith Statement:</strong>
                            A statement that you have a good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law.
                        </div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-start gap-3 text-xs sm:text-sm">
                        <span class="font-bold text-cyan text-base">06</span>
                        <div>
                            <strong class="text-navy block">Statement Under Penalty of Perjury:</strong>
                            A statement that the information in the notification is accurate, and under penalty of perjury, that you are authorized to act on behalf of the owner of the exclusive right that is allegedly infringed.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Designated Agent Box -->
            <div class="p-6 rounded-2xl bg-gradient-to-br from-navy to-[#0F223D] text-white space-y-4">
                <div class="flex items-center justify-between border-b border-white/10 pb-3">
                    <h3 class="text-base font-bold text-cyan">Designated DMCA Copyright Agent</h3>
                    <span class="text-[10px] uppercase font-bold bg-cyan/20 text-cyan px-2.5 py-0.5 rounded-full">Official Channel</span>
                </div>
                <p class="text-xs sm:text-sm text-gray-300">
                    Send all formal DMCA notices or inquiries to our designated legal recipient:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                    <div>
                        <span class="text-gray-400 block text-[11px] uppercase font-bold">Email (Preferred)</span>
                        <a href="mailto:dmca@examtopicsbase.com" class="text-cyan font-bold hover:underline text-sm sm:text-base">dmca@examtopicsbase.com</a>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px] uppercase font-bold">Alternative Support</span>
                        <a href="mailto:support@examtopicsbase.com" class="text-gray-200 hover:underline">support@examtopicsbase.com</a>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-gray-400 block text-[11px] uppercase font-bold">Subject Line Standard</span>
                        <code class="text-xs font-mono bg-black/40 px-2 py-1 rounded text-cyan">DMCA Takedown Notice - [Content URL or Topic]</code>
                    </div>
                </div>
            </div>

            <!-- Section 3: Counter-Notification -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">3</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Counter-Notification Procedure</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    If you believe your content or resource was removed or disabled by mistake or misidentification, you may submit a formal counter-notification pursuant to 17 U.S.C. § 512(g)(3). A valid counter-notification must be in writing and contain:
                </p>
                <ul class="list-disc list-inside text-xs sm:text-sm text-gray-600 space-y-2">
                    <li>Your physical or electronic signature.</li>
                    <li>Identification of the material that was removed and the location where it previously appeared.</li>
                    <li>A statement under penalty of perjury that you have a good faith belief that the material was removed as a result of mistake or misidentification.</li>
                    <li>Your name, address, telephone number, and a statement that you consent to the jurisdiction of the Federal District Court for the judicial district in which your address is located (or if outside the U.S., for any judicial district in which ExamTopicsBase may be found), and that you will accept service of process from the person who provided the original notification.</li>
                </ul>
                <p class="text-xs text-gray-500 italic">
                    Upon receipt of a valid counter-notification, we will forward it to the original complaining party. If the complaining party does not file a court action within 10–14 business days, the removed material may be restored at our discretion.
                </p>
            </div>

            <!-- Section 4: Warning on False Claims -->
            <div class="p-4 rounded-xl bg-amber-50 border-l-4 border-amber-500 text-xs sm:text-sm text-amber-900 space-y-1">
                <strong class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Legal Liability Warning (17 U.S.C. § 512(f))
                </strong>
                <p class="text-amber-800 leading-relaxed text-xs">
                    Please be aware that under Section 512(f) of the DMCA, any person who knowingly materially misrepresents that material or activity is infringing (or was removed by mistake) may be subject to liability for statutory damages, court costs, and attorneys' fees.
                </p>
            </div>

        </div>
    </div>
</section>
@endsection
