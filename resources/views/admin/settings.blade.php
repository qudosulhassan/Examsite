@extends('layouts.admin')

@section('title', 'Settings Center — Exam Topics Base')

@section('styles')
<style>
    /* Settings Center Two-Column Layout */
    .settings-grid-layout {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        gap: 1.5rem !important;
        align-items: flex-start !important;
        width: 100% !important;
    }
    .settings-sidebar-col {
        width: 250px !important;
        min-width: 250px !important;
        max-width: 250px !important;
        flex: 0 0 250px !important;
    }
    .settings-content-col {
        flex: 1 1 500px !important;
        min-width: 0 !important;
        max-width: 100% !important;
    }

    @media (max-width: 768px) {
        .settings-grid-layout {
            flex-direction: column !important;
        }
        .settings-sidebar-col {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 100% !important;
            flex: 1 1 100% !important;
        }
        .settings-content-col {
            width: 100% !important;
            flex: 1 1 100% !important;
        }
    }

    /* Responsive grid for 2-column input rows */
    .settings-form-grid-2 {
        display: grid !important;
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
        gap: 1.5rem !important;
    }
    @media (min-width: 640px) {
        .settings-form-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
</style>
@endsection

@section('content')

<script>
function settingsCenter() {
    const tabs = [
        { id: 'general', name: 'General', badge: '' },
        { id: 'branding', name: 'Branding', badge: 'Logos' },
        { id: 'contact', name: 'Contact & Social', badge: '' },
        { id: 'seo', name: 'SEO Defaults', badge: '' },
        { id: 'promotion', name: 'Promotion Banner', badge: 'Hero' },
        { id: 'subscriptions', name: 'Subscription Plans', badge: 'Plans' },
        { id: 'email', name: 'Email Settings', badge: '' },
        { id: 'payments', name: 'Payment Gateways', badge: 'Stripe' },
        { id: 'maintenance', name: 'Maintenance Mode', badge: '' },
        { id: 'security', name: 'Security & Auth', badge: '' },
        { id: 'advanced', name: 'System & Cache', badge: 'Tools' }
    ];

    return {
        activeTab: '{{ request()->get('tab', 'general') }}',
        searchQuery: '',
        isSubmitting: false,
        hasUnsavedChanges: false,

        // SEO character counters
        seoTitle: '{{ addslashes($settings['default_seo_title'] ?? config('seo.defaults.title')) }}',
        seoDescription: '{{ addslashes($settings['default_meta_description'] ?? config('seo.defaults.description')) }}',

        // Promotion banner live preview models
        bannerActive: '{{ $settings['home_banner_active'] ?? '0' }}',
        bannerText: '{{ addslashes($settings['home_banner_text'] ?? '') }}',
        bannerCoupon: '{{ addslashes($settings['home_banner_coupon'] ?? '') }}',
        bannerButtonText: '{{ addslashes($settings['home_banner_button_text'] ?? '') }}',

        // Branding assets
        branding: {
            site_logo: '{{ $settings['site_logo'] ?? '' }}',
            site_logo_dark: '{{ $settings['site_logo_dark'] ?? '' }}',
            site_logo_light: '{{ $settings['site_logo_light'] ?? '' }}',
            site_favicon: '{{ $settings['site_favicon'] ?? '' }}',
            apple_touch_icon: '{{ $settings['apple_touch_icon'] ?? '' }}'
        },

        // Subscription Plans
        plans: @json($plans),
        planModalOpen: false,
        editingPlanIndex: null,
        newFeatureText: '',
        currentPlan: {
            name: '',
            price_monthly: 0,
            price_annual: 0,
            features: [],
            status: 'active'
        },

        // All Tabs List
        allTabs: tabs,
        visibleTabs: [...tabs],

        init() {
            this.visibleTabs = [...this.allTabs];

            // Prevent accidental tab closure if unsaved
            window.addEventListener('beforeunload', (e) => {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
        },

        switchTab(tabId) {
            this.activeTab = tabId;
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.replaceState({}, '', url);
        },

        markDirty() {
            this.hasUnsavedChanges = true;
        },

        submitMainForm() {
            this.hasUnsavedChanges = false;
            document.getElementById('settingsMainForm').submit();
        },

        filterSettings() {
            if (!this.searchQuery) {
                this.visibleTabs = [...this.allTabs];
                return;
            }
            const q = this.searchQuery.toLowerCase();
            this.visibleTabs = this.allTabs.filter(t => 
                t.name.toLowerCase().includes(q) || 
                t.id.toLowerCase().includes(q) || 
                (t.badge && t.badge.toLowerCase().includes(q))
            );
            if (this.visibleTabs.length && !this.visibleTabs.some(t => t.id === this.activeTab)) {
                this.activeTab = this.visibleTabs[0].id;
            }
        },

        // Branding Upload
        async uploadAsset(event, type) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('type', type);
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('admin.settings.upload-branding') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    this.branding[type] = result.url;
                    alert(result.message || 'Asset uploaded successfully.');
                } else {
                    alert(result.message || 'Upload failed.');
                }
            } catch (err) {
                alert('Network error while uploading asset.');
            }
        },

        async removeAsset(type) {
            if (!confirm('Are you sure you want to remove this custom branding asset?')) return;

            const formData = new FormData();
            formData.append('type', type);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('admin.settings.remove-branding') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    this.branding[type] = '';
                    alert('Asset removed.');
                } else {
                    alert('Network error removing asset.');
                }
            } catch (err) {
                alert('Network error removing asset.');
            }
        },

        // Subscription Plans Manager
        openPlanModal(index = null) {
            this.editingPlanIndex = index;
            if (index !== null) {
                const existing = this.plans[index];
                this.currentPlan = {
                    name: existing.name || '',
                    price_monthly: existing.price_monthly || 0,
                    price_annual: existing.price_annual || 0,
                    features: Array.isArray(existing.features) ? [...existing.features] : [],
                    status: existing.status || 'active'
                };
            } else {
                this.currentPlan = {
                    name: '',
                    price_monthly: 19,
                    price_annual: 99,
                    features: ['Access to 50 exams', 'Standard support'],
                    status: 'active'
                };
            }
            this.newFeatureText = '';
            this.planModalOpen = true;
        },

        editPlan(index) {
            this.openPlanModal(index);
        },

        duplicatePlan(index) {
            const original = this.plans[index];
            const duplicate = {
                name: original.name + ' (Copy)',
                price_monthly: original.price_monthly,
                price_annual: original.price_annual,
                features: [...(original.features || [])],
                status: original.status || 'active'
            };
            this.plans.push(duplicate);
            this.markDirty();
        },

        togglePlanStatus(index) {
            this.plans[index].status = this.plans[index].status === 'disabled' ? 'active' : 'disabled';
            this.markDirty();
        },

        deletePlan(index) {
            if (confirm('Delete this subscription plan? Existing active subscribers will not be affected.')) {
                this.plans.splice(index, 1);
                this.markDirty();
            }
        },

        addFeature() {
            if (!this.newFeatureText.trim()) return;
            this.currentPlan.features.push(this.newFeatureText.trim());
            this.newFeatureText = '';
        },

        removeFeature(index) {
            this.currentPlan.features.splice(index, 1);
        },

        savePlan() {
            if (!this.currentPlan.name.trim()) {
                alert('Plan name is required.');
                return;
            }
            if (this.currentPlan.price_monthly < 0 || this.currentPlan.price_annual < 0) {
                alert('Prices cannot be negative.');
                return;
            }

            if (this.editingPlanIndex !== null) {
                this.plans[this.editingPlanIndex] = { ...this.currentPlan };
            } else {
                this.plans.push({ ...this.currentPlan });
            }

            this.planModalOpen = false;
            this.markDirty();
        },

        // --- PAYMENT OPERATIONS CENTER ---
        paymentFilter: '{{ $paymentOverview['active_filter'] ?? 'all' }}',
        paymentDateFrom: '{{ $paymentOverview['date_from'] ?? '' }}',
        paymentDateTo: '{{ $paymentOverview['date_to'] ?? '' }}',
        isTestingStripe: false,
        isTestingPayPal: false,
        testResult: null, // { gateway: '', success: bool, message: '', mode: '', details: {} }

        // Transaction Details Drawer
        selectedTxId: null,
        txLoading: false,
        txData: null,
        txDrawerOpen: false,

        // Refund Modal
        refundModalOpen: false,
        refundOrder: null,
        refundType: 'full',
        refundAmount: '',
        refundReason: '',
        revokeAccess: true,
        isRefunding: false,

        // Webhook Payload Inspector
        webhookPayloadModalOpen: false,
        selectedWebhook: null,
        isRetryingWebhook: false,

        // Gateway Statuses
        stripeEnabled: {{ ($settings['payment_gateway_stripe_enabled'] ?? '1') === '1' ? 'true' : 'false' }},
        paypalEnabled: {{ ($settings['payment_gateway_paypal_enabled'] ?? '1') === '1' ? 'true' : 'false' }},

        // Stripe Credential Management State
        stripeModalOpen: false,
        isUpdatingStripe: false,
        stripeShowSecret: false,
        stripeShowWebhook: false,
        stripeForm: {
            mode: '{{ $paymentInfo['stripe_mode_raw'] ?? 'test' }}',
            publishable_key: '{{ addslashes($paymentInfo['stripe_raw_public_key'] ?? '') }}',
            secret_key: '',
            webhook_secret: '',
        },
        stripeData: {
            configured: {{ $paymentInfo['stripe_configured'] ? 'true' : 'false' }},
            mode: '{{ $paymentInfo['stripe_mode'] }}',
            is_live: {{ $paymentInfo['stripe_is_live'] ? 'true' : 'false' }},
            public_key: '{{ $paymentInfo['stripe_public_key'] }}',
            raw_public_key: '{{ addslashes($paymentInfo['stripe_raw_public_key'] ?? '') }}',
            masked_secret: '{{ $paymentInfo['stripe_masked_secret'] }}',
            masked_webhook: '{{ $paymentInfo['stripe_masked_webhook'] }}',
            secret_configured: {{ $paymentInfo['stripe_secret_configured'] ? 'true' : 'false' }},
            webhook_configured: {{ $paymentInfo['stripe_webhook_configured'] ? 'true' : 'false' }},
        },

        // PayPal Credential Management State
        paypalModalOpen: false,
        isUpdatingPayPal: false,
        paypalShowSecret: false,
        paypalForm: {
            mode: '{{ $paymentInfo['paypal_mode_raw'] ?? 'sandbox' }}',
            client_id: '{{ addslashes($paymentInfo['paypal_raw_client_id'] ?? '') }}',
            client_secret: '',
            webhook_id: '{{ addslashes($paymentInfo['paypal_webhook_id'] ?? '') }}',
        },
        paypalData: {
            configured: {{ $paymentInfo['paypal_configured'] ? 'true' : 'false' }},
            mode: '{{ $paymentInfo['paypal_mode'] }}',
            is_live: {{ $paymentInfo['paypal_is_live'] ? 'true' : 'false' }},
            client_id: '{{ $paymentInfo['paypal_client_id'] }}',
            raw_client_id: '{{ addslashes($paymentInfo['paypal_raw_client_id'] ?? '') }}',
            webhook_id: '{{ addslashes($paymentInfo['paypal_webhook_id'] ?? '') }}',
            masked_secret: '{{ $paymentInfo['paypal_masked_secret'] }}',
            secret_configured: {{ $paymentInfo['paypal_secret_configured'] ? 'true' : 'false' }},
        },

        // Action Toasts
        toastMessage: '',
        toastType: 'success',
        toastVisible: false,

        showToast(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 4500);
        },

        openStripeModal() {
            this.stripeForm.mode = this.stripeData.is_live ? 'live' : 'test';
            this.stripeForm.publishable_key = this.stripeData.raw_public_key;
            this.stripeForm.secret_key = '';
            this.stripeForm.webhook_secret = '';
            this.stripeShowSecret = false;
            this.stripeShowWebhook = false;
            this.stripeModalOpen = true;
        },

        async submitStripeCredentials() {
            if (this.stripeForm.mode === 'live' && !this.stripeData.is_live) {
                if (!confirm("⚠️ PRODUCTION WARNING:\n\nYou are switching Stripe to LIVE mode. Real customer credit cards will be charged!\n\nAre you sure you want to proceed?")) {
                    return;
                }
            }
            this.isUpdatingStripe = true;
            try {
                const res = await fetch('{{ route('admin.settings.update-stripe-credentials') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.stripeForm)
                });
                const data = await res.json();
                if (data.success) {
                    this.stripeData.mode = data.data.mode;
                    this.stripeData.is_live = data.data.is_live;
                    this.stripeData.public_key = data.data.public_key;
                    this.stripeData.raw_public_key = data.data.raw_public_key;
                    this.stripeData.masked_secret = data.data.masked_secret;
                    this.stripeData.masked_webhook = data.data.masked_webhook;
                    this.stripeData.secret_configured = data.data.secret_configured;
                    this.stripeData.webhook_configured = data.data.webhook_configured;
                    this.stripeData.configured = data.data.secret_configured;
                    this.stripeModalOpen = false;
                    this.showToast(data.message || 'Stripe credentials updated successfully.', 'success');
                } else {
                    this.showToast(data.message || 'Could not update Stripe credentials.', 'error');
                }
            } catch (err) {
                this.showToast('Network error: ' + err.message, 'error');
            } finally {
                this.isUpdatingStripe = false;
            }
        },

        openPayPalModal() {
            this.paypalForm.mode = this.paypalData.is_live ? 'live' : 'sandbox';
            this.paypalForm.client_id = this.paypalData.raw_client_id;
            this.paypalForm.client_secret = '';
            this.paypalForm.webhook_id = this.paypalData.webhook_id;
            this.paypalShowSecret = false;
            this.paypalModalOpen = true;
        },

        async submitPayPalCredentials() {
            if (this.paypalForm.mode === 'live' && !this.paypalData.is_live) {
                if (!confirm("⚠️ PRODUCTION WARNING:\n\nYou are switching PayPal to LIVE mode. Real customer PayPal accounts will be billed!\n\nAre you sure you want to proceed?")) {
                    return;
                }
            }
            this.isUpdatingPayPal = true;
            try {
                const res = await fetch('{{ route('admin.settings.update-paypal-credentials') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.paypalForm)
                });
                const data = await res.json();
                if (data.success) {
                    this.paypalData.mode = data.data.mode;
                    this.paypalData.is_live = data.data.is_live;
                    this.paypalData.client_id = data.data.client_id;
                    this.paypalData.raw_client_id = data.data.raw_client_id;
                    this.paypalData.webhook_id = data.data.webhook_id;
                    this.paypalData.masked_secret = data.data.masked_secret;
                    this.paypalData.secret_configured = data.data.secret_configured;
                    this.paypalData.configured = data.data.secret_configured;
                    this.paypalModalOpen = false;
                    this.showToast(data.message || 'PayPal credentials updated successfully.', 'success');
                } else {
                    this.showToast(data.message || 'Could not update PayPal credentials.', 'error');
                }
            } catch (err) {
                this.showToast('Network error: ' + err.message, 'error');
            } finally {
                this.isUpdatingPayPal = false;
            }
        },

        async testGatewayConnection(gateway) {
            if (gateway === 'stripe') this.isTestingStripe = true;
            if (gateway === 'paypal') this.isTestingPayPal = true;
            this.testResult = null;

            try {
                const endpoint = gateway === 'stripe' ? '{{ route('admin.settings.test-stripe') }}' : '{{ route('admin.settings.test-paypal') }}';
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                this.testResult = {
                    gateway: gateway.toUpperCase(),
                    success: data.success,
                    status: data.status,
                    mode: data.mode || '',
                    message: data.message,
                    details: data.details || null
                };
            } catch (err) {
                this.testResult = {
                    gateway: gateway.toUpperCase(),
                    success: false,
                    status: 'network_error',
                    message: 'Network request failed: ' + err.message
                };
            } finally {
                if (gateway === 'stripe') this.isTestingStripe = false;
                if (gateway === 'paypal') this.isTestingPayPal = false;
            }
        },

        async openTxDetails(orderId) {
            this.selectedTxId = orderId;
            this.txLoading = true;
            this.txDrawerOpen = true;
            this.txData = null;

            try {
                const res = await fetch(`{{ url('/admin/settings/transactions') }}/${orderId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.txData = data.order;
                } else {
                    alert('Error loading transaction details.');
                    this.txDrawerOpen = false;
                }
            } catch (err) {
                alert('Network error loading transaction details.');
                this.txDrawerOpen = false;
            } finally {
                this.txLoading = false;
            }
        },

        openRefundModal(order) {
            this.refundOrder = order;
            this.refundType = 'full';
            this.refundAmount = order.remaining_refundable ?? order.total_amount;
            this.refundReason = 'Requested by customer';
            this.revokeAccess = true;
            this.refundModalOpen = true;
        },

        async executeRefund() {
            if (!this.refundOrder) return;
            const maxRefund = parseFloat(this.refundOrder.remaining_refundable || this.refundOrder.total_amount);
            const amountToRefund = this.refundType === 'full' ? maxRefund : parseFloat(this.refundAmount);

            if (isNaN(amountToRefund) || amountToRefund <= 0) {
                alert('Please enter a valid refund amount greater than $0.00.');
                return;
            }
            if (amountToRefund > maxRefund) {
                alert(`Refund amount ($${amountToRefund}) exceeds remaining refundable amount ($${maxRefund}).`);
                return;
            }

            if (!confirm(`Are you sure you want to process a refund of $${amountToRefund.toFixed(2)} for Order #${this.refundOrder.order_number}? This will call the real payment gateway.`)) {
                return;
            }

            this.isRefunding = true;
            try {
                const res = await fetch(`{{ url('/admin/settings/transactions') }}/${this.refundOrder.id}/refund`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        refund_type: this.refundType,
                        amount: amountToRefund,
                        reason: this.refundReason,
                        revoke_access: this.revokeAccess ? 1 : 0
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.refundModalOpen = false;
                    window.location.reload();
                } else {
                    alert('Refund failed: ' + data.message);
                }
            } catch (err) {
                alert('Network error while processing refund: ' + err.message);
            } finally {
                this.isRefunding = false;
            }
        },

        openWebhookModal(webhook) {
            this.selectedWebhook = webhook;
            this.webhookPayloadModalOpen = true;
        },

        async retryWebhookItem(webhookId) {
            if (!confirm(`Reprocess webhook #${webhookId}?`)) return;

            this.isRetryingWebhook = true;
            try {
                const res = await fetch(`{{ url('/admin/settings/webhooks') }}/${webhookId}/retry`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.webhookPayloadModalOpen = false;
                    window.location.reload();
                } else {
                    alert('Retry error: ' + data.message);
                }
            } catch (err) {
                alert('Network error: ' + err.message);
            } finally {
                this.isRetryingWebhook = false;
            }
        },

        async toggleGatewayStatus(gateway) {
            const newState = gateway === 'stripe' ? this.stripeEnabled : this.paypalEnabled;
            if (!confirm(`Are you sure you want to ${newState ? 'enable' : 'disable'} ${gateway.toUpperCase()} checkout?`)) {
                if (gateway === 'stripe') this.stripeEnabled = !newState;
                if (gateway === 'paypal') this.paypalEnabled = !newState;
                return;
            }

            try {
                const res = await fetch('{{ route('admin.settings.toggle-gateway') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        gateway: gateway,
                        enabled: newState ? 1 : 0
                    })
                });
                const data = await res.json();
                if (!data.success) {
                    alert('Failed to update gateway status.');
                }
            } catch (err) {
                alert('Error updating gateway status.');
            }
        }
    };
}
</script>

<div x-data="settingsCenter()" class="space-y-8 pb-24">

    <!-- Top Workspace Header (ExamTopicsBase Dark Navy & Teal Design) -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Left: Breadcrumb & Title -->
            <div class="space-y-1">
                <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-navy font-bold">Settings</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-navy tracking-tight font-heading">
                        Settings Center
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan/10 text-cyan border border-cyan/30">
                        Production Ready
                    </span>
                </div>
                <p class="text-xs text-gray-500">
                    Manage global platform settings, brand identity, subscriptions, SEO defaults, and payment gateways.
                </p>
            </div>

            <!-- Right: Meta Status & Fast Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="text-right text-xs text-gray-500 hidden sm:block">
                    <div>Last updated: <span class="font-bold text-navy">{{ $lastUpdatedTime ? $lastUpdatedTime->diffForHumans() : 'Never' }}</span></div>
                    <div class="text-[11px] text-gray-400">By: {{ $lastUpdatedBy }}</div>
                </div>

                <button type="button" @click="submitMainForm()" :disabled="isSubmitting" class="inline-flex items-center gap-2 bg-navy hover:bg-navy/90 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-lg shadow transition disabled:opacity-50">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="isSubmitting ? 'Saving...' : 'Save Settings'">Save Settings</span>
                </button>
            </div>
        </div>

        <!-- Search Settings Bar & Quick Jump -->
        <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" x-model="searchQuery" @input="filterSettings()" placeholder="Search settings (e.g., logo, SEO, stripe)..." class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border-gray-200 focus:border-cyan focus:ring-cyan">
                <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterSettings()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Unsaved changes warning indicator -->
            <div x-show="hasUnsavedChanges" x-cloak class="flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-800 text-xs px-3 py-1.5 rounded-lg animate-pulse">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span class="font-semibold">You have unsaved changes!</span>
            </div>
        </div>
    </div>

    <!-- Alert / Flash Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold p-4 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <div>
                <p class="font-black text-sm">{{ session('success') }}</p>
                <p class="text-[11px] font-normal text-emerald-700">All changes have been committed and cached configuration refreshed.</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs font-bold p-4 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <p class="font-black text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl shadow-sm text-xs">
            <div class="font-bold flex items-center gap-2 mb-2 text-sm text-red-900">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                Please correct the following configuration errors:
            </div>
            <ul class="list-disc list-inside space-y-1 text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Tab Navigation Layout -->
    <div class="settings-grid-layout flex flex-col md:flex-row items-start gap-6 w-full">
        <!-- Tab Navigation Sidebar (Desktop & Mobile Dropdown) -->
        <div class="settings-sidebar-col w-full md:w-[260px] md:min-w-[260px] md:max-w-[260px] flex-shrink-0">
            <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-sm sticky top-6 space-y-1">
                <div class="px-3 py-2 text-[10px] font-black uppercase tracking-wider text-gray-400">Settings Sections</div>
                
                <template x-for="tab in visibleTabs" :key="tab.id">
                    <button type="button" @click="switchTab(tab.id)"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-bold transition text-left"
                            :class="activeTab === tab.id ? 'bg-navy text-white shadow-sm ring-1 ring-navy' : 'text-gray-600 hover:bg-gray-50 hover:text-navy'">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full" :class="activeTab === tab.id ? 'bg-cyan' : 'bg-gray-300'"></span>
                            <span x-text="tab.name"></span>
                        </div>
                        <span x-show="tab.badge" x-text="tab.badge" class="px-1.5 py-0.5 text-[9px] rounded font-bold uppercase"
                              :class="activeTab === tab.id ? 'bg-cyan/20 text-cyan' : 'bg-gray-100 text-gray-500'"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Tab Content Area (Main Form) -->
        <div class="settings-content-col flex-1 min-w-0 w-full space-y-6">
            <form id="settingsMainForm" action="{{ route('admin.settings.update') }}" method="POST" @submit="isSubmitting = true" class="space-y-6">
                @csrf
                <input type="hidden" name="active_tab" :value="activeTab">

                <!-- TAB 1: GENERAL -->
                <div x-show="activeTab === 'general'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-general">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">01</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">General Settings</h3>
                                <p class="text-xs text-gray-500">Core platform details, naming, default currency, and timezone.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="site_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Site Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="site_name" id="site_name" @input="markDirty()" value="{{ old('site_name', $settings['site_name'] ?? 'Exam Topics Base') }}" required class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Displayed in title bars, emails, and footer copyright.</p>
                            </div>

                            <div>
                                <label for="site_tagline" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Site Tagline
                                </label>
                                <input type="text" name="site_tagline" id="site_tagline" @input="markDirty()" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Marketing slogan displayed on the homepage and meta tags.</p>
                            </div>

                            <div>
                                <label for="site_url" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Canonical Site URL
                                </label>
                                <input type="url" name="site_url" id="site_url" @input="markDirty()" value="{{ old('site_url', $settings['site_url'] ?? config('app.url', 'http://127.0.0.1:8000')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono text-xs">
                                <p class="text-[11px] text-gray-400 mt-1">Base domain URL for public asset generation and sitemaps.</p>
                            </div>

                            <div>
                                <label for="default_currency" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Default Currency
                                </label>
                                <select name="default_currency" id="default_currency" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                    <option value="USD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD ($ - United States Dollar)</option>
                                    <option value="EUR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'EUR' ? 'selected' : '' }}>EUR (€ - Euro)</option>
                                    <option value="GBP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'GBP' ? 'selected' : '' }}>GBP (£ - British Pound)</option>
                                    <option value="CAD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'CAD' ? 'selected' : '' }}>CAD ($ - Canadian Dollar)</option>
                                    <option value="AUD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'AUD' ? 'selected' : '' }}>AUD ($ - Australian Dollar)</option>
                                </select>
                            </div>

                            <div>
                                <label for="default_timezone" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Default Timezone
                                </label>
                                <select name="default_timezone" id="default_timezone" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                    <option value="UTC" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                    <option value="America/New_York" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST / EDT)</option>
                                    <option value="America/Chicago" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'America/Chicago' ? 'selected' : '' }}>America/Chicago (CST / CDT)</option>
                                    <option value="America/Los_Angeles" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (PST / PDT)</option>
                                    <option value="Europe/London" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT / BST)</option>
                                    <option value="Asia/Dubai" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                                    <option value="Asia/Karachi" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'Asia/Karachi' ? 'selected' : '' }}>Asia/Karachi (PKT)</option>
                                </select>
                            </div>

                            <div>
                                <label for="contact_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Primary Contact Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="contact_email" id="contact_email" @input="markDirty()" value="{{ old('contact_email', $settings['contact_email'] ?? 'contact@examtopicsbase.com') }}" required class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: BRANDING (Logos & Favicon) -->
                <div x-show="activeTab === 'branding'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-branding">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">02</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Brand & Visual Identity</h3>
                                <p class="text-xs text-gray-500">Upload primary logo, dark/light variations, and browser favicons.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Primary Site Logo -->
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-navy uppercase tracking-wide">Primary Logo</label>
                                    <span class="text-[10px] text-gray-400 font-mono">PNG, SVG, WEBP (Max 3MB)</span>
                                </div>
                                
                                <div class="bg-navy p-4 rounded-lg flex items-center justify-center min-h-[90px] relative group">
                                    <img :src="branding.site_logo || '{{ asset('images/logo.png') }}'" alt="Site Logo Preview" class="h-10 max-w-[200px] object-contain">
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-md transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <span>Upload New</span>
                                        <input type="file" @change="uploadAsset($event, 'site_logo')" accept="image/*" class="hidden">
                                    </label>
                                    <button type="button" x-show="branding.site_logo" @click="removeAsset('site_logo')" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Site Favicon -->
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-navy uppercase tracking-wide">Browser Favicon</label>
                                    <span class="text-[10px] text-gray-400 font-mono">PNG, ICO (32x32, 64x64)</span>
                                </div>
                                
                                <div class="bg-white border border-gray-200 p-4 rounded-lg flex items-center justify-center min-h-[90px]">
                                    <img :src="branding.site_favicon || '{{ asset('favicon-32x32.png') }}'" alt="Favicon Preview" class="w-8 h-8 object-contain">
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-md transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <span>Upload Favicon</span>
                                        <input type="file" @change="uploadAsset($event, 'site_favicon')" accept="image/png,image/x-icon,image/svg+xml" class="hidden">
                                    </label>
                                    <button type="button" x-show="branding.site_favicon" @click="removeAsset('site_favicon')" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Dark Logo -->
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-navy uppercase tracking-wide">Dark Logo (Light backgrounds)</label>
                                    <span class="text-[10px] text-gray-400 font-mono">PNG, SVG (Max 3MB)</span>
                                </div>
                                
                                <div class="bg-white border border-gray-200 p-4 rounded-lg flex items-center justify-center min-h-[90px]">
                                    <img :src="branding.site_logo_dark || branding.site_logo || '{{ asset('images/logo.png') }}'" alt="Dark Logo Preview" class="h-10 max-w-[200px] object-contain">
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-md transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <span>Upload Logo</span>
                                        <input type="file" @change="uploadAsset($event, 'site_logo_dark')" accept="image/*" class="hidden">
                                    </label>
                                    <button type="button" x-show="branding.site_logo_dark" @click="removeAsset('site_logo_dark')" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Apple Touch Icon -->
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-navy uppercase tracking-wide">Apple Touch Icon (180x180)</label>
                                    <span class="text-[10px] text-gray-400 font-mono">Square PNG</span>
                                </div>
                                
                                <div class="bg-white border border-gray-200 p-4 rounded-lg flex items-center justify-center min-h-[90px]">
                                    <img :src="branding.apple_touch_icon || '{{ asset('apple-touch-icon.png') }}'" alt="Apple Touch Icon Preview" class="w-12 h-12 rounded-xl object-contain shadow-sm border border-gray-200">
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-md transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <span>Upload Icon</span>
                                        <input type="file" @change="uploadAsset($event, 'apple_touch_icon')" accept="image/png" class="hidden">
                                    </label>
                                    <button type="button" x-show="branding.apple_touch_icon" @click="removeAsset('apple_touch_icon')" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: CONTACT & SOCIAL -->
                <div x-show="activeTab === 'contact'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-contact">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">03</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Contact &amp; Social Links</h3>
                                <p class="text-xs text-gray-500">Customer communication channels and official social media profile URLs.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="support_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Support Email Address
                                </label>
                                <input type="email" name="support_email" id="support_email" @input="markDirty()" value="{{ old('support_email', $settings['support_email'] ?? 'support@examtopicsbase.com') }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="contact_phone" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Phone Number
                                </label>
                                <input type="text" name="contact_phone" id="contact_phone" @input="markDirty()" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" placeholder="+1 (800) 123-4567" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="contact_whatsapp" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    WhatsApp Support Number / Link
                                </label>
                                <input type="text" name="contact_whatsapp" id="contact_whatsapp" @input="markDirty()" value="{{ old('contact_whatsapp', $settings['contact_whatsapp'] ?? '') }}" placeholder="+1234567890" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="business_address" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Business / Operational Address
                                </label>
                                <input type="text" name="business_address" id="business_address" @input="markDirty()" value="{{ old('business_address', $settings['business_address'] ?? '') }}" placeholder="123 Tech Center Blvd, San Francisco, CA" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>
                        </div>

                        <!-- Social Media Links (Only saved & displayed if valid URL) -->
                        <div class="pt-4 border-t border-gray-100">
                            <h4 class="text-xs font-bold text-navy uppercase tracking-wider mb-4">Official Social Profiles</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Facebook URL</label>
                                    <input type="url" name="social_facebook" @input="markDirty()" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" placeholder="https://facebook.com/yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">X (Twitter) URL</label>
                                    <input type="url" name="social_twitter" @input="markDirty()" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" placeholder="https://x.com/yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">LinkedIn URL</label>
                                    <input type="url" name="social_linkedin" @input="markDirty()" value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}" placeholder="https://linkedin.com/company/yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Instagram URL</label>
                                    <input type="url" name="social_instagram" @input="markDirty()" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" placeholder="https://instagram.com/yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">YouTube Channel URL</label>
                                    <input type="url" name="social_youtube" @input="markDirty()" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" placeholder="https://youtube.com/@yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Telegram Community URL</label>
                                    <input type="url" name="social_telegram" @input="markDirty()" value="{{ old('social_telegram', $settings['social_telegram'] ?? '') }}" placeholder="https://t.me/yourcommunity" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: SEO SETTINGS -->
                <div x-show="activeTab === 'seo'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-seo">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">04</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">SEO &amp; Search Engine Defaults</h3>
                                <p class="text-xs text-gray-500">Global fallback SEO metadata. Exam and blog specific settings will ALWAYS take priority.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Google SERP Live Preview -->
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4.5 space-y-2">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Live Google Search Preview</div>
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm max-w-xl space-y-1">
                                <div class="text-xs text-[#202124] flex items-center gap-1.5 font-sans">
                                    <span class="w-4 h-4 rounded-full bg-cyan/20 inline-flex items-center justify-center text-[10px] font-bold text-navy">E</span>
                                    <span class="text-xs text-[#4d5156]">{{ $settings['site_url'] ?? 'https://examtopicsbase.com' }}</span>
                                </div>
                                <h4 class="text-base text-[#1a0dab] hover:underline font-medium cursor-pointer truncate" x-text="seoTitle || 'Exam Topics Base - Pass Your IT Certification Exam First Attempt'"></h4>
                                <p class="text-xs text-[#4d5156] line-clamp-2 leading-relaxed" x-text="seoDescription || 'Prepare for any IT certification exam with Exam Topics Base. Download verified PDF dumps or practice online with our test engine.'"></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="default_seo_title" class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Default SEO Title</label>
                                    <span class="text-[10px] text-gray-400 font-mono"><span x-text="seoTitle.length"></span> / 60 chars</span>
                                </div>
                                <input type="text" name="default_seo_title" id="default_seo_title" x-model="seoTitle" @input="markDirty()" value="{{ old('default_seo_title', $settings['default_seo_title'] ?? config('seo.defaults.title')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="default_meta_description" class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Default Meta Description</label>
                                    <span class="text-[10px] text-gray-400 font-mono"><span x-text="seoDescription.length"></span> / 160 chars</span>
                                </div>
                                <textarea name="default_meta_description" id="default_meta_description" x-model="seoDescription" @input="markDirty()" rows="3" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">{{ old('default_meta_description', $settings['default_meta_description'] ?? config('seo.defaults.description')) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="default_meta_keywords" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Default Keywords</label>
                                    <input type="text" name="default_meta_keywords" id="default_meta_keywords" @input="markDirty()" value="{{ old('default_meta_keywords', $settings['default_meta_keywords'] ?? config('seo.defaults.keywords')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>

                                <div>
                                    <label for="robots_setting" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Robots Meta Directive</label>
                                    <select name="robots_setting" id="robots_setting" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                        <option value="index, follow" {{ old('robots_setting', $settings['robots_setting'] ?? 'index, follow') === 'index, follow' ? 'selected' : '' }}>index, follow (Allow search indexing - Recommended for Production)</option>
                                        <option value="noindex, nofollow" {{ old('robots_setting', $settings['robots_setting'] ?? '') === 'noindex, nofollow' ? 'selected' : '' }}>noindex, nofollow (Block search engines - Staging/Dev)</option>
                                        <option value="noindex, follow" {{ old('robots_setting', $settings['robots_setting'] ?? '') === 'noindex, follow' ? 'selected' : '' }}>noindex, follow</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: PROMOTION BANNER -->
                <div x-show="activeTab === 'promotion'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-promotion">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">05</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Header Promotion Banner</h3>
                                <p class="text-xs text-gray-500">Display global announcement banners, sales timers, and discount coupons.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Live Banner Preview Component -->
                        <div class="space-y-2">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Live Header Preview</div>
                            <div class="bg-gradient-to-r from-orange to-red-500 text-white py-2.5 px-4 text-center text-xs sm:text-sm shadow-md rounded-xl flex items-center justify-center flex-wrap gap-2 transition-all"
                                 :class="bannerActive == '1' ? 'opacity-100' : 'opacity-40 grayscale'">
                                <span class="font-bold" x-text="bannerText || '🔥 FLASH SALE! Use coupon NINJA50 for 50% off all bundles!'"></span>
                                <template x-if="bannerCoupon">
                                    <span class="bg-white text-orange px-2 py-0.5 rounded font-mono font-black text-xs mx-1" x-text="bannerCoupon"></span>
                                </template>
                                <template x-if="bannerButtonText">
                                    <span class="inline-flex items-center text-[10px] uppercase font-black bg-navy text-white px-2.5 py-1 rounded shadow-sm hover:bg-black transition ml-2" x-text="bannerButtonText"></span>
                                </template>
                            </div>
                            <div class="text-[11px] text-gray-400 italic text-center" x-show="bannerActive != '1'">
                                (Banner is currently set to Inactive / Hidden)
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                            <div class="md:col-span-2">
                                <label for="home_banner_text" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Banner Message <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="home_banner_text" id="home_banner_text" x-model="bannerText" @input="markDirty()" value="{{ old('home_banner_text', $settings['home_banner_text'] ?? '') }}" placeholder="e.g. FLASH SALE! Get 50% off all certification packs" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                            </div>

                            <div>
                                <label for="home_banner_active" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Banner Status
                                </label>
                                <select name="home_banner_active" id="home_banner_active" x-model="bannerActive" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-bold">
                                    <option value="0" {{ old('home_banner_active', $settings['home_banner_active'] ?? '0') === '0' ? 'selected' : '' }}>Inactive / Hidden</option>
                                    <option value="1" {{ old('home_banner_active', $settings['home_banner_active'] ?? '0') === '1' ? 'selected' : '' }}>Active / Visible</option>
                                </select>
                            </div>

                            <div>
                                <label for="home_banner_coupon" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Highlight Coupon Code (Optional)
                                </label>
                                <input type="text" name="home_banner_coupon" id="home_banner_coupon" x-model="bannerCoupon" @input="markDirty()" value="{{ old('home_banner_coupon', $settings['home_banner_coupon'] ?? '') }}" placeholder="e.g. NINJA50" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono uppercase font-bold text-orange">
                            </div>

                            <div>
                                <label for="home_banner_button_text" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Button Text (Optional)
                                </label>
                                <input type="text" name="home_banner_button_text" id="home_banner_button_text" x-model="bannerButtonText" @input="markDirty()" value="{{ old('home_banner_button_text', $settings['home_banner_button_text'] ?? '') }}" placeholder="e.g. Shop Now" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="home_banner_link" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Target Action URL
                                </label>
                                <input type="text" name="home_banner_link" id="home_banner_link" @input="markDirty()" value="{{ old('home_banner_link', $settings['home_banner_link'] ?? '#') }}" placeholder="e.g. /vendors" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="home_banner_start_date" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Campaign Start Date (Optional)
                                </label>
                                <input type="datetime-local" name="home_banner_start_date" id="home_banner_start_date" @input="markDirty()" value="{{ old('home_banner_start_date', isset($settings['home_banner_start_date']) ? date('Y-m-dTH:i', strtotime($settings['home_banner_start_date'])) : '') }}" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="home_banner_end_date" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Campaign End Date (Auto-expire)
                                </label>
                                <input type="datetime-local" name="home_banner_end_date" id="home_banner_end_date" @input="markDirty()" value="{{ old('home_banner_end_date', isset($settings['home_banner_end_date']) ? date('Y-m-dTH:i', strtotime($settings['home_banner_end_date'])) : '') }}" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 6: SUBSCRIPTION PLANS (Visual Plan Manager - NO RAW JSON) -->
                <div x-show="activeTab === 'subscriptions'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-subscriptions">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">06</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Subscription Plans &amp; Pricing</h3>
                                <p class="text-xs text-gray-500">Visual Plan Manager with chip features. Fully backward-compatible with CartController.</p>
                            </div>
                        </div>
                        <button type="button" @click="openPlanModal()" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase px-4 py-2 rounded-lg shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span>Add New Plan</span>
                        </button>
                    </div>

                    <!-- Hidden JSON field that syncs with form submit -->
                    <input type="hidden" name="subscription_plans" :value="JSON.stringify(plans)">

                    <div class="p-6 space-y-6">
                        <!-- Plans Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <template x-for="(plan, index) in plans" :key="index">
                                <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/50 hover:bg-white hover:border-cyan/40 hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-base font-black text-navy uppercase font-heading" x-text="plan.name"></h4>
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full"
                                                  :class="plan.status === 'disabled' ? 'bg-gray-200 text-gray-600' : 'bg-emerald-100 text-emerald-800'"
                                                  x-text="plan.status === 'disabled' ? 'Disabled' : 'Active'"></span>
                                        </div>

                                        <div class="flex items-baseline gap-2 mb-4">
                                            <div class="text-2xl font-black text-navy font-mono">
                                                $<span x-text="plan.price_monthly"></span><span class="text-xs text-gray-400 font-sans font-normal">/mo</span>
                                            </div>
                                            <span class="text-gray-300">|</span>
                                            <div class="text-xs font-bold text-gray-500 font-mono">
                                                $<span x-text="plan.price_annual"></span>/yr
                                            </div>
                                        </div>

                                        <!-- Features List -->
                                        <div class="space-y-1.5 border-t border-gray-200 pt-3">
                                            <template x-for="(feat, fIndex) in (plan.features || [])" :key="fIndex">
                                                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                                    <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span class="truncate" x-text="feat"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Plan Actions -->
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="editPlan(index)" class="p-1.5 text-gray-500 hover:text-navy hover:bg-gray-100 rounded transition" title="Edit Plan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button type="button" @click="duplicatePlan(index)" class="p-1.5 text-gray-500 hover:text-cyan hover:bg-gray-100 rounded transition" title="Duplicate Plan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                            </button>
                                            <button type="button" @click="togglePlanStatus(index)" class="p-1.5 text-gray-500 hover:text-orange hover:bg-gray-100 rounded transition" :title="plan.status === 'disabled' ? 'Enable' : 'Disable'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                            </button>
                                        </div>
                                        <button type="button" @click="deletePlan(index)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition" title="Delete Plan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: EMAIL SETTINGS -->
                <div x-show="activeTab === 'email'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-email">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">07</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Transactional Email Settings</h3>
                                <p class="text-xs text-gray-500">Configure outbound sender identity and administrative alerts. SMTP secrets remain secured in environment.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="mail_from_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Mail From Name
                                </label>
                                <input type="text" name="mail_from_name" id="mail_from_name" @input="markDirty()" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? config('mail.from.name', 'Exam Topics Base')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="mail_from_address" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Mail From Address
                                </label>
                                <input type="email" name="mail_from_address" id="mail_from_address" @input="markDirty()" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? config('mail.from.address', 'noreply@examtopicsbase.com')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="order_notification_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Order Notification Recipient Email
                                </label>
                                <input type="email" name="order_notification_email" id="order_notification_email" @input="markDirty()" value="{{ old('order_notification_email', $settings['order_notification_email'] ?? 'orders@examtopicsbase.com') }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="admin_notification_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    System Alerts &amp; Admin Email
                                </label>
                                <input type="email" name="admin_notification_email" id="admin_notification_email" @input="markDirty()" value="{{ old('admin_notification_email', $settings['admin_notification_email'] ?? 'admin@examtopicsbase.com') }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-xs text-gray-600 flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <span class="font-bold text-navy">Security Notice:</span> SMTP server credentials, Mailgun/Postmark/Resend API tokens, and secret passwords are managed via backend environment variables (<span class="font-mono text-navy font-bold">.env</span>) and are never exposed in administrative forms.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 8: PAYMENTS OPERATIONS CENTER -->
                <div x-show="activeTab === 'payments'" x-cloak class="space-y-6" id="section-payments">
                    
                    <!-- 1. LIVE VS TEST MODE WARNING BANNER -->
                    @if($paymentInfo['any_live_active'])
                        <div class="bg-gradient-to-r from-red-600 via-rose-600 to-red-700 text-white p-5 rounded-2xl shadow-lg border border-red-500/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-pulse">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-black uppercase tracking-wider">LIVE PAYMENTS ACTIVE</h3>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-white text-red-700 uppercase">Production Mode</span>
                                    </div>
                                    <p class="text-xs text-red-100 mt-0.5">Real customer cards and PayPal accounts are currently being charged. Exercise extreme caution with refunds and settings.</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-black/20 text-xs font-mono font-bold tracking-wide border border-white/20">
                                Real Gateway Connected
                            </span>
                        </div>
                    @else
                        <div class="bg-navy border border-cyan/30 text-white p-4.5 rounded-2xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-cyan/15 text-cyan flex items-center justify-center flex-shrink-0 font-bold text-xs">
                                    TEST
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xs font-black uppercase tracking-wider text-white">TEST / SANDBOX MODE</h3>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan/20 text-cyan border border-cyan/30">Safe Simulation</span>
                                    </div>
                                    <p class="text-[11px] text-gray-300 mt-0.5">Test cards and sandbox credentials active. No real credit cards will be billed.</p>
                                </div>
                            </div>
                            <div class="text-[11px] text-gray-400 font-mono">
                                Environment: <span class="text-cyan font-bold">Local Development</span>
                            </div>
                        </div>
                    @endif

                    <!-- 2. PAYMENT HEALTH PANEL -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-xs space-y-3">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-cyan animate-ping"></div>
                                <h4 class="text-xs font-black text-navy uppercase tracking-wider">Payment System Health Status</h4>
                            </div>
                            <span class="text-[10px] text-gray-400 font-mono">Real Diagnostics</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            <!-- Stripe API Health -->
                            <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/70 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-gray-500 uppercase tracking-wider text-[10px]">Stripe API</span>
                                        <span class="w-2 h-2 rounded-full {{ $paymentHealth['stripe_api']['status'] === 'healthy' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    </div>
                                    <div class="text-xs font-bold text-navy mt-1 truncate">{{ $paymentHealth['stripe_api']['label'] }}</div>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-2 truncate">{{ $paymentHealth['stripe_api']['description'] }}</div>
                            </div>

                            <!-- Stripe Webhook -->
                            <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/70 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-gray-500 uppercase tracking-wider text-[10px]">Stripe Webhook</span>
                                        <span class="w-2 h-2 rounded-full {{ $paymentHealth['stripe_webhook']['status'] === 'healthy' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    </div>
                                    <div class="text-xs font-bold text-navy mt-1 truncate">{{ $paymentHealth['stripe_webhook']['label'] }}</div>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-2 truncate">{{ $paymentHealth['stripe_webhook']['description'] }}</div>
                            </div>

                            <!-- PayPal API -->
                            <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/70 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-gray-500 uppercase tracking-wider text-[10px]">PayPal API</span>
                                        <span class="w-2 h-2 rounded-full {{ $paymentHealth['paypal_api']['status'] === 'healthy' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    </div>
                                    <div class="text-xs font-bold text-navy mt-1 truncate">{{ $paymentHealth['paypal_api']['label'] }}</div>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-2 truncate">{{ $paymentHealth['paypal_api']['description'] }}</div>
                            </div>

                            <!-- Database Records -->
                            <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/70 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-gray-500 uppercase tracking-wider text-[10px]">Database Records</span>
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    </div>
                                    <div class="text-xs font-bold text-navy mt-1 truncate">{{ $paymentHealth['database_records']['label'] }}</div>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-2 truncate">{{ $paymentHealth['database_records']['description'] }}</div>
                            </div>

                            <!-- Order Sync -->
                            <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/70 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-gray-500 uppercase tracking-wider text-[10px]">Failure Monitor</span>
                                        <span class="w-2 h-2 rounded-full {{ $paymentHealth['order_sync']['status'] === 'healthy' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    </div>
                                    <div class="text-xs font-bold text-navy mt-1 truncate">{{ $paymentHealth['order_sync']['label'] }}</div>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-2 truncate">{{ $paymentHealth['order_sync']['description'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. REAL PAYMENT OVERVIEW KPI METRICS WITH DATE FILTER -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                            <div>
                                <h3 class="text-sm font-black text-navy uppercase tracking-wider">Payment Revenue &amp; Performance Overview</h3>
                                <p class="text-xs text-gray-500">Aggregated live order and refund statistics calculated directly from database records.</p>
                            </div>

                            <!-- Date Filter Control -->
                            <div class="flex flex-wrap items-center gap-2">
                                <select onchange="window.location.href='{{ route('admin.settings.index') }}?tab=payments&payment_date_filter=' + this.value" class="text-xs font-bold text-navy border-gray-200 rounded-lg py-1.5 pl-3 pr-8 focus:border-cyan focus:ring-cyan bg-gray-50">
                                    <option value="all" {{ $paymentOverview['active_filter'] === 'all' ? 'selected' : '' }}>All Time</option>
                                    <option value="today" {{ $paymentOverview['active_filter'] === 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="7days" {{ $paymentOverview['active_filter'] === '7days' ? 'selected' : '' }}>Past 7 Days</option>
                                    <option value="30days" {{ $paymentOverview['active_filter'] === '30days' ? 'selected' : '' }}>Past 30 Days</option>
                                    <option value="90days" {{ $paymentOverview['active_filter'] === '90days' ? 'selected' : '' }}>Past 90 Days</option>
                                    <option value="custom" {{ $paymentOverview['active_filter'] === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                                </select>

                                @if($paymentOverview['active_filter'] === 'custom')
                                    <div class="flex items-center gap-1.5">
                                        <input type="date" id="payment_custom_from" value="{{ $paymentOverview['date_from'] }}" class="text-xs border-gray-200 rounded-lg py-1 px-2">
                                        <span class="text-xs text-gray-400">to</span>
                                        <input type="date" id="payment_custom_to" value="{{ $paymentOverview['date_to'] }}" class="text-xs border-gray-200 rounded-lg py-1 px-2">
                                        <button type="button" onclick="window.location.href='{{ route('admin.settings.index') }}?tab=payments&payment_date_filter=custom&payment_date_from=' + document.getElementById('payment_custom_from').value + '&payment_date_to=' + document.getElementById('payment_custom_to').value" class="px-2.5 py-1 bg-navy text-white text-xs font-bold rounded-lg hover:bg-navy/90">Apply</button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Metric Cards Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Total Revenue -->
                            <div class="p-4 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50/50 shadow-2xs space-y-1">
                                <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total Net Revenue</div>
                                <div class="text-xl sm:text-2xl font-black text-navy font-mono tracking-tight">${{ number_format($paymentOverview['total_revenue'], 2) }}</div>
                                <div class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Real settled revenue</span>
                                </div>
                            </div>

                            <!-- Successful Payments -->
                            <div class="p-4 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50/50 shadow-2xs space-y-1">
                                <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Successful Payments</div>
                                <div class="text-xl sm:text-2xl font-black text-emerald-600 font-mono tracking-tight">{{ number_format($paymentOverview['successful_payments']) }}</div>
                                <div class="text-[10px] text-gray-500 font-medium">Orders completed</div>
                            </div>

                            <!-- Success Rate -->
                            <div class="p-4 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50/50 shadow-2xs space-y-1">
                                <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Success Rate</div>
                                <div class="text-xl sm:text-2xl font-black text-cyan font-mono tracking-tight">{{ $paymentOverview['success_rate'] }}%</div>
                                <div class="text-[10px] text-gray-500 font-medium">{{ $paymentOverview['failed_payments'] }} failed attempts</div>
                            </div>

                            <!-- Total Refunded -->
                            <div class="p-4 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50/50 shadow-2xs space-y-1">
                                <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Refunded Amount</div>
                                <div class="text-xl sm:text-2xl font-black text-purple-700 font-mono tracking-tight">${{ number_format($paymentOverview['refunded_amount'], 2) }}</div>
                                <div class="text-[10px] text-purple-600 font-bold">{{ $paymentOverview['refund_count'] }} refunds recorded</div>
                            </div>
                        </div>

                        <!-- Secondary Meta Bar -->
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3.5 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2">
                            <div>
                                Pending Authorizations: <span class="font-bold text-navy">{{ $paymentOverview['pending_payments'] }} orders</span>
                            </div>
                            <div>
                                Last Successful Payment: <span class="font-bold text-navy">{{ $paymentOverview['last_successful_payment'] ? $paymentOverview['last_successful_payment']->diffForHumans() . ' (' . $paymentOverview['last_successful_payment']->format('M d, Y H:i') . ')' : 'No payments yet' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. PAYMENT GATEWAY OPERATIONAL CARDS -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- STRIPE CARD -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between">
                            <div>
                                <!-- Header -->
                                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-[#635BFF] text-white flex items-center justify-center font-black text-sm shadow-sm">
                                            S
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-sm font-black text-navy uppercase tracking-wide">Stripe Payments</h4>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="stripeData.is_live ? 'bg-red-100 text-red-700' : 'bg-cyan/10 text-cyan border border-cyan/30'" x-text="stripeData.mode">
                                                    {{ $paymentInfo['stripe_mode'] }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-gray-400">Credit / Debit Cards, Apple Pay, Google Pay</p>
                                        </div>
                                    </div>

                                    <!-- Switch Toggle -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="stripeEnabled" @change="toggleGatewayStatus('stripe')" class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-navy"></div>
                                    </label>
                                </div>

                                <!-- Body Details -->
                                <div class="p-6 space-y-3.5 text-xs">
                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Gateway Status</span>
                                        <span class="font-bold flex items-center gap-1.5" :class="stripeData.configured ? 'text-emerald-700' : 'text-amber-700'">
                                            <span class="w-2 h-2 rounded-full" :class="stripeData.configured ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                            <span x-text="stripeData.configured ? 'Connected' : 'Not Configured (Mock Mode)'">{{ $paymentInfo['stripe_configured'] ? 'Connected' : 'Not Configured (Mock Mode)' }}</span>
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Publishable Key</span>
                                        <span class="font-mono text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[11px]" x-text="stripeData.public_key">{{ $paymentInfo['stripe_public_key'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Secret Key</span>
                                        <span class="font-mono text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[11px]" x-text="stripeData.masked_secret">{{ $paymentInfo['stripe_masked_secret'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Webhook Endpoint</span>
                                        <span class="font-mono text-navy text-[11px] truncate max-w-[200px]" title="{{ $paymentInfo['stripe_webhook_url'] }}">{{ $paymentInfo['stripe_webhook_url'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Webhook Signing Secret</span>
                                        <span class="font-mono text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[11px]" x-text="stripeData.masked_webhook">{{ $paymentInfo['stripe_masked_webhook'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1">
                                        <span class="text-gray-500 font-medium">Last Webhook Received</span>
                                        <span class="font-medium text-gray-700">{{ $paymentInfo['stripe_last_webhook'] ? $paymentInfo['stripe_last_webhook']->diffForHumans() : 'None recorded yet' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions Footer -->
                            <div class="p-6 bg-gray-50/50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="openStripeModal()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold text-white bg-navy hover:bg-navy/90 shadow-2xs transition">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        <span>Edit Credentials</span>
                                    </button>

                                    <button type="button" @click="testGatewayConnection('stripe')" :disabled="isTestingStripe" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold text-navy bg-white border border-gray-200 hover:bg-gray-50 shadow-2xs transition disabled:opacity-50">
                                        <svg class="w-3.5 h-3.5 text-cyan" :class="isTestingStripe ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        <span x-text="isTestingStripe ? 'Testing...' : 'Test Stripe API'">Test Stripe API</span>
                                    </button>
                                </div>
                                
                                <span class="text-[10px] text-gray-400 font-mono">Stripe SDK v10+</span>
                            </div>
                        </div>

                        <!-- PAYPAL CARD -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between">
                            <div>
                                <!-- Header -->
                                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-[#003087] text-white flex items-center justify-center font-black text-sm shadow-sm">
                                            P
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-sm font-black text-navy uppercase tracking-wide">PayPal Standard &amp; Express</h4>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="paypalData.is_live ? 'bg-red-100 text-red-700' : 'bg-cyan/10 text-cyan border border-cyan/30'" x-text="paypalData.mode">
                                                    {{ $paymentInfo['paypal_mode'] }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-gray-400">PayPal Wallet, Pay in 4, Subscriptions</p>
                                        </div>
                                    </div>

                                    <!-- Switch Toggle -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="paypalEnabled" @change="toggleGatewayStatus('paypal')" class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-navy"></div>
                                    </label>
                                </div>

                                <!-- Body Details -->
                                <div class="p-6 space-y-3.5 text-xs">
                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Gateway Status</span>
                                        <span class="font-bold flex items-center gap-1.5" :class="paypalData.configured ? 'text-emerald-700' : 'text-amber-700'">
                                            <span class="w-2 h-2 rounded-full" :class="paypalData.configured ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                            <span x-text="paypalData.configured ? 'Connected' : 'Sandbox (Mock Mode)'">{{ $paymentInfo['paypal_configured'] ? 'Connected' : 'Sandbox (Mock Mode)' }}</span>
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Client ID</span>
                                        <span class="font-mono text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[11px]" x-text="paypalData.client_id">{{ $paymentInfo['paypal_client_id'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Client Secret</span>
                                        <span class="font-mono text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[11px]" x-text="paypalData.masked_secret">{{ $paymentInfo['paypal_masked_secret'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Webhook Endpoint</span>
                                        <span class="font-mono text-navy text-[11px] truncate max-w-[200px]" title="{{ $paymentInfo['paypal_webhook_url'] }}">{{ $paymentInfo['paypal_webhook_url'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Webhook ID</span>
                                        <span class="font-mono text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[11px]" x-text="paypalData.webhook_id || 'Not configured'">{{ $paymentInfo['paypal_webhook_id'] ?: 'Not configured' }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                        <span class="text-gray-500 font-medium">Environment Mode</span>
                                        <span class="font-bold text-navy" x-text="paypalData.mode">{{ $paymentInfo['paypal_mode'] }}</span>
                                    </div>

                                    <div class="flex items-center justify-between py-1">
                                        <span class="text-gray-500 font-medium">Last Webhook Received</span>
                                        <span class="font-medium text-gray-700">{{ $paymentInfo['paypal_last_webhook'] ? $paymentInfo['paypal_last_webhook']->diffForHumans() : 'None recorded yet' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions Footer -->
                            <div class="p-6 bg-gray-50/50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="openPayPalModal()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold text-white bg-navy hover:bg-navy/90 shadow-2xs transition">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        <span>Edit Credentials</span>
                                    </button>

                                    <button type="button" @click="testGatewayConnection('paypal')" :disabled="isTestingPayPal" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold text-navy bg-white border border-gray-200 hover:bg-gray-50 shadow-2xs transition disabled:opacity-50">
                                        <svg class="w-3.5 h-3.5 text-cyan" :class="isTestingPayPal ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        <span x-text="isTestingPayPal ? 'Testing...' : 'Test PayPal API'">Test PayPal API</span>
                                    </button>
                                </div>

                                <span class="text-[10px] text-gray-400 font-mono">srmklive/paypal</span>
                            </div>
                        </div>
                    </div>

                    <!-- CONNECTION TEST RESULT MODAL/TOAST -->
                    <div x-show="testResult" x-cloak class="p-4 rounded-xl border transition" :class="testResult && testResult.success ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900'">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" :class="testResult && testResult.success ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div class="space-y-1">
                                    <h5 class="text-xs font-black uppercase tracking-wide" x-text="(testResult && testResult.gateway) + ' Connection Result: ' + (testResult && testResult.status)"></h5>
                                    <p class="text-xs font-medium" x-text="testResult && testResult.message"></p>
                                    <template x-if="testResult && testResult.details">
                                        <div class="text-[11px] font-mono text-gray-600 bg-white/70 p-2 rounded border border-gray-200 mt-2">
                                            <span x-text="JSON.stringify(testResult.details)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <button type="button" @click="testResult = null" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                        </div>
                    </div>

                    <!-- 5. REAL TRANSACTIONS TABLE -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden space-y-4">
                        <div class="p-6 pb-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-black text-navy uppercase tracking-wider">Payment Transactions</h3>
                                <p class="text-xs text-gray-500">Live order payments with instant detail drawer and refund controls.</p>
                            </div>

                            <!-- Filter Controls -->
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="text" id="tx_search_input" value="{{ request('tx_search') }}" placeholder="Search order #, customer, ID..." class="text-xs border-gray-200 rounded-lg py-1.5 px-3 w-44 focus:border-cyan focus:ring-cyan" onkeydown="if(event.key==='Enter'){event.preventDefault(); applyTxFilter();}">

                                <select id="tx_gateway_select" onchange="applyTxFilter()" class="text-xs border-gray-200 rounded-lg py-1.5 pl-2.5 pr-7 focus:border-cyan focus:ring-cyan">
                                    <option value="">All Gateways</option>
                                    <option value="stripe" {{ request('tx_gateway') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                                    <option value="paypal" {{ request('tx_gateway') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                                </select>

                                <select id="tx_status_select" onchange="applyTxFilter()" class="text-xs border-gray-200 rounded-lg py-1.5 pl-2.5 pr-7 focus:border-cyan focus:ring-cyan">
                                    <option value="">All Statuses</option>
                                    <option value="paid" {{ request('tx_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="pending" {{ request('tx_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="failed" {{ request('tx_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="refunded" {{ request('tx_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>

                                <button type="button" onclick="applyTxFilter()" class="px-3 py-1.5 bg-navy text-white text-xs font-bold rounded-lg hover:bg-navy/90 transition">Filter</button>
                            </div>
                            <script>
                            function applyTxFilter() {
                                const s = encodeURIComponent(document.getElementById('tx_search_input')?.value || '');
                                const g = encodeURIComponent(document.getElementById('tx_gateway_select')?.value || '');
                                const st = encodeURIComponent(document.getElementById('tx_status_select')?.value || '');
                                window.location.href = '{{ route('admin.settings.index') }}?tab=payments&tx_search=' + s + '&tx_gateway=' + g + '&tx_status=' + st;
                            }
                            </script>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/80 text-[10px] font-black uppercase tracking-wider text-gray-400 border-y border-gray-200">
                                    <tr>
                                        <th class="py-3 px-6">Order Number</th>
                                        <th class="py-3 px-6">Customer</th>
                                        <th class="py-3 px-6">Gateway</th>
                                        <th class="py-3 px-6">Amount</th>
                                        <th class="py-3 px-6">Status</th>
                                        <th class="py-3 px-6">Date</th>
                                        <th class="py-3 px-6 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($transactions as $tx)
                                        <tr class="hover:bg-gray-50/60 transition">
                                            <td class="py-3.5 px-6 font-bold text-navy font-mono">
                                                #{{ $tx->order_number }}
                                            </td>
                                            <td class="py-3.5 px-6">
                                                <div class="font-bold text-navy">{{ $tx->billing_name ?: ($tx->user->name ?? 'Customer') }}</div>
                                                <div class="text-[11px] text-gray-400 truncate max-w-[150px]">{{ $tx->billing_email ?: ($tx->user->email ?? 'N/A') }}</div>
                                            </td>
                                            <td class="py-3.5 px-6">
                                                <span class="inline-flex items-center gap-1.5 font-bold uppercase text-[11px] {{ $tx->payment_method === 'stripe' ? 'text-[#635BFF]' : 'text-[#003087]' }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $tx->payment_method === 'stripe' ? 'bg-[#635BFF]' : 'bg-[#003087]' }}"></span>
                                                    {{ $tx->payment_method }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-6 font-mono font-bold text-navy">
                                                ${{ number_format((float)$tx->total_amount, 2) }}
                                                @if((float)$tx->refunded_amount > 0)
                                                    <span class="block text-[10px] text-purple-600 font-normal">-${{ number_format((float)$tx->refunded_amount, 2) }} ref.</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-6">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $tx->status_badge['bg'] }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $tx->status_badge['dot'] }}"></span>
                                                    <span>{{ $tx->status_badge['label'] }}</span>
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-6 text-gray-400 whitespace-nowrap">
                                                {{ $tx->created_at->format('M d, Y') }}
                                                <span class="block text-[10px]">{{ $tx->created_at->format('H:i') }}</span>
                                            </td>
                                            <td class="py-3.5 px-6 text-right whitespace-nowrap space-x-2">
                                                <button type="button" @click="openTxDetails({{ $tx->id }})" class="px-2.5 py-1 text-xs font-bold text-navy bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                                    Details
                                                </button>
                                                @if($tx->isRefundable())
                                                    <button type="button" @click="openRefundModal({{ json_encode(['id' => $tx->id, 'order_number' => $tx->order_number, 'total_amount' => $tx->total_amount, 'remaining_refundable' => $tx->remainingRefundableAmount()]) }})" class="px-2.5 py-1 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition">
                                                        Refund
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-8 text-center text-xs text-gray-400">
                                                No payment transactions found matching the selected criteria.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Links -->
                        <div class="p-4 border-t border-gray-100">
                            {{ $transactions->links() }}
                        </div>
                    </div>

                    <!-- 6. WEBHOOK MONITORING & ACTIVITY LOGS TABS -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- WEBHOOK MONITORING -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between">
                            <div>
                                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-cyan"></div>
                                        <h4 class="text-xs font-black text-navy uppercase tracking-wide">Recent Webhook Deliveries</h4>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-mono">Real-time Inbound</span>
                                </div>

                                <div class="divide-y divide-gray-100 max-h-[360px] overflow-y-auto">
                                    @forelse($webhookLogs as $hook)
                                        <div class="p-3.5 hover:bg-gray-50/60 transition flex items-center justify-between gap-3 text-xs">
                                            <div class="space-y-0.5 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-navy uppercase text-[11px]">{{ $hook->gateway }}</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase {{ $hook->status_badge['bg'] }}">{{ $hook->status }}</span>
                                                    @if($hook->processing_time_ms)
                                                        <span class="text-[10px] text-gray-400 font-mono">{{ $hook->processing_time_ms }}ms</span>
                                                    @endif
                                                </div>
                                                <div class="font-mono text-[11px] text-gray-600 truncate">{{ $hook->event_type }}</div>
                                                <div class="text-[10px] text-gray-400">{{ $hook->created_at->diffForHumans() }}</div>
                                            </div>

                                            <button type="button" @click="openWebhookModal({{ json_encode($hook) }})" class="px-2 py-1 text-[11px] font-bold text-navy bg-gray-100 hover:bg-gray-200 rounded transition flex-shrink-0">
                                                Inspect
                                            </button>
                                        </div>
                                    @empty
                                        <div class="p-8 text-center text-xs text-gray-400">
                                            No webhook deliveries recorded yet. Inbound events will be logged here automatically.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="p-3.5 bg-gray-50/50 border-t border-gray-100 text-[11px] text-gray-500 flex items-center justify-between">
                                <span>Total Logged: {{ $webhookLogs->count() }}</span>
                                <span class="font-mono text-[10px]">Auto-tracked via Controller</span>
                            </div>
                        </div>

                        <!-- PAYMENT AUDIT ACTIVITY LOGS -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between">
                            <div>
                                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-orange"></div>
                                        <h4 class="text-xs font-black text-navy uppercase tracking-wide">Payment Activity &amp; Audit Trail</h4>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-mono">Gateway Events</span>
                                </div>

                                <div class="divide-y divide-gray-100 max-h-[360px] overflow-y-auto">
                                    @forelse($activityLogs as $act)
                                        <div class="p-3.5 hover:bg-gray-50/60 transition space-y-1 text-xs">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-black text-navy uppercase text-[10px]">{{ $act->gateway ?? 'SYSTEM' }}</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $act->status === 'success' ? 'bg-emerald-50 text-emerald-700' : ($act->status === 'error' ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-700') }}">
                                                        {{ $act->event }}
                                                    </span>
                                                </div>
                                                <span class="text-[10px] text-gray-400">{{ $act->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-[11px] text-gray-600">{{ $act->message }}</p>
                                        </div>
                                    @empty
                                        <div class="p-8 text-center text-xs text-gray-400">
                                            No payment activities logged yet. Gateway tests and refunds will appear here.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="p-3.5 bg-gray-50/50 border-t border-gray-100 text-[11px] text-gray-500 flex items-center justify-between">
                                <span>Recent Activities: {{ $activityLogs->count() }}</span>
                                <span class="font-mono text-[10px]">CSRF &amp; Auth Protected</span>
                            </div>
                        </div>
                    </div>

                    <!-- 7. PAYMENT SETTINGS & CURRENCY CONFIGURATION FORM -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xs space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h3 class="text-sm font-black text-navy uppercase tracking-wider">Payment &amp; Checkout Configuration</h3>
                            <p class="text-xs text-gray-500">Supported platform settings. Saved with administrator CSRF authorization.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="default_currency" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Default Currency <span class="text-red-500">*</span>
                                </label>
                                <select name="default_currency" id="default_currency" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-bold">
                                    <option value="USD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD ($) — United States Dollar</option>
                                    <option value="EUR" {{ old('default_currency', $settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (€) — Euro</option>
                                    <option value="GBP" {{ old('default_currency', $settings['default_currency'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP (£) — British Pound</option>
                                    <option value="CAD" {{ old('default_currency', $settings['default_currency'] ?? '') === 'CAD' ? 'selected' : '' }}>CAD ($) — Canadian Dollar</option>
                                    <option value="AUD" {{ old('default_currency', $settings['default_currency'] ?? '') === 'AUD' ? 'selected' : '' }}>AUD ($) — Australian Dollar</option>
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1">Primary currency passed to Stripe Elements and PayPal orders.</p>
                            </div>

                            <div>
                                <label for="payment_receipt_auto_send" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Customer Invoice Receipts
                                </label>
                                <select name="payment_receipt_auto_send" id="payment_receipt_auto_send" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                    <option value="1" {{ old('payment_receipt_auto_send', $settings['payment_receipt_auto_send'] ?? '1') === '1' ? 'selected' : '' }}>Automatically dispatch PDF invoice email upon payment success</option>
                                    <option value="0" {{ old('payment_receipt_auto_send', $settings['payment_receipt_auto_send'] ?? '') === '0' ? 'selected' : '' }}>Manual dispatch only</option>
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1">Generates Barryvdh Dompdf invoice and dispatches to billing email.</p>
                            </div>

                            <div>
                                <label for="payment_failure_notify_admin" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Payment Failure Alert
                                </label>
                                <select name="payment_failure_notify_admin" id="payment_failure_notify_admin" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                    <option value="1" {{ old('payment_failure_notify_admin', $settings['payment_failure_notify_admin'] ?? '1') === '1' ? 'selected' : '' }}>Log and alert administrator on recurring invoice failure</option>
                                    <option value="0" {{ old('payment_failure_notify_admin', $settings['payment_failure_notify_admin'] ?? '') === '0' ? 'selected' : '' }}>Log only</option>
                                </select>
                            </div>

                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-xs text-gray-600 flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                <div>
                                    <span class="font-bold text-navy">Security Policy:</span> Secret keys (<span class="font-mono text-navy font-bold">STRIPE_SECRET</span>, <span class="font-mono text-navy font-bold">PAYPAL_CLIENT_SECRET</span>, <span class="font-mono text-navy font-bold">STRIPE_WEBHOOK_SECRET</span>) are never stored in plain form inputs. Always configure them in your local <span class="font-mono text-navy font-bold">.env</span> file.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- TAB 9: MAINTENANCE MODE -->
                <div x-show="activeTab === 'maintenance'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-maintenance">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">09</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Maintenance Mode</h3>
                                <p class="text-xs text-gray-500">Emergency website offline toggle. Authenticated administrators automatically bypass offline screen.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="maintenance_mode" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Maintenance Status
                                </label>
                                <select name="maintenance_mode" id="maintenance_mode" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-bold">
                                    <option value="false" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? 'false') === 'false' ? 'selected' : '' }}>Disabled (Site Online for all visitors)</option>
                                    <option value="true" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? 'false') === 'true' ? 'selected' : '' }}>Enabled (Site Offline / Maintenance Notice)</option>
                                </select>
                            </div>

                            <div>
                                <label for="maintenance_return_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Expected Return Notice (Optional)
                                </label>
                                <input type="text" name="maintenance_return_time" id="maintenance_return_time" @input="markDirty()" value="{{ old('maintenance_return_time', $settings['maintenance_return_time'] ?? '') }}" placeholder="e.g. Back online today at 4:00 PM EST" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>
                        </div>

                        <div>
                            <label for="maintenance_message" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                Public Maintenance Message
                            </label>
                            <textarea name="maintenance_message" id="maintenance_message" @input="markDirty()" rows="3" placeholder="We are performing brief scheduled system maintenance. All user progress is secure." class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">{{ old('maintenance_message', $settings['maintenance_message'] ?? 'We are performing brief scheduled system maintenance to improve our practice engine. All purchased guides are secure. We will be back online shortly!') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- TAB 10: SECURITY -->
                <div x-show="activeTab === 'security'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-security">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">10</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Security &amp; Session Management</h3>
                                <p class="text-xs text-gray-500">Control session timeout limits, brute-force rate limits, and password criteria.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="session_timeout_minutes" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Session Lifetime (Minutes)
                                </label>
                                <input type="number" name="session_timeout_minutes" id="session_timeout_minutes" @input="markDirty()" value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes'] ?? '120') }}" min="15" max="1440" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono">
                            </div>

                            <div>
                                <label for="max_login_attempts" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Max Failed Login Attempts
                                </label>
                                <input type="number" name="max_login_attempts" id="max_login_attempts" @input="markDirty()" value="{{ old('max_login_attempts', $settings['max_login_attempts'] ?? '5') }}" min="3" max="20" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono">
                            </div>

                            <div>
                                <label for="password_min_length" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Minimum Password Length
                                </label>
                                <input type="number" name="password_min_length" id="password_min_length" @input="markDirty()" value="{{ old('password_min_length', $settings['password_min_length'] ?? '8') }}" min="8" max="32" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Sticky Floating Save Bar -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <span class="text-xs text-gray-400">ExamTopicsBase Core Engine v2.0</span>
                    <button type="submit" :disabled="isSubmitting" class="bg-navy hover:bg-navy/90 text-white font-bold text-xs uppercase tracking-wider px-7 py-3 rounded-lg shadow-md transition disabled:opacity-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span x-text="isSubmitting ? 'Saving...' : 'Save Settings'">Save Settings</span>
                    </button>
                </div>
            </form>

            <!-- TAB 11: ADVANCED & SYSTEM CACHE TOOLS (Separate standalone form for cache clears) -->
            <div x-show="activeTab === 'advanced'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-advanced">
                <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">11</span>
                        <div>
                            <h3 class="text-sm font-bold text-navy uppercase tracking-wide">System &amp; Cache Maintenance</h3>
                            <p class="text-xs text-gray-500">Safely clear application and view caches when changes are made.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Application Cache -->
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-navy uppercase">App Data Cache</h4>
                                <p class="text-[11px] text-gray-500 mt-1">Clears application query caches, statistics caches, and keys.</p>
                            </div>
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirm('Clear application cache?')">
                                @csrf
                                <input type="hidden" name="cache_type" value="application">
                                <button type="submit" class="w-full text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 py-1.5 px-3 rounded-lg transition shadow-sm">
                                    Clear App Cache
                                </button>
                            </form>
                        </div>

                        <!-- View Cache -->
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-navy uppercase">Blade Views Cache</h4>
                                <p class="text-[11px] text-gray-500 mt-1">Purges all compiled Blade template views from storage.</p>
                            </div>
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirm('Clear compiled Blade view templates?')">
                                @csrf
                                <input type="hidden" name="cache_type" value="view">
                                <button type="submit" class="w-full text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 py-1.5 px-3 rounded-lg transition shadow-sm">
                                    Clear Views Cache
                                </button>
                            </form>
                        </div>

                        <!-- Route Cache -->
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-navy uppercase">Routes Cache</h4>
                                <p class="text-[11px] text-gray-500 mt-1">Clears registered route mappings and controller lookups.</p>
                            </div>
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirm('Clear route cache?')">
                                @csrf
                                <input type="hidden" name="cache_type" value="route">
                                <button type="submit" class="w-full text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 py-1.5 px-3 rounded-lg transition shadow-sm">
                                    Clear Route Cache
                                </button>
                            </form>
                        </div>

                        <!-- Flush All -->
                        <div class="border border-orange/30 rounded-xl p-4 bg-orange/5 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-orange uppercase">Flush All Caches</h4>
                                <p class="text-[11px] text-gray-600 mt-1">Safely flushes application, views, routes, and config.</p>
                            </div>
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirm('Are you sure you want to flush all system and template caches?')">
                                @csrf
                                <input type="hidden" name="cache_type" value="all">
                                <button type="submit" class="w-full text-xs font-bold text-white bg-orange hover:bg-orange/90 py-1.5 px-3 rounded-lg transition shadow-sm">
                                    Flush All Caches
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PLAN EDITOR MODAL (Interactive Drawer) -->
    <div x-show="planModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Background Backdrop -->
            <div x-show="planModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="planModalOpen = false" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity"></div>

            <!-- Modal Dialog Content -->
            <div x-show="planModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-gray-200">
                
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-base font-black text-navy uppercase font-heading" x-text="editingPlanIndex !== null ? 'Edit Subscription Plan' : 'Create New Subscription Plan'"></h3>
                    <button type="button" @click="planModalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 pt-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Plan Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="currentPlan.name" placeholder="e.g. Pro, Ultimate, Team" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-bold">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Monthly Price ($)</label>
                            <input type="number" step="0.01" min="0" x-model.number="currentPlan.price_monthly" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Annual Price ($)</label>
                            <input type="number" step="0.01" min="0" x-model.number="currentPlan.price_annual" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Plan Features</label>
                        <div class="flex gap-2 mb-2">
                            <input type="text" x-model="newFeatureText" @keydown.enter.prevent="addFeature()" placeholder="Type a feature and press Enter or Add..." class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            <button type="button" @click="addFeature()" class="px-3 py-1.5 bg-navy text-white text-xs font-bold rounded-lg hover:bg-navy/90 transition">Add</button>
                        </div>

                        <!-- Feature Chips -->
                        <div class="flex flex-wrap gap-1.5 p-2 bg-gray-50 border border-gray-200 rounded-lg min-h-[50px]">
                            <template x-for="(feat, fIdx) in currentPlan.features" :key="fIdx">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white text-navy border border-gray-200 shadow-2xs">
                                    <span x-text="feat"></span>
                                    <button type="button" @click="removeFeature(fIdx)" class="text-gray-400 hover:text-red-500 font-bold ml-1">&times;</button>
                                </span>
                            </template>
                            <span x-show="!currentPlan.features.length" class="text-xs text-gray-400 italic">No features added yet.</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Plan Status</label>
                        <select x-model="currentPlan.status" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            <option value="active">Active (Available for customer subscription)</option>
                            <option value="disabled">Disabled (Hidden from public checkout)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="planModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button" @click="savePlan()" class="px-5 py-2 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-lg shadow transition">
                        Save Plan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TRANSACTION DETAILS MODAL DRAWER -->
    <div x-show="txDrawerOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Backdrop -->
            <div x-show="txDrawerOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="txDrawerOpen = false" class="absolute inset-0 bg-navy/60 backdrop-blur-xs transition-opacity"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div x-show="txDrawerOpen" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-md bg-white shadow-2xl border-l border-gray-200 flex flex-col justify-between">
                    
                    <div>
                        <!-- Header -->
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-black text-navy uppercase tracking-wider">Transaction Details</h3>
                                <p class="text-xs text-gray-500" x-text="txData ? '#' + txData.order_number : 'Loading...'"></p>
                            </div>
                            <button type="button" @click="txDrawerOpen = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                        </div>

                        <!-- Content Loading or Loaded -->
                        <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-140px)]">
                            <template x-if="txLoading">
                                <div class="py-12 text-center text-xs text-gray-400 animate-pulse">
                                    Loading real transaction records...
                                </div>
                            </template>

                            <template x-if="!txLoading && txData">
                                <div class="space-y-5 text-xs">
                                    <!-- Status Banner -->
                                    <div class="p-3.5 rounded-xl border flex items-center justify-between" :class="txData.status_badge.bg">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" :class="txData.status_badge.dot"></span>
                                            <span class="font-bold uppercase text-[11px]" x-text="txData.status_badge.label"></span>
                                        </div>
                                        <span class="font-mono font-black text-sm" x-text="'$' + txData.total_amount"></span>
                                    </div>

                                    <!-- Customer Info -->
                                    <div class="space-y-2 border-b border-gray-100 pb-4">
                                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Customer Details</div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Name</span>
                                            <span class="font-bold text-navy" x-text="txData.customer_name"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Billing Email</span>
                                            <span class="font-bold text-navy" x-text="txData.customer_email"></span>
                                        </div>
                                    </div>

                                    <!-- Gateway Info -->
                                    <div class="space-y-2 border-b border-gray-100 pb-4">
                                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Gateway Information</div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Method</span>
                                            <span class="font-bold uppercase" x-text="txData.payment_method"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Reference ID</span>
                                            <span class="font-mono text-gray-700 truncate max-w-[200px]" x-text="txData.gateway_reference"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Created At</span>
                                            <span class="text-gray-700" x-text="txData.created_at"></span>
                                        </div>
                                    </div>

                                    <!-- Items -->
                                    <div class="space-y-2 border-b border-gray-100 pb-4">
                                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Purchased Items</div>
                                        <template x-for="(item, idx) in txData.items" :key="idx">
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <div>
                                                    <div class="font-bold text-navy" x-text="item.title"></div>
                                                    <div class="text-[10px] text-gray-400 uppercase" x-text="item.type"></div>
                                                </div>
                                                <span class="font-mono font-bold text-navy" x-text="'$' + item.price"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Refunds List -->
                                    <template x-if="txData.refunds && txData.refunds.length">
                                        <div class="space-y-2">
                                            <div class="text-[10px] font-black uppercase tracking-wider text-purple-600">Issued Refunds</div>
                                            <template x-for="ref in txData.refunds" :key="ref.id">
                                                <div class="p-2.5 rounded-lg bg-purple-50 border border-purple-100 text-[11px] space-y-1">
                                                    <div class="flex justify-between font-bold text-purple-900">
                                                        <span x-text="'Refund #' + ref.id"></span>
                                                        <span x-text="'-$' + ref.amount"></span>
                                                    </div>
                                                    <div class="text-gray-500 text-[10px]" x-text="'Reason: ' + (ref.reason || 'None')"></div>
                                                    <div class="text-gray-400 text-[9px]" x-text="ref.date + ' by ' + ref.admin"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Drawer Footer -->
                    <div class="p-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between gap-3">
                        <button type="button" @click="txDrawerOpen = false" class="w-full px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-200 rounded-lg transition">
                            Close
                        </button>
                        <template x-if="txData && txData.is_refundable">
                            <button type="button" @click="openRefundModal(txData); txDrawerOpen = false" class="w-full px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition shadow-sm">
                                Issue Refund
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- REFUND TRANSACTION MODAL -->
    <div x-show="refundModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="refundModalOpen" @click="refundModalOpen = false" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="refundModalOpen" class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-gray-200">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-bold">
                            $
                        </div>
                        <h3 class="text-sm font-black text-navy uppercase">Issue Customer Refund</h3>
                    </div>
                    <button type="button" @click="refundModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                </div>

                <div class="space-y-4 pt-4 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex justify-between items-center">
                        <span class="text-gray-600">Order Number</span>
                        <span class="font-mono font-bold text-navy" x-text="'#' + (refundOrder && refundOrder.order_number)"></span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex justify-between items-center">
                        <span class="text-gray-600">Remaining Refundable Balance</span>
                        <span class="font-mono font-bold text-emerald-700" x-text="'$' + (refundOrder && (refundOrder.remaining_refundable || refundOrder.total_amount))"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Refund Type</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="refundType = 'full'; refundAmount = refundOrder.remaining_refundable || refundOrder.total_amount" class="p-2 text-xs font-bold rounded-lg border text-center transition" :class="refundType === 'full' ? 'bg-navy text-white border-navy' : 'bg-white text-gray-700 border-gray-200'">
                                Full Refund
                            </button>
                            <button type="button" @click="refundType = 'partial'" class="p-2 text-xs font-bold rounded-lg border text-center transition" :class="refundType === 'partial' ? 'bg-navy text-white border-navy' : 'bg-white text-gray-700 border-gray-200'">
                                Partial Refund
                            </button>
                        </div>
                    </div>

                    <div x-show="refundType === 'partial'">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Partial Refund Amount ($)</label>
                        <input type="number" step="0.01" min="0.01" :max="refundOrder && (refundOrder.remaining_refundable || refundOrder.total_amount)" x-model="refundAmount" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Reason for Refund</label>
                        <select x-model="refundReason" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            <option value="Requested by customer">Requested by customer</option>
                            <option value="Duplicate charge">Duplicate charge</option>
                            <option value="Fraudulent transaction">Fraudulent transaction</option>
                            <option value="Product dissatisfaction">Product dissatisfaction</option>
                            <option value="Administrative correction">Administrative correction</option>
                        </select>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer pt-1">
                        <input type="checkbox" x-model="revokeAccess" class="rounded text-navy focus:ring-cyan">
                        <span class="text-xs text-gray-700 font-medium">Revoke exam and certification access immediately</span>
                    </label>

                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900">
                        <strong>Warning:</strong> This will dispatch an actual refund request to the live/test payment gateway. This operation cannot be undone.
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="refundModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button" @click="executeRefund()" :disabled="isRefunding" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow transition disabled:opacity-50">
                        <span x-text="isRefunding ? 'Processing...' : 'Confirm & Issue Refund'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- WEBHOOK PAYLOAD INSPECTOR MODAL -->
    <div x-show="webhookPayloadModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="webhookPayloadModalOpen" @click="webhookPayloadModalOpen = false" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="webhookPayloadModalOpen" class="relative inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-gray-200">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-sm font-black text-navy uppercase" x-text="'Webhook Delivery #' + (selectedWebhook && selectedWebhook.id)"></h3>
                        <p class="text-[11px] text-gray-400 font-mono" x-text="selectedWebhook && selectedWebhook.event_type"></p>
                    </div>
                    <button type="button" @click="webhookPayloadModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                </div>

                <div class="space-y-4 pt-4 text-xs">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-2.5 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-gray-400 uppercase text-[10px] block font-bold">Gateway</span>
                            <span class="font-bold uppercase text-navy" x-text="selectedWebhook && selectedWebhook.gateway"></span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-gray-400 uppercase text-[10px] block font-bold">Status</span>
                            <span class="font-bold uppercase" :class="selectedWebhook && selectedWebhook.status === 'processed' ? 'text-emerald-700' : 'text-rose-700'" x-text="selectedWebhook && selectedWebhook.status"></span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-gray-400 uppercase text-[10px] block font-bold">Duration</span>
                            <span class="font-mono font-bold text-navy" x-text="(selectedWebhook && selectedWebhook.processing_time_ms ? selectedWebhook.processing_time_ms + 'ms' : 'N/A')"></span>
                        </div>
                    </div>

                    <template x-if="selectedWebhook && selectedWebhook.error_message">
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                            <span class="font-bold">Error:</span> <span x-text="selectedWebhook.error_message"></span>
                        </div>
                    </template>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-gray-700 uppercase">Payload Data (JSON)</span>
                            <span class="text-[10px] text-gray-400 font-mono">Inbound Request Body</span>
                        </div>
                        <pre class="bg-gray-900 text-emerald-400 p-4 rounded-xl text-[11px] font-mono overflow-x-auto max-h-72 border border-gray-800 leading-relaxed" x-text="selectedWebhook ? JSON.stringify(selectedWebhook.payload, null, 2) : ''"></pre>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div>
                        <template x-if="selectedWebhook && (selectedWebhook.status === 'failed' || selectedWebhook.status === 'pending')">
                            <button type="button" @click="retryWebhookItem(selectedWebhook.id)" :disabled="isRetryingWebhook" class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow transition disabled:opacity-50">
                                <span x-text="isRetryingWebhook ? 'Retrying...' : 'Reprocess Webhook'"></span>
                            </button>
                        </template>
                    </div>
                    <button type="button" @click="webhookPayloadModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- STRIPE CREDENTIALS MODAL -->
    <div x-show="stripeModalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="stripeModalOpen" @click="stripeModalOpen = false" class="fixed inset-0 bg-navy/70 backdrop-blur-xs transition-opacity"></div>

            <div x-show="stripeModalOpen" class="relative inline-block w-full max-w-xl p-6 sm:p-7 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-gray-200">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#635BFF] text-white flex items-center justify-center font-black text-sm shadow-sm">
                            S
                        </div>
                        <div>
                            <h3 class="text-base font-black text-navy uppercase font-heading">Stripe API Credentials</h3>
                            <p class="text-xs text-gray-400">Configure Stripe secret keys, publishable keys &amp; webhook secret.</p>
                        </div>
                    </div>
                    <button type="button" @click="stripeModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold p-1">&times;</button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitStripeCredentials()" class="space-y-4.5 pt-5 text-xs">
                    <!-- Environment Mode -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Environment Mode</label>
                        <select x-model="stripeForm.mode" class="w-full text-xs font-bold border-gray-300 rounded-xl py-2.5 px-3 focus:border-cyan focus:ring-cyan bg-gray-50">
                            <option value="test">Test / Sandbox Mode (Safe simulation - no real charges)</option>
                            <option value="live">Live / Production Mode (REAL MONEY &amp; CUSTOMER CHARGES)</option>
                        </select>
                    </div>

                    <!-- Prominent Warning when Live Mode Selected -->
                    <div x-show="stripeForm.mode === 'live'" x-cloak class="p-4 bg-red-50 border-2 border-red-500/40 rounded-xl text-red-900 space-y-1">
                        <div class="flex items-center gap-2 font-black text-xs uppercase text-red-700 tracking-wide">
                            <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Live Production Warning</span>
                        </div>
                        <p class="text-[11px] text-red-700 leading-relaxed font-medium">
                            Switching Stripe to <strong>LIVE</strong> mode means actual credit cards will be debited. Ensure you enter your live keys (<code class="font-mono bg-red-100 px-1 py-0.5 rounded text-[10px]">pk_live_...</code> and <code class="font-mono bg-red-100 px-1 py-0.5 rounded text-[10px]">sk_live_...</code>).
                        </p>
                    </div>

                    <!-- Publishable Key -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Publishable Key
                            <span class="text-[10px] font-normal text-gray-400 lowercase">(pk_test_... or pk_live_...)</span>
                        </label>
                        <input type="text" x-model="stripeForm.publishable_key" placeholder="pk_test_51..." class="w-full text-xs font-mono border-gray-300 rounded-xl py-2 px-3 focus:border-cyan focus:ring-cyan">
                        <div class="text-[11px] text-gray-400 mt-1 flex items-center justify-between">
                            <span>Client-side public key for Stripe Checkout and Elements.</span>
                        </div>
                    </div>

                    <!-- Secret Key -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Secret Key
                                <span class="text-[10px] font-normal text-gray-400 lowercase">(sk_test_... or sk_live_...)</span>
                            </label>
                            <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                AES-256 Encrypted
                            </span>
                        </div>
                        <div class="relative">
                            <input :type="stripeShowSecret ? 'text' : 'password'" x-model="stripeForm.secret_key" placeholder="•••••••••••••••••••••••• (Leave blank to keep current secret)" class="w-full text-xs font-mono border-gray-300 rounded-xl py-2 px-3 pr-10 focus:border-cyan focus:ring-cyan">
                            <button type="button" @click="stripeShowSecret = !stripeShowSecret" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-navy focus:outline-none" title="Toggle visibility">
                                <svg x-show="!stripeShowSecret" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="stripeShowSecret" x-cloak class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1 flex items-center justify-between">
                            <span>Current: <span class="font-mono font-bold text-navy" x-text="stripeData.masked_secret"></span></span>
                            <span class="text-[10px] text-gray-400">Never exposed in plain text</span>
                        </div>
                    </div>

                    <!-- Webhook Signing Secret -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Webhook Signing Secret
                                <span class="text-[10px] font-normal text-gray-400 lowercase">(whsec_...)</span>
                            </label>
                            <span class="text-[10px] text-gray-400 font-mono">Optional</span>
                        </div>
                        <div class="relative">
                            <input :type="stripeShowWebhook ? 'text' : 'password'" x-model="stripeForm.webhook_secret" placeholder="•••••••••••••••••••••••• (Leave blank to keep current secret)" class="w-full text-xs font-mono border-gray-300 rounded-xl py-2 px-3 pr-10 focus:border-cyan focus:ring-cyan">
                            <button type="button" @click="stripeShowWebhook = !stripeShowWebhook" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-navy focus:outline-none" title="Toggle visibility">
                                <svg x-show="!stripeShowWebhook" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="stripeShowWebhook" x-cloak class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1 flex items-center justify-between">
                            <span>Current: <span class="font-mono font-bold text-navy" x-text="stripeData.masked_webhook"></span></span>
                            <span class="text-[10px] text-gray-400">Endpoint: /webhook/stripe</span>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-[11px] text-gray-600 flex items-start gap-2">
                        <svg class="w-4 h-4 text-cyan flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span><strong>AES-256 Vault:</strong> Secret keys are securely encrypted at rest. Leaving secret inputs blank preserves existing configured keys.</span>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" @click="stripeModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isUpdatingStripe" class="px-5 py-2.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-lg shadow-sm transition flex items-center gap-2 disabled:opacity-50">
                            <svg x-show="isUpdatingStripe" class="w-3.5 h-3.5 animate-spin text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="isUpdatingStripe ? 'Saving & Encrypting...' : 'Save Stripe Credentials'">Save Stripe Credentials</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PAYPAL CREDENTIALS MODAL -->
    <div x-show="paypalModalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="paypalModalOpen" @click="paypalModalOpen = false" class="fixed inset-0 bg-navy/70 backdrop-blur-xs transition-opacity"></div>

            <div x-show="paypalModalOpen" class="relative inline-block w-full max-w-xl p-6 sm:p-7 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-gray-200">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#003087] text-white flex items-center justify-center font-black text-sm shadow-sm">
                            P
                        </div>
                        <div>
                            <h3 class="text-base font-black text-navy uppercase font-heading">PayPal API Credentials</h3>
                            <p class="text-xs text-gray-400">Configure Client ID, Client Secret &amp; Webhook ID for PayPal.</p>
                        </div>
                    </div>
                    <button type="button" @click="paypalModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold p-1">&times;</button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitPayPalCredentials()" class="space-y-4.5 pt-5 text-xs">
                    <!-- Environment Mode -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Environment Mode</label>
                        <select x-model="paypalForm.mode" class="w-full text-xs font-bold border-gray-300 rounded-xl py-2.5 px-3 focus:border-cyan focus:ring-cyan bg-gray-50">
                            <option value="sandbox">Sandbox (Testing / Mock Simulation)</option>
                            <option value="live">Live / Production (REAL PAYPAL ACCOUNTS &amp; CHARGES)</option>
                        </select>
                    </div>

                    <!-- Prominent Warning when Live Mode Selected -->
                    <div x-show="paypalForm.mode === 'live'" x-cloak class="p-4 bg-red-50 border-2 border-red-500/40 rounded-xl text-red-900 space-y-1">
                        <div class="flex items-center gap-2 font-black text-xs uppercase text-red-700 tracking-wide">
                            <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Live Production Warning</span>
                        </div>
                        <p class="text-[11px] text-red-700 leading-relaxed font-medium">
                            Switching PayPal to <strong>LIVE</strong> mode will process live buyer payments. Ensure you enter your PayPal Live REST API app credentials.
                        </p>
                    </div>

                    <!-- Client ID -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">PayPal Client ID</label>
                        <input type="text" x-model="paypalForm.client_id" placeholder="AYSq3RDG..." class="w-full text-xs font-mono border-gray-300 rounded-xl py-2 px-3 focus:border-cyan focus:ring-cyan">
                        <div class="text-[11px] text-gray-400 mt-1">REST API Client ID from PayPal Developer Portal.</div>
                    </div>

                    <!-- Client Secret -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">PayPal Client Secret</label>
                            <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                AES-256 Encrypted
                            </span>
                        </div>
                        <div class="relative">
                            <input :type="paypalShowSecret ? 'text' : 'password'" x-model="paypalForm.client_secret" placeholder="•••••••••••••••••••••••• (Leave blank to keep current secret)" class="w-full text-xs font-mono border-gray-300 rounded-xl py-2 px-3 pr-10 focus:border-cyan focus:ring-cyan">
                            <button type="button" @click="paypalShowSecret = !paypalShowSecret" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-navy focus:outline-none" title="Toggle visibility">
                                <svg x-show="!paypalShowSecret" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="paypalShowSecret" x-cloak class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1 flex items-center justify-between">
                            <span>Current: <span class="font-mono font-bold text-navy" x-text="paypalData.masked_secret"></span></span>
                            <span class="text-[10px] text-gray-400">Never exposed in plain text</span>
                        </div>
                    </div>

                    <!-- Webhook ID -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            PayPal Webhook ID
                            <span class="text-[10px] font-normal text-gray-400 lowercase">(Optional)</span>
                        </label>
                        <input type="text" x-model="paypalForm.webhook_id" placeholder="8JH49102..." class="w-full text-xs font-mono border-gray-300 rounded-xl py-2 px-3 focus:border-cyan focus:ring-cyan">
                        <div class="text-[11px] text-gray-400 mt-1">Webhook ID configured in PayPal developer dashboard.</div>
                    </div>

                    <!-- Security Notice -->
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-[11px] text-gray-600 flex items-start gap-2">
                        <svg class="w-4 h-4 text-cyan flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span><strong>AES-256 Vault:</strong> Secret keys are securely encrypted at rest. Leaving secret inputs blank preserves existing configured keys.</span>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" @click="paypalModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isUpdatingPayPal" class="px-5 py-2.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-lg shadow-sm transition flex items-center gap-2 disabled:opacity-50">
                            <svg x-show="isUpdatingPayPal" class="w-3.5 h-3.5 animate-spin text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="isUpdatingPayPal ? 'Saving & Encrypting...' : 'Save PayPal Credentials'">Save PayPal Credentials</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FLOATING TOAST NOTIFICATION -->
    <div x-show="toastVisible" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border text-xs font-bold"
         :class="toastType === 'success' ? 'bg-navy text-white border-cyan/40 shadow-cyan/10' : 'bg-red-950 text-white border-red-500/50 shadow-red-900/20'">
        <span class="w-2.5 h-2.5 rounded-full" :class="toastType === 'success' ? 'bg-cyan animate-pulse' : 'bg-red-400'"></span>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection
