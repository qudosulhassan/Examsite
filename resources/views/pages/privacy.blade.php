@extends('layouts.public')

@section('title', 'Privacy Policy - ExamTopicsBase')

@section('content')
<!-- Header Hero -->
<section class="relative bg-navy text-white py-16 lg:py-20 border-b border-white/10 overflow-hidden" style="background: linear-gradient(180deg, #0A1628 0%, #0D1F38 50%, #0A1628 100%) !important; background-color: #0A1628 !important; color: #ffffff !important;">
    <!-- Ambient glow -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-cyan/10 rounded-full filter blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full filter blur-[120px] pointer-events-none"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan/10 border border-cyan/30 text-cyan text-xs font-bold uppercase tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Candidate Privacy &amp; Data Protection
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white" style="color: #ffffff !important;">
            Privacy Policy
        </h1>
        <p class="text-sm sm:text-base text-gray-300 max-w-2xl mx-auto leading-relaxed" style="color: #cbd5e1 !important;">
            Your trust is our highest priority. Learn how ExamTopicsBase safeguards your personal information, protects payment transactions, and upholds your global data privacy rights.
        </p>
        <div class="flex items-center justify-center gap-4 text-xs text-gray-400 pt-2 font-medium" style="color: #94a3b8 !important;">
            <span>Effective Date: January 1, 2026</span>
            <span>&bull;</span>
            <span>Last Updated: September 2026</span>
            <span>&bull;</span>
            <span>GDPR &amp; CCPA Compliant</span>
        </div>
    </div>
</section>

<!-- Trust Highlights Grid -->
<section class="border-b border-white/5 py-8" style="background-color: #07101E !important; color: #ffffff !important;">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl border border-white/10 flex items-start gap-3" style="background-color: rgba(255, 255, 255, 0.04) !important; border-color: rgba(255, 255, 255, 0.1) !important;">
                <div class="w-8 h-8 rounded-lg bg-cyan/20 text-cyan flex items-center justify-center flex-shrink-0 font-bold">✓</div>
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider" style="color: #ffffff !important;">Zero Data Selling</h4>
                    <p class="text-xs mt-0.5" style="color: #94a3b8 !important;">We never sell, rent, or trade candidate details to third parties.</p>
                </div>
            </div>
            <div class="p-4 rounded-xl border border-white/10 flex items-start gap-3" style="background-color: rgba(255, 255, 255, 0.04) !important; border-color: rgba(255, 255, 255, 0.1) !important;">
                <div class="w-8 h-8 rounded-lg bg-cyan/20 text-cyan flex items-center justify-center flex-shrink-0 font-bold">🔒</div>
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider" style="color: #ffffff !important;">PCI-DSS Payments</h4>
                    <p class="text-xs mt-0.5" style="color: #94a3b8 !important;">Card processing handled by Stripe &amp; PayPal. We never store raw card numbers.</p>
                </div>
            </div>
            <div class="p-4 rounded-xl border border-white/10 flex items-start gap-3" style="background-color: rgba(255, 255, 255, 0.04) !important; border-color: rgba(255, 255, 255, 0.1) !important;">
                <div class="w-8 h-8 rounded-lg bg-cyan/20 text-cyan flex items-center justify-center flex-shrink-0 font-bold">🛡️</div>
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider" style="color: #ffffff !important;">256-Bit SSL/TLS</h4>
                    <p class="text-xs mt-0.5" style="color: #94a3b8 !important;">Bank-grade end-to-end encryption for all sessions and test engines.</p>
                </div>
            </div>
            <div class="p-4 rounded-xl border border-white/10 flex items-start gap-3" style="background-color: rgba(255, 255, 255, 0.04) !important; border-color: rgba(255, 255, 255, 0.1) !important;">
                <div class="w-8 h-8 rounded-lg bg-cyan/20 text-cyan flex items-center justify-center flex-shrink-0 font-bold">👤</div>
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider" style="color: #ffffff !important;">Full User Control</h4>
                    <p class="text-xs mt-0.5" style="color: #94a3b8 !important;">Export, edit, or request complete deletion of your account at any time.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Policy Body -->
<section class="py-14 lg:py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 sm:p-12 space-y-12 text-gray-700 leading-relaxed">
            
            <!-- Quick Summary Box -->
            <div class="p-5 rounded-xl bg-cyan/5 border-l-4 border-cyan text-sm space-y-2">
                <h3 class="font-bold text-navy flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Summary in Plain English
                </h3>
                <p class="text-gray-600 leading-relaxed text-xs sm:text-sm">
                    At <strong>ExamTopicsBase</strong>, we believe privacy policies shouldn't require a law degree to understand. We only collect the bare minimum information needed to set up your account, process orders securely, deliver your practice exams, and provide customer support. We respect your inbox, never spam, and give you complete control over your profile.
                </p>
            </div>

            <!-- Section 1 -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">1</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Introduction &amp; Scope</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    This Privacy Policy applies to <strong>ExamTopicsBase</strong> (accessible at <a href="https://examtopicsbase.com" class="text-cyan font-bold hover:underline">https://examtopicsbase.com</a>), including all subdomains, browser-based interactive test engines, mobile-accessible platforms, and digital study resources. By registering an account, purchasing practice question sets, or browsing our website, you consent to the practices described in this policy.
                </p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    If you do not agree with any aspect of this policy, please discontinue use of our site. For any privacy inquiries, reach our Data Protection Officer anytime at <a href="mailto:support@examtopicsbase.com" class="text-cyan font-bold hover:underline">support@examtopicsbase.com</a>.
                </p>
            </div>

            <!-- Section 2 -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">2</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Information We Collect</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    We collect information across three main categories to provide a seamless certification preparation experience:
                </p>

                <div class="space-y-4 pl-2">
                    <div class="border-l-2 border-cyan/40 pl-4 py-1">
                        <h3 class="text-sm font-bold text-navy uppercase tracking-wider">A. Information You Voluntarily Provide</h3>
                        <ul class="list-disc list-inside text-xs sm:text-sm text-gray-600 space-y-1.5 mt-1.5">
                            <li><strong>Account Registration:</strong> Name, email address, password hash, and optional profile settings.</li>
                            <li><strong>Order &amp; Billing Data:</strong> Billing contact details, billing country, selected payment method (Credit Card, Debit Card, PayPal). <em>Note: All payment card details are transmitted directly to PCI-DSS Level 1 certified payment processors (Stripe / PayPal) and are never stored or seen by ExamTopicsBase servers.</em></li>
                            <li><strong>Support Inquiries:</strong> Messages, feedback, score reports submitted for guarantee claims, and communication history.</li>
                        </ul>
                    </div>

                    <div class="border-l-2 border-cyan/40 pl-4 py-1">
                        <h3 class="text-sm font-bold text-navy uppercase tracking-wider">B. Automatic Usage &amp; Test Engine Telemetry</h3>
                        <ul class="list-disc list-inside text-xs sm:text-sm text-gray-600 space-y-1.5 mt-1.5">
                            <li><strong>Practice Exam Progress:</strong> Question attempts, answers chosen, flagged questions, completion timestamps, and percentage scores used solely to generate your personal study analytics.</li>
                            <li><strong>Technical Data:</strong> IP address, browser type and version, device hardware profile, operating system, session IDs, and referral URLs.</li>
                        </ul>
                    </div>

                    <div class="border-l-2 border-cyan/40 pl-4 py-1">
                        <h3 class="text-sm font-bold text-navy uppercase tracking-wider">C. Cookies &amp; Local Storage</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mt-1">
                            We use standard HTTP cookies and local storage tokens to keep you logged in securely, remember your shopping cart items, track test engine session state, and protect against Cross-Site Request Forgery (CSRF).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 3 -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">3</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">How We Use Your Information</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    We process candidate data strictly for legitimate educational, operational, and commercial purposes:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-150">
                        <strong class="text-navy block mb-1">Instant Digital Fulfillment</strong>
                        Provisioning immediate access to purchased practice exam study guides and web test engines.
                    </div>
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-150">
                        <strong class="text-navy block mb-1">Customer Support &amp; Guarantees</strong>
                        Responding to candidate technical tickets and processing 100% money-back guarantee refund claims.
                    </div>
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-150">
                        <strong class="text-navy block mb-1">System Security &amp; Fraud Prevention</strong>
                        Preventing unauthorized account sharing, automated scraping, brute-force intrusions, and fraudulent transactions.
                    </div>
                    <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-150">
                        <strong class="text-navy block mb-1">Transactional Notifications</strong>
                        Sending purchase receipts, password reset links, and exam question bank update alerts.
                    </div>
                </div>
            </div>

            <!-- Section 4 -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">4</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Third-Party Service Providers</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    We collaborate with industry-leading cloud infrastructure and service partners who adhere to the strictest security standards. These partners only process your information to perform specific tasks on our behalf:
                </p>
                <ul class="list-disc list-inside text-xs sm:text-sm text-gray-600 space-y-2">
                    <li><strong>Stripe &amp; PayPal:</strong> Encrypted payment tokenization and processing under PCI-DSS Level 1 compliance.</li>
                    <li><strong>Cloudflare &amp; Cloudflare R2:</strong> Global Content Delivery Network (CDN), DDoS mitigation, and secure encrypted object storage for downloadable preparation materials.</li>
                    <li><strong>Transactional Email Gateways:</strong> Delivering invoice PDFs, account verification, and critical service notifications.</li>
                </ul>
            </div>

            <!-- Section 5 -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">5</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Your Rights Under GDPR &amp; CCPA</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Regardless of your geographic location, ExamTopicsBase grants all registered candidates comprehensive privacy controls:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm text-gray-600">
                    <div class="p-3 rounded-lg border border-gray-200">
                        <strong class="text-navy block">Right to Access</strong>
                        You may request a copy of all personal records we hold about your account.
                    </div>
                    <div class="p-3 rounded-lg border border-gray-200">
                        <strong class="text-navy block">Right to Rectification</strong>
                        You can update your name, email, or profile settings anytime via your User Dashboard.
                    </div>
                    <div class="p-3 rounded-lg border border-gray-200">
                        <strong class="text-navy block">Right to Erasure ("Be Forgotten")</strong>
                        Request complete deletion of your account, history, and test records upon request.
                    </div>
                    <div class="p-3 rounded-lg border border-gray-200">
                        <strong class="text-navy block">Right to Restrict &amp; Opt-Out</strong>
                        Opt out of any marketing newsletters with one single click via the unsubscribe link.
                    </div>
                </div>
                <p class="text-xs text-gray-500 italic">
                    To exercise any of these rights, email us at <a href="mailto:support@examtopicsbase.com" class="text-cyan font-bold hover:underline">support@examtopicsbase.com</a>. We process all verified requests within 30 days without charge.
                </p>
            </div>

            <!-- Section 6 -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">6</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Data Retention &amp; Security</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    We implement defense-in-depth technical safeguards including 256-bit SSL/TLS transmission encryption, encrypted PostgreSQL databases, password hashing (Argon2id/Bcrypt), role-based administrative access restrictions, and regular vulnerability audits.
                </p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    We retain candidate account records for as long as your account is active to maintain your purchased practice test access. If you request account closure, your personally identifiable information is purged within 30 days, retaining only anonymized transaction records required by financial tax laws.
                </p>
            </div>

            <!-- Section 7 -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">7</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Children's Online Privacy</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    ExamTopicsBase is designed for IT certification professionals, college students, and adult learners. We do not knowingly collect or solicit personal information from children under the age of 16. If we learn that we have collected information from a child under 16, we will immediately delete that information from our servers.
                </p>
            </div>

            <!-- Section 8 -->
            <div class="space-y-4 pt-4 border-t border-gray-150">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-navy text-white text-xs font-black flex items-center justify-center">8</span>
                    <h2 class="text-xl sm:text-2xl font-black text-navy tracking-tight">Contact Our Privacy Team</h2>
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">
                    If you have questions, comments, or concerns about this Privacy Policy, please contact our support desk:
                </p>
                <div class="p-4 rounded-xl bg-navy text-white text-xs sm:text-sm space-y-2">
                    <div class="font-bold text-cyan text-base">ExamTopicsBase Privacy Desk</div>
                    <div><strong>Email:</strong> <a href="mailto:support@examtopicsbase.com" class="text-cyan hover:underline">support@examtopicsbase.com</a></div>
                    <div><strong>Website:</strong> <a href="https://examtopicsbase.com" class="text-cyan hover:underline">https://examtopicsbase.com</a></div>
                    <div><strong>Response Guarantee:</strong> Inquiries answered within 24 business hours.</div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
