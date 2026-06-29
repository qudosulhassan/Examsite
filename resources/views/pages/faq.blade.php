@extends('layouts.public')

@section('title', 'Frequently Asked Questions (FAQ) - ExamsNinja')

@section('seo_tags')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Are these real exam questions?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. Our practice test materials are compiled by certified IT professionals who collect and verify real questions from actual exams. We review and update our databases monthly to ensure maximum accuracy."
      }
    },
    {
      "@type": "Question",
      "name": "How are files delivered?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "All deliveries are instant. As soon as your payment is verified via Stripe or PayPal, you will be redirected back to your Student Portal dashboard, and an email confirmation will be sent. You can download your PDFs directly from the dashboard."
      }
    },
    {
      "@type": "Question",
      "name": "How many times can I download?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Each single guide purchase grants you a maximum of 3 download attempts from the dashboard. If you exceed this limit due to device changes or corruption, please contact our support team to request additional attempts."
      }
    },
    {
      "@type": "Question",
      "name": "Do you offer refunds?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, we offer a 100% money-back guarantee. If you study our materials and fail your certification exam within 30 days of purchase, send us a copy of your official score report, and we will issue a full refund."
      }
    },
    {
      "@type": "Question",
      "name": "How long are free updates valid?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Every guide purchase includes 90 days of free updates. If the vendor updates the exam questions or blueprints within this timeframe, the updated PDF will be uploaded to our R2 storage, and you can download it for free."
      }
    },
    {
      "@type": "Question",
      "name": "Is my payment secure?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Absolutely. We do not store or process your credit card details on our servers. All transactions are handled securely by Stripe or PayPal using industry-grade SSL encryption and PCI-compliant gateways."
      }
    },
    {
      "@type": "Question",
      "name": "Can I use the test engine on my phone?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. Our interactive test engine is 100% web-based and responsive. It works perfectly on all smartphones, tablets, laptops, and desktop computers without installing any software or app stores."
      }
    },
    {
      "@type": "Question",
      "name": "What if I fail my exam?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "If you fail the exam, contact our support team at support@examsninja.com with your order details and a copy of your score report. We will process your 100% refund immediately or provide alternative study guides for free, depending on your choice."
      }
    }
  ]
}
</script>
@endsection

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-16 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <div class="mb-6 flex justify-center">
            <x-breadcrumbs :links="[
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'FAQ', 'url' => '']
            ]" />
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
            Frequently Asked Questions
        </h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Got questions about our exam dumps, PDF guides, or the test engine? Find answers below.
        </p>
    </div>
</section>

<!-- Accordion Section -->
<section class="py-16 bg-white" x-data="{ activeIndex: null }">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        
        <!-- FAQ 1 -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button @click="activeIndex = activeIndex === 1 ? null : 1" 
                    class="w-full flex justify-between items-center p-5 text-left font-bold text-navy bg-gray-50 focus:outline-none">
                <span>Are these real exam questions?</span>
                <svg class="h-5 w-5 transition duration-200" :class="activeIndex === 1 ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="activeIndex === 1" class="p-5 text-sm text-gray-600 leading-relaxed border-t border-gray-250" style="display: none;">
                Yes. Our practice test materials are compiled by certified IT professionals who collect and verify real questions from actual exams. We review and update our databases monthly to ensure maximum accuracy.
            </div>
        </div>

        <!-- FAQ 2 -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button @click="activeIndex = activeIndex === 2 ? null : 2" 
                    class="w-full flex justify-between items-center p-5 text-left font-bold text-navy bg-gray-50 focus:outline-none">
                <span>How are files delivered?</span>
                <svg class="h-5 w-5 transition duration-200" :class="activeIndex === 2 ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="activeIndex === 2" class="p-5 text-sm text-gray-600 leading-relaxed border-t border-gray-250" style="display: none;">
                All deliveries are instant. As soon as your payment is verified via Stripe or PayPal, you will be redirected back to your Student Portal dashboard, and an email confirmation will be sent. You can download your PDFs directly from the dashboard.
            </div>
        </div>

        <!-- FAQ 3 -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button @click="activeIndex = activeIndex === 3 ? null : 3" 
                    class="w-full flex justify-between items-center p-5 text-left font-bold text-navy bg-gray-50 focus:outline-none">
                <span>How many times can I download?</span>
                <svg class="h-5 w-5 transition duration-200" :class="activeIndex === 3 ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="activeIndex === 3" class="p-5 text-sm text-gray-600 leading-relaxed border-t border-gray-250" style="display: none;">
                Each single guide purchase grants you a maximum of 3 download attempts from the dashboard. If you exceed this limit due to device changes or corruption, please contact our support team to request additional attempts.
            </div>
        </div>

        <!-- FAQ 4 -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button @click="activeIndex = activeIndex === 4 ? null : 4" 
                    class="w-full flex justify-between items-center p-5 text-left font-bold text-navy bg-gray-50 focus:outline-none">
                <span>Do you offer refunds?</span>
                <svg class="h-5 w-5 transition duration-200" :class="activeIndex === 4 ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="activeIndex === 4" class="p-5 text-sm text-gray-600 leading-relaxed border-t border-gray-250" style="display: none;">
                Yes, we offer a 100% money-back guarantee. If you study our materials and fail your certification exam within 30 days of purchase, send us a copy of your official score report, and we will issue a full refund.
            </div>
        </div>

        <!-- FAQ 5 -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button @click="activeIndex = activeIndex === 5 ? null : 5" 
                    class="w-full flex justify-between items-center p-5 text-left font-bold text-navy bg-gray-50 focus:outline-none">
                <span>How long are free updates valid?</span>
                <svg class="h-5 w-5 transition duration-200" :class="activeIndex === 5 ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="activeIndex === 5" class="p-5 text-sm text-gray-600 leading-relaxed border-t border-gray-250" style="display: none;">
                Every guide purchase includes 90 days of free updates. If the vendor updates the exam questions or blueprints within this timeframe, the updated PDF will be uploaded to our R2 storage, and you can download it for free.
            </div>
        </div>

        <!-- FAQ 6 -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button @click="activeIndex = activeIndex === 6 ? null : 6" 
                    class="w-full flex justify-between items-center p-5 text-left font-bold text-navy bg-gray-50 focus:outline-none">
                <span>Is my payment secure?</span>
                <svg class="h-5 w-5 transition duration-200" :class="activeIndex === 6 ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="activeIndex === 6" class="p-5 text-sm text-gray-600 leading-relaxed border-t border-gray-250" style="display: none;">
                Absolutely. We do not store or process your credit card details on our servers. All transactions are handled securely by Stripe or PayPal using industry-grade SSL encryption and PCI-compliant gateways.
            </div>
        </div>

        <!-- FAQ 7 -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button @click="activeIndex = activeIndex === 7 ? null : 7" 
                    class="w-full flex justify-between items-center p-5 text-left font-bold text-navy bg-gray-50 focus:outline-none">
                <span>Can I use the test engine on my phone?</span>
                <svg class="h-5 w-5 transition duration-200" :class="activeIndex === 7 ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="activeIndex === 7" class="p-5 text-sm text-gray-600 leading-relaxed border-t border-gray-250" style="display: none;">
                Yes. Our interactive test engine is 100% web-based and responsive. It works perfectly on all smartphones, tablets, laptops, and desktop computers without installing any software or app stores.
            </div>
        </div>

        <!-- FAQ 8 -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button @click="activeIndex = activeIndex === 8 ? null : 8" 
                    class="w-full flex justify-between items-center p-5 text-left font-bold text-navy bg-gray-50 focus:outline-none">
                <span>What if I fail my exam?</span>
                <svg class="h-5 w-5 transition duration-200" :class="activeIndex === 8 ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="activeIndex === 8" class="p-5 text-sm text-gray-600 leading-relaxed border-t border-gray-250" style="display: none;">
                If you fail the exam, contact our support team at support@examsninja.com with your order details and a copy of your score report. We will process your 100% refund immediately or provide alternative study guides for free, depending on your choice.
            </div>
        </div>

    </div>
</section>
@endsection
