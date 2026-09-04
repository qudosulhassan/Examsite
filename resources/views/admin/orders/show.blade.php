@extends('layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    refundModalOpen: false,
    refundType: 'full',
    refundAmount: '{{ number_format($order->remainingRefundableAmount(), 2, '.', '') }}',
    maxRefundable: {{ (float)$order->remainingRefundableAmount() }},
    copiedText: null,
    copyToClipboard(text, key) {
        navigator.clipboard.writeText(text);
        this.copiedText = key;
        setTimeout(() => { this.copiedText = null; }, 2000);
    }
}">

    <!-- Top Action Bar & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-400 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.orders.index') }}" class="hover:text-navy transition">Orders</a>
                <span>/</span>
                <span class="text-cyan font-bold font-mono">#{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black text-navy tracking-tight font-mono">Order #{{ $order->order_number }}</h1>
                @php
                    $statusMap = [
                        'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => '●'],
                        'paid'      => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => '●'],
                        'pending'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'icon' => '⏳'],
                        'processing'=> ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => '⚙'],
                        'refunded'  => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'icon' => '↩'],
                        'partially_refunded' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'icon' => '◐'],
                        'failed'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'icon' => '✕'],
                        'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300', 'icon' => '—'],
                    ];
                    $st = $statusMap[strtolower($order->payment_status)] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'icon' => '●'];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $st['bg'] }} {{ $st['text'] }} {{ $st['border'] }}">
                    <span class="mr-1.5 text-[10px]">{{ $st['icon'] }}</span>
                    {{ strtoupper(str_replace('_', ' ', $order->payment_status)) }}
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-1">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }} ({{ $order->created_at->diffForHumans() }})</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Print Invoice -->
            <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank"
               class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-200 text-gray-700 hover:text-navy hover:border-gray-300 rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Invoice
            </a>

            <!-- Download PDF -->
            <a href="{{ route('admin.orders.invoice', $order->id) }}"
               class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-200 text-gray-700 hover:text-navy hover:border-gray-300 rounded-xl text-xs font-bold shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>

            <!-- Resend Confirmation Email -->
            <form action="{{ route('admin.orders.resend-confirmation', $order->id) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-200 text-gray-700 hover:text-navy hover:border-gray-300 rounded-xl text-xs font-bold shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Resend Email
                </button>
            </form>

            <!-- Refund Trigger Button -->
            @if($order->isRefundable())
                <button @click="refundModalOpen = true"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-black shadow-md shadow-purple-200 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Issue Refund
                </button>
            @endif
        </div>
    </div>

    <!-- Alert / Feedback Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-bold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs font-bold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center font-bold">✕</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Grid: Left Column (7 cols) + Right Column (5 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- ================= LEFT COLUMN ================= -->
        <div class="lg:col-span-7 space-y-8">
            
            <!-- 1. Order Items Matrix -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-150 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-navy text-sm">Purchased Products & Licenses</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }} in this order</p>
                    </div>
                </div>

                <div class="divide-y divide-gray-150">
                    @foreach($order->items as $item)
                        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-gray-50/50 transition">
                            <div class="flex items-start space-x-3.5">
                                <div class="w-10 h-10 rounded-xl bg-cyan/10 text-cyan flex items-center justify-center font-black text-sm flex-shrink-0">
                                    {{ $item->item_type === 'subscription' ? '⚡' : '📜' }}
                                </div>
                                <div>
                                    <div class="font-bold text-navy text-sm">
                                        @if($item->exam)
                                            <span class="text-cyan font-mono mr-1">[{{ $item->exam->vendor ? $item->exam->vendor->name : 'IT' }}]</span>
                                            {{ $item->exam->exam_code }} — {{ $item->exam->exam_name }}
                                        @else
                                            <span>{{ $item->plan_name }} Plan</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-3 text-[11px] text-gray-400 mt-1">
                                        <span class="font-semibold uppercase text-gray-600 bg-gray-100 px-2 py-0.5 rounded">
                                            Type: {{ $item->item_type }}
                                        </span>
                                        <span>Qty: {{ $item->quantity ?? 1 }}</span>
                                        @if($item->exam)
                                            <a href="{{ route('admin.exams.edit', $item->exam->id) }}" class="text-cyan hover:underline font-semibold">
                                                Inspect Exam →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-sm font-extrabold text-navy font-mono">
                                    ${{ number_format($item->price, 2) }}
                                </div>
                                <div class="text-[10px] text-gray-400">Unit Price</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Financial Totals Calculation Summary -->
                <div class="bg-gray-50/80 p-5 border-t border-gray-150 space-y-2.5">
                    <div class="flex justify-between text-xs text-gray-500 font-semibold">
                        <span>Items Subtotal</span>
                        <span class="font-mono text-gray-800">${{ number_format($order->subtotal, 2) }}</span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-xs text-emerald-600 font-semibold">
                            <span>Discount / Coupon Applied @if($order->coupon) ({{ $order->coupon->code }}) @endif</span>
                            <span class="font-mono font-bold">-${{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    @if($order->tax_amount > 0)
                        <div class="flex justify-between text-xs text-gray-500 font-semibold">
                            <span>Estimated Tax</span>
                            <span class="font-mono text-gray-800">${{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="border-t border-gray-200 pt-2.5 flex justify-between items-baseline">
                        <span class="text-sm font-black text-navy uppercase">Total Charged</span>
                        <span class="text-lg font-black text-navy font-mono">${{ number_format($order->total_amount, 2) }}</span>
                    </div>

                    @if($order->refunded_amount > 0)
                        <div class="flex justify-between text-xs text-purple-600 font-bold pt-1">
                            <span>Total Refunded to Customer</span>
                            <span class="font-mono">-${{ number_format($order->refunded_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-navy font-black pt-1 border-t border-dashed border-gray-300">
                            <span>Net Kept Revenue</span>
                            <span class="font-mono text-emerald-600">${{ number_format(max(0, $order->total_amount - $order->refunded_amount), 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. Linked Certification Access Status -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-navy text-sm">Certification Exam Access Grants</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Active exam entitlements unlocked by this transaction</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 bg-cyan/10 text-cyan rounded-lg">
                        {{ $order->userExams->count() }} active {{ Str::plural('grant', $order->userExams->count()) }}
                    </span>
                </div>

                @if($order->userExams->isEmpty())
                    <div class="p-4 bg-gray-50 rounded-xl text-center text-xs text-gray-400">
                        No active exam access records are currently linked to this order. (Access may have been revoked upon refund or expired).
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach($order->userExams as $ue)
                            <div class="p-3.5 bg-gray-50 border border-gray-150 rounded-xl flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                                        ✓
                                    </div>
                                    <div>
                                        <div class="font-bold text-navy">
                                            {{ $ue->exam ? $ue->exam->exam_code : 'Exam #' . $ue->exam_id }}
                                            — {{ $ue->exam ? $ue->exam->exam_name : 'Exam Access' }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            Status: <span class="font-semibold text-emerald-600 uppercase">{{ $ue->status ?? 'Active' }}</span>
                                            &nbsp;•&nbsp; Expires: {{ $ue->expires_at ? Carbon\Carbon::parse($ue->expires_at)->format('M d, Y') : 'Lifetime / Subscription' }}
                                        </div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                    UNLOCKED
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 3. Refund History Matrix -->
            @if($order->refunds->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-extrabold text-navy text-sm">Refund Transactions</h3>
                            <p class="text-[11px] text-gray-400 mt-0.5">Historical refund records executed for this order</p>
                        </div>
                        <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200">
                            Total Refunded: ${{ number_format($order->refunded_amount, 2) }}
                        </span>
                    </div>

                    <div class="divide-y divide-gray-150">
                        @foreach($order->refunds as $rf)
                            <div class="py-3 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <div class="font-bold text-purple-800 flex items-center space-x-2">
                                        <span class="font-mono text-sm">${{ number_format($rf->amount, 2) }}</span>
                                        <span class="text-[10px] uppercase px-1.5 py-0.5 bg-purple-100 rounded text-purple-700 font-bold">
                                            {{ $rf->status }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        Reason: <span class="italic font-medium text-gray-700">{{ $rf->reason ?: 'No reason recorded' }}</span>
                                    </div>
                                    @if($rf->gateway_refund_id)
                                        <div class="text-[10px] text-gray-400 font-mono mt-0.5">
                                            Gateway ID: {{ $rf->gateway_refund_id }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right text-[11px] text-gray-400 flex-shrink-0">
                                    <div>Processed by: <strong class="text-gray-700">{{ $rf->admin ? $rf->admin->name : 'System Admin' }}</strong></div>
                                    <div>{{ $rf->created_at->format('M d, Y h:i A') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 4. Authentic Order Timeline Chain -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">Order Activity Log & Timeline</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Auditable events, payment states, and administrator modifications</p>
                </div>

                @if($order->timelines->isEmpty())
                    <!-- Fallback if no timeline records yet -->
                    <div class="space-y-4 relative pl-6 border-l-2 border-gray-150">
                        <div class="relative">
                            <div class="absolute -left-[31px] top-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-4 ring-emerald-50"></div>
                            <div class="text-xs font-bold text-navy">Order Created & Captured</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">Initial order creation via {{ strtoupper($order->payment_method) }}</div>
                            <div class="text-[10px] text-gray-400 mt-1 font-semibold">{{ $order->created_at->format('M d, Y h:i:s A') }}</div>
                        </div>
                    </div>
                @else
                    <div class="space-y-5 relative pl-6 border-l-2 border-gray-150">
                        @foreach($order->timelines as $tl)
                            @php
                                $dotColor = match($tl->event) {
                                    'order_created' => 'bg-emerald-500 ring-emerald-50',
                                    'payment_completed' => 'bg-emerald-500 ring-emerald-50',
                                    'refund_processed' => 'bg-purple-500 ring-purple-50',
                                    'status_updated' => 'bg-blue-500 ring-blue-50',
                                    'notes_updated' => 'bg-amber-500 ring-amber-50',
                                    'confirmation_resent' => 'bg-cyan ring-cyan/20',
                                    default => 'bg-gray-400 ring-gray-100',
                                };
                            @endphp
                            <div class="relative">
                                <div class="absolute -left-[31px] top-1 w-3.5 h-3.5 rounded-full {{ $dotColor }} ring-4"></div>
                                <div class="text-xs font-bold text-navy flex items-center space-x-2">
                                    <span>{{ strtoupper(str_replace('_', ' ', $tl->event)) }}</span>
                                    @if($tl->performer)
                                        <span class="text-[10px] font-normal text-gray-400">by {{ $tl->performer->name }}</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-gray-600 mt-0.5">{{ $tl->description }}</div>
                                <div class="text-[10px] text-gray-400 mt-1 font-semibold">{{ $tl->created_at->format('M d, Y h:i:s A') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- ================= RIGHT COLUMN ================= -->
        <div class="lg:col-span-5 space-y-8">
            
            <!-- 1. Customer Profile Card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-navy text-sm">Customer Profile</h3>
                    @if($order->user)
                        <a href="{{ route('admin.users.show', $order->user_id) }}" class="text-xs font-bold text-cyan hover:underline">
                            View Account →
                        </a>
                    @endif
                </div>

                <div class="flex items-center space-x-3.5 pb-4 border-b border-gray-150">
                    <div class="w-12 h-12 rounded-2xl bg-navy/10 text-navy font-black flex items-center justify-center text-sm flex-shrink-0">
                        {{ $order->user ? strtoupper(substr($order->user->name, 0, 2)) : 'GU' }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-black text-navy text-base truncate">
                            {{ $order->user ? $order->user->name : ($order->billing_name ?: 'Guest Customer') }}
                        </div>
                        <div class="text-xs text-gray-400 truncate">
                            {{ $order->user ? $order->user->email : $order->billing_email }}
                        </div>
                    </div>
                </div>

                <!-- Customer Metrics Quick Stats -->
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Orders</div>
                        <div class="text-lg font-black text-navy mt-0.5">{{ $customerOrdersCount }}</div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Customer LTV</div>
                        <div class="text-lg font-black text-emerald-600 mt-0.5">${{ number_format($customerTotalSpent, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- 2. Payment Gateway & Transaction Metadata -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h3 class="font-extrabold text-navy text-sm">Payment Gateway Metadata</h3>

                <div class="space-y-3 text-xs">
                    <!-- Method -->
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-400 font-semibold">Payment Gateway</span>
                        <span class="font-extrabold uppercase text-navy flex items-center gap-1.5">
                            @if(strtolower($order->payment_method) === 'stripe')
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Stripe
                            @elseif(strtolower($order->payment_method) === 'paypal')
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> PayPal
                            @else
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ $order->payment_method ?: 'Direct' }}
                            @endif
                        </span>
                    </div>

                    <!-- Stripe Payment Intent ID -->
                    @if($order->stripe_payment_intent_id)
                        <div class="py-2 border-b border-gray-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-gray-400 font-semibold">Stripe Payment Intent</span>
                                <button @click="copyToClipboard('{{ $order->stripe_payment_intent_id }}', 'stripe')"
                                        class="text-[10px] font-bold text-cyan hover:underline">
                                    <span x-text="copiedText === 'stripe' ? 'Copied!' : 'Copy'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-gray-700 bg-gray-50 p-2 rounded-lg break-all select-all">
                                {{ $order->stripe_payment_intent_id }}
                            </div>
                        </div>
                    @endif

                    <!-- PayPal Order ID -->
                    @if($order->paypal_order_id)
                        <div class="py-2 border-b border-gray-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-gray-400 font-semibold">PayPal Transaction ID</span>
                                <button @click="copyToClipboard('{{ $order->paypal_order_id }}', 'paypal')"
                                        class="text-[10px] font-bold text-cyan hover:underline">
                                    <span x-text="copiedText === 'paypal' ? 'Copied!' : 'Copy'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-gray-700 bg-gray-50 p-2 rounded-lg break-all select-all">
                                {{ $order->paypal_order_id }}
                            </div>
                        </div>
                    @endif

                    <!-- Order Reference -->
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-400 font-semibold">System Order ID</span>
                        <span class="font-mono font-bold text-navy">#{{ $order->id }}</span>
                    </div>

                    <!-- Currency -->
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-400 font-semibold">Currency</span>
                        <span class="font-bold text-navy">USD ($)</span>
                    </div>
                </div>
            </div>

            <!-- 3. Billing Information -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h3 class="font-extrabold text-navy text-sm">Billing Details</h3>
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-gray-100">
                        <span class="text-gray-400 font-semibold">Billing Name</span>
                        <span class="font-bold text-navy">{{ $order->billing_name ?: 'Not Provided' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-gray-100">
                        <span class="text-gray-400 font-semibold">Billing Email</span>
                        <span class="font-bold text-navy">{{ $order->billing_email ?: 'Not Provided' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-gray-400 font-semibold">IP Address</span>
                        <span class="font-mono text-gray-500">{{ $order->ip_address ?: '127.0.0.1' }}</span>
                    </div>
                </div>
            </div>

            <!-- 4. Quick Status Changer -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h3 class="font-extrabold text-navy text-sm">Update Order Status</h3>
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <select name="status" class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-navy outline-none focus:border-cyan">
                            <option value="completed" {{ $order->payment_status === 'completed' ? 'selected' : '' }}>Completed / Paid</option>
                            <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->payment_status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="partially_refunded" {{ $order->payment_status === 'partially_refunded' ? 'selected' : '' }}>Partially Refunded</option>
                            <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="cancelled" {{ $order->payment_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 bg-navy hover:bg-navy/90 text-white rounded-xl text-xs font-bold transition">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- 5. Administrator Private Notes -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">Internal Administrator Notes</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Private notes visible only to system administrators</p>
                </div>

                <form action="{{ route('admin.orders.notes', $order->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <textarea name="admin_notes" rows="4"
                              placeholder="Add order notes, support ticket links, or customer dispute notes..."
                              class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-navy outline-none focus:border-cyan focus:bg-white transition">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                    <button type="submit" class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-navy font-bold rounded-xl text-xs transition">
                        Save Notes
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- Interactive Refund Modal (Alpine.js) -->
    <div x-show="refundModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="refundModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="refundModalOpen = false"
                 class="fixed inset-0 transition-opacity bg-navy/60 backdrop-blur-sm"></div>

            <!-- Dialog Modal -->
            <div x-show="refundModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl sm:align-middle">

                <div class="flex items-center justify-between pb-4 border-b border-gray-150">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                            ↩
                        </div>
                        <h3 class="text-base font-extrabold text-navy">Process Refund</h3>
                    </div>
                    <button @click="refundModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST" class="mt-5 space-y-4">
                    @csrf

                    <!-- Refund Type Radio Selection -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Refund Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 border rounded-xl flex items-center space-x-2 cursor-pointer transition"
                                   :class="refundType === 'full' ? 'border-purple-600 bg-purple-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="refund_type" value="full" x-model="refundType"
                                       @change="refundAmount = '{{ number_format($order->remainingRefundableAmount(), 2, '.', '') }}'"
                                       class="text-purple-600 focus:ring-purple-500">
                                <div>
                                    <div class="text-xs font-bold text-navy">Full Refund</div>
                                    <div class="text-[10px] text-gray-400 font-mono">${{ number_format($order->remainingRefundableAmount(), 2) }}</div>
                                </div>
                            </label>

                            <label class="p-3 border rounded-xl flex items-center space-x-2 cursor-pointer transition"
                                   :class="refundType === 'partial' ? 'border-purple-600 bg-purple-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="refund_type" value="partial" x-model="refundType"
                                       class="text-purple-600 focus:ring-purple-500">
                                <div>
                                    <div class="text-xs font-bold text-navy">Partial Refund</div>
                                    <div class="text-[10px] text-gray-400">Custom amount</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Custom Amount Input (if partial) -->
                    <div x-show="refundType === 'partial'">
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            Refund Amount ($ USD)
                            <span class="text-gray-400 font-normal">(Max: ${{ number_format($order->remainingRefundableAmount(), 2) }})</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 font-bold">$</span>
                            <input type="number" name="amount" step="0.01" min="0.01" :max="maxRefundable" x-model="refundAmount"
                                   class="w-full h-11 pl-7 pr-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-navy outline-none focus:border-purple-600">
                        </div>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Reason for Refund</label>
                        <input type="text" name="reason" placeholder="e.g. Customer requested cancellation within 30-day guarantee"
                               class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-navy outline-none focus:border-purple-600">
                    </div>

                    <!-- Revoke Access Checkbox -->
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <label class="flex items-start space-x-2 cursor-pointer">
                            <input type="checkbox" name="revoke_access" value="1" checked
                                   class="rounded border-amber-300 text-purple-600 focus:ring-purple-500 mt-0.5">
                            <div class="text-xs">
                                <span class="font-bold text-amber-900">Revoke Linked Certification Exam Access</span>
                                <p class="text-[10px] text-amber-700 mt-0.5">Immediately disables access to exams granted by this purchase.</p>
                            </div>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-gray-150 flex items-center justify-end space-x-3">
                        <button type="button" @click="refundModalOpen = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-black shadow-md shadow-purple-200 transition">
                            Confirm & Execute Refund
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection

