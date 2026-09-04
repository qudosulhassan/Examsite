@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <a href="{{ route('admin.users.index') }}" class="hover:text-navy transition">Users</a>
                <span>/</span>
                <span class="text-navy font-bold">{{ $user->name }}</span>
            </div>
            <h1 class="text-2xl font-black text-navy tracking-tight">User Account Profile</h1>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center space-x-2 text-xs font-bold text-navy bg-white border border-gray-250 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit Account</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-gray-600 bg-white border border-gray-250 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Users</span>
            </a>
        </div>
    </div>

    <!-- User Header Profile Card -->
    <div class="bg-white border border-gray-250 rounded-xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center space-x-5">
                <div class="relative">
                    @if($user->avatar)
                        <img class="h-20 w-20 rounded-2xl object-cover border-2 border-white shadow-md ring-2 ring-gray-150"
                             src="{{ Str::startsWith($user->avatar, ['http', '/']) ? $user->avatar : asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                    @else
                        <div class="h-20 w-20 rounded-2xl bg-navy text-cyan font-black text-2xl flex items-center justify-center border-2 border-white shadow-md ring-2 ring-gray-150">
                            {{ $user->initials }}
                        </div>
                    @endif
                    <div class="absolute -bottom-1 -right-1">
                        @if($user->status === 'active')
                            <span class="w-4 h-4 bg-emerald-500 border-2 border-white rounded-full block" title="Active"></span>
                        @elseif($user->status === 'suspended')
                            <span class="w-4 h-4 bg-red-500 border-2 border-white rounded-full block" title="Suspended"></span>
                        @else
                            <span class="w-4 h-4 bg-amber-500 border-2 border-white rounded-full block" title="Pending"></span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="flex items-center space-x-3">
                        <h2 class="text-xl font-black text-navy">{{ $user->name }}</h2>
                        <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2.5 py-0.5 rounded-full">#{{ $user->id }}</span>
                    </div>
                    <p class="text-xs text-gray-500 font-semibold mt-0.5">{{ $user->email }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <!-- Role Badge -->
                        @if($user->role === 'Super Admin' || $user->role === 'super_admin')
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                                Super Admin
                            </span>
                        @elseif($user->role === 'Admin' || $user->role === 'admin')
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-navy text-white shadow-2xs">
                                Admin
                            </span>
                        @elseif($user->role === 'Staff')
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                Staff
                            </span>
                        @elseif($user->role === 'Moderator')
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-cyan bg-opacity-20 text-navy border border-cyan border-opacity-30">
                                Moderator
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-700 border border-gray-200">
                                {{ $user->role ?: 'Student' }}
                            </span>
                        @endif

                        <!-- Status Badge -->
                        @if($user->status === 'active')
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Active
                            </span>
                        @elseif($user->status === 'suspended')
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-700 border border-red-200">
                                Suspended
                            </span>
                        @elseif($user->status === 'pending')
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                                Pending
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600 border border-gray-200">
                                Deactivated
                            </span>
                        @endif

                        <!-- Email Verified Badge -->
                        @if($user->email_verified_at)
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold text-teal-700 bg-teal-50 border border-teal-200 flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Verified Email</span>
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200">
                                Unverified Email
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Metadata Metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 border-t md:border-t-0 md:border-l border-gray-200 pt-4 md:pt-0 md:pl-6">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-150">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Orders</span>
                    <span class="text-base font-black text-navy">{{ $recentOrders->count() }}</span>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-150">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Exams</span>
                    <span class="text-base font-black text-cyan">{{ $purchasedExams->count() }}</span>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-150">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Phone</span>
                    <span class="text-xs font-semibold text-gray-700 truncate block">{{ $user->phone ?: 'None' }}</span>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-150">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Last Active</span>
                    <span class="text-xs font-semibold text-gray-700 block">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Granted Exams & Recent Orders (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Granted Certification Access Card -->
            <div class="bg-white border border-gray-250 rounded-xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-cyan bg-opacity-15 border border-cyan border-opacity-30 flex items-center justify-center text-cyan">
                            <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-navy">Certification & Exam Access</h3>
                            <p class="text-[11px] text-gray-400">PDF question dumps and interactive engine simulator licenses</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-navy bg-gray-100 px-3 py-1 rounded-full">{{ $purchasedExams->count() }} active</span>
                </div>

                <div class="divide-y divide-gray-150">
                    @forelse($purchasedExams as $ue)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-start space-x-4">
                                <div class="h-10 w-10 rounded-lg bg-navy text-white flex flex-col items-center justify-center font-black">
                                    <span class="text-[9px] uppercase tracking-wider {{ $ue->access_type === 'pdf' ? 'text-orange' : 'text-cyan' }}">{{ $ue->access_type }}</span>
                                </div>
                                <div>
                                    <span class="font-black text-navy text-sm block">
                                        {{ $ue->exam->exam_code ?? 'N/A' }} - {{ $ue->exam->exam_name ?? 'Certification' }}
                                    </span>
                                    <div class="flex items-center space-x-3 text-[11px] text-gray-400 font-semibold mt-0.5">
                                        @if($ue->access_type === 'pdf')
                                            <span>Downloaded: <strong class="text-navy">{{ $ue->download_count ?? 0 }} / {{ $ue->max_downloads ?? 10 }}</strong> times</span>
                                        @else
                                            <span>Simulator engine enabled</span>
                                        @endif
                                        <span>•</span>
                                        <span>Granted on {{ $ue->created_at ? $ue->created_at->format('M d, Y') : 'Unknown' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <form action="{{ route('admin.users.revoke-access', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke access to this exam for this user?')">
                                    @csrf
                                    <input type="hidden" name="user_exam_id" value="{{ $ue->id }}">
                                    <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition">
                                        Revoke
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <p class="text-xs font-bold text-gray-500">No certification access privileges registered</p>
                            <p class="text-[11px] text-gray-400 mt-1">Use the panel on the right to manually grant an exam package.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Orders & Purchases Card -->
            <div class="bg-white border border-gray-250 rounded-xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-navy bg-opacity-10 border border-navy border-opacity-20 flex items-center justify-center text-navy">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-navy">Order History & Invoices</h3>
                            <p class="text-[11px] text-gray-400">Transactions processed by this account</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold uppercase text-[10px]">
                                <th class="py-3 px-6">Order #</th>
                                <th class="py-3 px-6">Total Amount</th>
                                <th class="py-3 px-6">Status</th>
                                <th class="py-3 px-6">Gateway</th>
                                <th class="py-3 px-6 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150">
                            @forelse($recentOrders as $order)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-6 font-bold text-navy">
                                        #{{ $order->order_number ?? $order->id }}
                                    </td>
                                    <td class="py-3 px-6 font-bold text-emerald-600">
                                        ${{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="py-3 px-6">
                                        @if(in_array(strtolower($order->status), ['completed', 'paid', 'success']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>
                                        @elseif(in_array(strtolower($order->status), ['pending', 'processing']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">{{ $order->status }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-6 font-medium text-gray-500 uppercase text-[10px]">
                                        {{ $order->payment_gateway ?? 'Stripe' }}
                                    </td>
                                    <td class="py-3 px-6 text-right text-gray-400 font-semibold">
                                        {{ $order->created_at ? $order->created_at->format('M d, Y H:i') : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        No purchase orders placed by this user yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- User Activity / Audit Trail -->
            <div class="bg-white border border-gray-250 rounded-xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-navy">
                            <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-navy">Security & Administrative Audit Trail</h3>
                            <p class="text-[11px] text-gray-400">Recorded administrator actions targeting this user</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-150 text-xs">
                    @forelse($auditLogs as $log)
                        <div class="px-6 py-3.5 flex items-start justify-between hover:bg-gray-50 transition">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-navy">{{ $log->admin ? $log->admin->name : 'System' }}</span>
                                    <span class="text-gray-400">•</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-600 font-mono">{{ $log->action }}</span>
                                </div>
                                <p class="text-gray-600 font-medium">{{ $log->description }}</p>
                                @if($log->ip_address)
                                    <span class="text-[10px] text-gray-400 font-mono">IP: {{ $log->ip_address }}</span>
                                @endif
                            </div>
                            <span class="text-[11px] text-gray-400 font-semibold whitespace-nowrap ml-4">
                                {{ $log->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-gray-400 text-xs">
                            No security audit records logged for this account yet.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Column: Grant Access Form & Account Info (1/3) -->
        <div class="space-y-6">
            
            <!-- Grant Access Card -->
            <div class="bg-white border border-gray-250 rounded-xl p-6 shadow-sm space-y-4">
                <div class="border-b border-gray-150 pb-3">
                    <h3 class="text-sm font-black text-navy">Manually Grant Access</h3>
                    <p class="text-[11px] text-gray-400">Authorize exam download without charging an invoice.</p>
                </div>
                
                <form action="{{ route('admin.users.grant-access', $user->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Exam Select -->
                    <div>
                        <label for="exam_id" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Select Certification <span class="text-red-500">*</span></label>
                        <select name="exam_id" id="exam_id" required
                                class="w-full border-gray-300 rounded-lg text-xs px-3 py-2.5 focus:border-cyan focus:ring-cyan bg-white">
                            <option value="">Choose Certification Exam...</option>
                            @foreach($exams as $ex)
                                <option value="{{ $ex->id }}">{{ $ex->exam_code }} — {{ Str::limit($ex->exam_name, 35) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Access Type Select -->
                    <div>
                        <label for="access_type" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Privilege Type <span class="text-red-500">*</span></label>
                        <select name="access_type" id="access_type" required
                                class="w-full border-gray-300 rounded-lg text-xs px-3 py-2.5 focus:border-cyan focus:ring-cyan bg-white">
                            <option value="pdf">Printable PDF Guide (Downloads)</option>
                            <option value="engine">Web Simulator Engine (Timed Practice)</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full inline-flex items-center justify-center space-x-2 bg-navy text-white text-xs font-bold py-2.5 rounded-lg shadow-sm hover:bg-opacity-95 transition">
                        <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Grant Exam Privileges</span>
                    </button>
                </form>
            </div>

            <!-- Account Details Card -->
            <div class="bg-white border border-gray-250 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-navy border-b border-gray-150 pb-3">Account Information</h3>
                
                <dl class="space-y-3 text-xs">
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">User ID</dt>
                        <dd class="font-bold text-navy mt-0.5">#{{ $user->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Registered On</dt>
                        <dd class="font-semibold text-gray-700 mt-0.5">{{ $user->created_at ? $user->created_at->format('F d, Y \a\t H:i:s') : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Last Profile Update</dt>
                        <dd class="font-semibold text-gray-700 mt-0.5">{{ $user->updated_at ? $user->updated_at->format('F d, Y \a\t H:i:s') : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Last Active Session</dt>
                        <dd class="font-semibold text-gray-700 mt-0.5">{{ $user->last_login_at ? $user->last_login_at->format('F d, Y \a\t H:i:s') : 'No recorded login' }}</dd>
                    </div>
                    @if($user->trashed())
                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <span class="text-xs font-bold text-red-700">Account is Soft Deleted</span>
                            <p class="text-[11px] text-red-600 mt-0.5">Deleted on {{ $user->deleted_at->format('M d, Y H:i') }}</p>
                        </div>
                    @endif
                </dl>
            </div>

        </div>

    </div>
</div>
@endsection
