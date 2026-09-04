@extends('layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    selectedUsers: [],
    selectAll: false,
    bulkActionModal: false,
    bulkActionType: '',
    deleteConfirmModal: false,
    userToDelete: null,
    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedUsers = Array.from(document.querySelectorAll('.user-checkbox')).map(el => el.value);
        } else {
            this.selectedUsers = [];
        }
    },
    confirmDelete(id, name, email) {
        this.userToDelete = { id, name, email };
        this.deleteConfirmModal = true;
    }
}">

    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-400 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
                <span>/</span>
                <span class="text-cyan font-bold">Users</span>
            </div>
            <h1 class="text-2xl font-extrabold text-navy tracking-tight">User Management</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manage user accounts, credentials, system roles, and access controls.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users.export', request()->query()) }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-5 py-2.5 bg-cyan hover:bg-opacity-90 text-navy text-xs font-black rounded-xl shadow-md shadow-cyan/20 transition-all transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add User
            </a>
        </div>
    </div>

    <!-- 1. Real Database-driven Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Total Users -->
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Users</span>
                <div class="w-8 h-8 rounded-xl bg-navy/5 text-navy flex items-center justify-center font-bold text-sm">
                    👥
                </div>
            </div>
            <div class="text-2xl font-black text-navy">{{ number_format($totalUsers) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Platform Accounts</div>
        </div>

        <!-- Active Users -->
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Active Users</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                    ✓
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($activeUsers) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">{{ $totalUsers > 0 ? round(($activeUsers/$totalUsers)*100, 1) : 0 }}% of total</div>
        </div>

        <!-- Students -->
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Students</span>
                <div class="w-8 h-8 rounded-xl bg-cyan/10 text-cyan flex items-center justify-center font-bold text-sm">
                    🎓
                </div>
            </div>
            <div class="text-2xl font-black text-navy">{{ number_format($studentUsers) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Standard Learners</div>
        </div>

        <!-- Administrators -->
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Admins</span>
                <div class="w-8 h-8 rounded-xl bg-orange/10 text-orange flex items-center justify-center font-bold text-sm">
                    🛡️
                </div>
            </div>
            <div class="text-2xl font-black text-orange">{{ number_format($adminUsers) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Super & Admins</div>
        </div>

        <!-- Staff / Moderators -->
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Staff / Mods</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                    ⚙️
                </div>
            </div>
            <div class="text-2xl font-black text-indigo-700">{{ number_format($staffUsers) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Operational Team</div>
        </div>

        <!-- Suspended Users -->
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Suspended</span>
                <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-sm">
                    ⛔
                </div>
            </div>
            <div class="text-2xl font-black text-red-600">{{ number_format($suspendedUsers) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Blocked Access</div>
        </div>
    </div>

    <!-- 2. Role Distribution Section -->
    <div class="bg-white border border-gray-150 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h3 class="text-sm font-black text-navy uppercase tracking-wider">Role Distribution</h3>
                <p class="text-xs text-gray-400">Proportional breakdown of active platform roles across the database.</p>
            </div>
            <span class="text-xs font-bold text-gray-500 mt-2 sm:mt-0">{{ count($rolesDistribution) }} Registered Roles</span>
        </div>

        <div class="space-y-3.5">
            @foreach($rolesDistribution as $roleItem)
                @php
                    $barColors = [
                        'Super Admin' => 'bg-orange',
                        'Admin'       => 'bg-red-500',
                        'Staff'       => 'bg-indigo-500',
                        'Moderator'   => 'bg-purple-500',
                        'Student'     => 'bg-cyan',
                    ];
                    $barColor = $barColors[$roleItem['name']] ?? 'bg-blue-500';
                @endphp
                <div>
                    <div class="flex justify-between items-center text-xs font-bold mb-1">
                        <span class="text-navy flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $barColor }}"></span>
                            {{ $roleItem['name'] }}
                        </span>
                        <span class="text-gray-500">
                            {{ $roleItem['count'] }} <span class="text-gray-400 font-normal">({{ $roleItem['percentage'] }}%)</span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-500" style="width: {{ max(1, $roleItem['percentage']) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. Search, Filter Toolbar & Bulk Actions Bar -->
    <!-- 3. Enterprise Search & Filter Bar (UI/UX Redesign) -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5">
        <form action="{{ route('admin.users.index') }}" method="GET" class="space-y-3.5">
            <!-- Main Search & Filter Row: Flexbox inline on desktop -->
            <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                <!-- Primary Search Input (Flex-grow) -->
                <div style="flex: 1 1 320px; min-width: 260px; position: relative;">
                    <div style="position: absolute; top: 0; bottom: 0; left: 14px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user name, email, phone, or ID..."
                           style="width: 100%; height: 42px; padding-left: 42px; padding-right: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13px; color: #0A1628; outline: none; transition: all 0.2s;"
                           onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#00D4AA'; this.style.boxShadow='0 0 0 3px rgba(0, 212, 170, 0.15)';"
                           onblur="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0'; this.style.boxShadow='none';">
                </div>

                <!-- Role Dropdown -->
                <div style="flex: 0 0 160px; min-width: 140px; position: relative;">
                    <select name="role"
                            style="width: 100%; height: 42px; padding-left: 14px; padding-right: 32px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13px; font-weight: 600; color: #0A1628; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; transition: all 0.2s;"
                            onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#00D4AA';"
                            onblur="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0';">
                        <option value="">All Roles</option>
                        @foreach($allRoles as $r)
                            <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                    <div style="position: absolute; top: 0; bottom: 0; right: 12px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Status Dropdown -->
                <div style="flex: 0 0 160px; min-width: 140px; position: relative;">
                    <select name="status"
                            style="width: 100%; height: 42px; padding-left: 14px; padding-right: 32px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13px; font-weight: 600; color: #0A1628; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; transition: all 0.2s;"
                            onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#00D4AA';"
                            onblur="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0';">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="deactivated" {{ request('status') === 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                    </select>
                    <div style="position: absolute; top: 0; bottom: 0; right: 12px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Joined Range Dropdown -->
                <div style="flex: 0 0 170px; min-width: 140px; position: relative;">
                    <select name="joined_range"
                            style="width: 100%; height: 42px; padding-left: 14px; padding-right: 32px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13px; font-weight: 600; color: #0A1628; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; transition: all 0.2s;"
                            onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#00D4AA';"
                            onblur="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0';">
                        <option value="">Joined: Anytime</option>
                        <option value="today" {{ request('joined_range') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="7days" {{ request('joined_range') === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ request('joined_range') === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_year" {{ request('joined_range') === 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                    <div style="position: absolute; top: 0; bottom: 0; right: 12px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Action Buttons: Filter & Reset -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="submit"
                            style="height: 42px; padding: 0 20px; background: #0A1628; color: #FFFFFF; font-size: 13px; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(10, 22, 40, 0.15); transition: opacity 0.2s;"
                            onmouseover="this.style.opacity='0.92';"
                            onmouseout="this.style.opacity='1';">
                        <svg width="15" height="15" fill="none" stroke="#00D4AA" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Search</span>
                    </button>

                    @if(request()->anyFilled(['search', 'role', 'status', 'joined_range', 'customer', 'email_verified']))
                        <a href="{{ route('admin.users.index') }}"
                           style="height: 42px; width: 42px; background: #F1F5F9; color: #64748B; border-radius: 12px; border: 1px solid #E2E8F0; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s;"
                           onmouseover="this.style.background='#FEE2E2'; this.style.color='#DC2626'; this.style.borderColor='#FECACA';"
                           onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B'; this.style.borderColor='#E2E8F0';"
                           title="Reset all filters">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Bottom Row: Quick Status Badges / Pills & Result Counter -->
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; padding-top: 12px; border-top: 1px solid #F1F5F9;">
                <!-- Quick Filter Pills -->
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 6px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94A3B8; margin-right: 4px;">Quick Status:</span>
                    
                    <a href="{{ route('admin.users.index', array_merge(request()->except(['status', 'page']), [])) }}"
                       style="padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ !request('status') ? 'background: #0A1628; color: #FFFFFF; border: 1px solid #0A1628;' : 'background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0;' }}">
                        All ({{ number_format($totalUsers) }})
                    </a>

                    <a href="{{ route('admin.users.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                       style="padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ request('status') === 'active' ? 'background: #059669; color: #FFFFFF; border: 1px solid #059669;' : 'background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;' }}">
                        Active ({{ number_format($activeUsers) }})
                    </a>

                    <a href="{{ route('admin.users.index', array_merge(request()->except(['status', 'page']), ['status' => 'suspended'])) }}"
                       style="padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ request('status') === 'suspended' ? 'background: #DC2626; color: #FFFFFF; border: 1px solid #DC2626;' : 'background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;' }}">
                        Suspended ({{ number_format($suspendedUsers) }})
                    </a>

                    <a href="{{ route('admin.users.index', array_merge(request()->except(['role', 'page']), ['role' => 'Student'])) }}"
                       style="padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ request('role') === 'Student' ? 'background: #00D4AA; color: #0A1628; font-weight: 700; border: 1px solid #00D4AA;' : 'background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0;' }}">
                        Students ({{ number_format($studentUsers) }})
                    </a>

                    <a href="{{ route('admin.users.index', array_merge(request()->except(['role', 'page']), ['role' => 'Admin'])) }}"
                       style="padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ in_array(request('role'), ['Admin', 'Super Admin']) ? 'background: #FF6B35; color: #FFFFFF; font-weight: 700; border: 1px solid #FF6B35;' : 'background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0;' }}">
                        Admins ({{ number_format($adminUsers) }})
                    </a>
                </div>

                <!-- Match Counter / Active Indicator -->
                <div style="font-size: 12px; color: #64748B;">
                    @if(request()->anyFilled(['search', 'role', 'status', 'joined_range', 'customer', 'email_verified']))
                        <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: #FEF3C7; border: 1px solid #FDE68A; color: #92400E; border-radius: 6px; font-weight: 700; font-size: 11px;">
                            <span>Matching:</span>
                            <span style="font-weight: 800;">{{ number_format($users->total()) }} users found</span>
                        </span>
                    @else
                        <span>Showing <strong style="color: #0A1628;">{{ number_format($users->total()) }}</strong> accounts</span>
                    @endif
                </div>
            </div>

            <!-- Preserved URL Query Params -->
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
            @if(request('direction')) <input type="hidden" name="direction" value="{{ request('direction') }}"> @endif
            @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
        </form>
    </div>

    <!-- Bulk Actions Bar (Visible when rows selected) -->
        <div x-show="selectedUsers.length > 0" x-cloak class="flex flex-wrap items-center justify-between p-3.5 bg-navy/5 border border-navy/10 rounded-xl transition-all">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-cyan animate-pulse"></span>
                <span class="text-xs font-extrabold text-navy" x-text="selectedUsers.length + ' users selected'"></span>
            </div>

            <div class="flex items-center space-x-2">
                <form action="{{ route('admin.users.bulk-action') }}" method="POST" id="bulkForm" class="flex items-center space-x-2">
                    @csrf
                    <!-- Hidden inputs populated dynamically -->
                    <template x-for="uid in selectedUsers" :key="uid">
                        <input type="hidden" name="user_ids[]" :value="uid">
                    </template>

                    <select name="action" x-model="bulkActionType" required class="text-xs py-1.5 px-3 border border-gray-300 rounded-lg bg-white text-navy focus:border-cyan focus:ring-cyan">
                        <option value="">Bulk Action...</option>
                        <option value="activate">Set Status: Active</option>
                        <option value="suspend">Set Status: Suspended</option>
                        <option value="deactivate">Set Status: Deactivated</option>
                        <option value="assign_role">Assign Role</option>
                        <option value="delete">Delete Users</option>
                    </select>

                    <div x-show="bulkActionType === 'assign_role'" style="display: none;">
                        <select name="assign_role" class="text-xs py-1.5 px-3 border border-gray-300 rounded-lg bg-white text-navy focus:border-cyan focus:ring-cyan">
                            @foreach($allRoles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" @click="
                        if (bulkActionType === 'delete') {
                            if (confirm('Are you sure you want to delete ' + selectedUsers.length + ' selected users?')) {
                                $el.closest('form').submit();
                            }
                        } else if (bulkActionType) {
                            $el.closest('form').submit();
                        }
                    " class="text-xs font-bold bg-navy text-white px-4 py-1.5 rounded-lg hover:bg-opacity-90 transition">
                        Apply
                    </button>
                </form>

                <button type="button" @click="selectedUsers = []; selectAll = false;" class="text-xs text-gray-500 hover:text-navy underline">
                    Clear Selection
                </button>
            </div>
        </div>
    </div>

    <!-- 4. Upgraded Users Table Card -->
    <div class="bg-white rounded-2xl border border-gray-150 shadow-sm overflow-hidden">
        
        <!-- Table Header & Pagination Size Info -->
        <div class="px-6 py-4 border-b border-gray-150 bg-gray-50/50 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div class="text-xs font-semibold text-gray-500">
                Showing <span class="font-bold text-navy">{{ $users->firstItem() ?? 0 }}</span> to <span class="font-bold text-navy">{{ $users->lastItem() ?? 0 }}</span> of <span class="font-bold text-navy">{{ number_format($users->total()) }}</span> users
            </div>

            <!-- Page Size & Quick Sort Links -->
            <div class="flex items-center space-x-3 text-xs">
                <span class="text-gray-400">Show:</span>
                @foreach([25, 50, 100] as $size)
                    <a href="{{ request()->fullUrlWithQuery(['per_page' => $size]) }}" class="px-2 py-0.5 rounded font-bold transition {{ (int)request('per_page', 25) === $size ? 'bg-navy text-white' : 'text-gray-500 hover:bg-gray-100' }}">
                        {{ $size }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-150">
                <thead class="bg-gray-50 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="rounded border-gray-300 text-cyan focus:ring-cyan">
                        </th>
                        <th class="px-5 py-3 text-left">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => ($sortField === 'name' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-navy">
                                User
                                @if($sortField === 'name')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 text-left">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => ($sortField === 'email' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-navy">
                                Email Address
                                @if($sortField === 'email')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 text-left">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'role', 'direction' => ($sortField === 'role' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-navy">
                                Role
                                @if($sortField === 'role')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 text-left">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => ($sortField === 'status' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-navy">
                                Status
                                @if($sortField === 'status')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 text-left">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sortField === 'created_at' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-navy">
                                Joined Date
                                @if($sortField === 'created_at')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 text-left">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'last_login_at', 'direction' => ($sortField === 'last_login_at' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-navy">
                                Last Login
                                @if($sortField === 'last_login_at')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'orders_count', 'direction' => ($sortField === 'orders_count' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center justify-center gap-1 hover:text-navy">
                                Purchases
                                @if($sortField === 'orders_count')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-150 text-xs">
                    @forelse($users as $usr)
                        <tr class="hover:bg-gray-50/70 transition">
                            <!-- Checkbox -->
                            <td class="px-5 py-4">
                                <input type="checkbox" :value="{{ $usr->id }}" x-model="selectedUsers" class="user-checkbox rounded border-gray-300 text-cyan focus:ring-cyan">
                            </td>

                            <!-- User (Name + Avatar + ID) -->
                            <td class="px-5 py-4">
                                <div class="flex items-center space-x-3">
                                    @if($usr->avatar)
                                        <img src="{{ $usr->avatar_url }}" alt="{{ $usr->name }}" class="h-9 w-9 rounded-xl object-cover border border-gray-200 shadow-sm shrink-0">
                                    @else
                                        <!-- Clean Initials Avatar (Never broken) -->
                                        <div class="h-9 w-9 rounded-xl bg-navy text-cyan border border-cyan/20 flex items-center justify-center font-black text-xs shadow-sm shrink-0">
                                            {{ $usr->initials }}
                                        </div>
                                    @endif

                                    <div>
                                        <a href="{{ route('admin.users.show', $usr->id) }}" class="font-bold text-navy hover:text-cyan transition block text-sm">
                                            {{ $usr->name }}
                                        </a>
                                        <span class="text-[10px] text-gray-400 font-medium">ID #{{ $usr->id }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Email & Verification Badge -->
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-700">{{ $usr->email }}</div>
                                @if($usr->email_verified_at)
                                    <span class="inline-flex items-center text-[10px] text-emerald-600 font-bold mt-0.5">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Verified
                                    </span>
                                @else
                                    <span class="text-[10px] text-amber-500 font-medium">Unverified</span>
                                @endif
                            </td>

                            <!-- Role Badge -->
                            <td class="px-5 py-4">
                                @php
                                    $roleBadgeStyles = [
                                        'Super Admin' => 'bg-orange/15 text-orange border-orange/30',
                                        'Admin'       => 'bg-red-50 text-red-600 border-red-200',
                                        'Staff'       => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'Moderator'   => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'Student'     => 'bg-cyan/15 text-navy border-cyan/30',
                                    ];
                                    $roleStyle = $roleBadgeStyles[$usr->role] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $roleStyle }}">
                                    {{ $usr->role }}
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-5 py-4">
                                @switch($usr->status)
                                    @case('active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Active
                                        </span>
                                        @break
                                    @case('suspended')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                            Suspended
                                        </span>
                                        @break
                                    @case('pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                            Pending
                                        </span>
                                        @break
                                    @case('deactivated')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                            Deactivated
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Active
                                        </span>
                                @endswitch
                            </td>

                            <!-- Joined Date -->
                            <td class="px-5 py-4 text-gray-500 font-semibold whitespace-nowrap">
                                <div>{{ $usr->created_at->format('M d, Y') }}</div>
                                <span class="text-[10px] text-gray-400 font-normal">{{ $usr->created_at->diffForHumans() }}</span>
                            </td>

                            <!-- Last Login -->
                            <td class="px-5 py-4 text-gray-500 font-medium whitespace-nowrap">
                                @if($usr->last_login_at)
                                    <div>{{ $usr->last_login_at->format('M d, Y') }}</div>
                                    <span class="text-[10px] text-gray-400">{{ $usr->last_login_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-gray-400 italic">Never</span>
                                @endif
                            </td>

                            <!-- Orders / Purchases -->
                            <td class="px-5 py-4 text-center">
                                @if($usr->orders_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-navy text-cyan border border-cyan/20">
                                        {{ $usr->orders_count }} Orders
                                    </span>
                                @else
                                    <span class="text-gray-400">0</span>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="px-5 py-4 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.users.show', $usr->id) }}" class="text-navy font-bold hover:text-cyan transition" title="View Details">
                                    View
                                </a>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('admin.users.edit', $usr->id) }}" class="text-cyan font-bold hover:underline" title="Edit User">
                                    Edit
                                </a>
                                <span class="text-gray-300">|</span>
                                <button type="button" @click="confirmDelete({{ $usr->id }}, '{{ addslashes($usr->name) }}', '{{ addslashes($usr->email) }}')" class="text-red-500 font-bold hover:underline" title="Delete User">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3 text-xl font-bold">
                                        🔍
                                    </div>
                                    <h4 class="text-base font-bold text-navy">No users found</h4>
                                    <p class="text-xs text-gray-400 mt-1">Try adjusting your search criteria or resetting filters.</p>
                                    <div class="mt-4 flex space-x-3">
                                        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-cyan hover:underline">Clear Filters</a>
                                        <span class="text-gray-300">|</span>
                                        <a href="{{ route('admin.users.create') }}" class="text-xs font-bold text-navy hover:underline">+ Add New User</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        @if($users->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- 5. Delete Confirmation Modal -->
    <div x-show="deleteConfirmModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="deleteConfirmModal = false"></div>
        <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 z-10">
            <div class="flex items-center space-x-3 text-red-600 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center font-black">
                    ⚠️
                </div>
                <h3 class="text-lg font-bold text-navy">Confirm User Deletion</h3>
            </div>

            <p class="text-xs text-gray-500 leading-relaxed">
                Are you sure you want to delete user <strong class="text-navy" x-text="userToDelete ? userToDelete.name : ''"></strong> (<span x-text="userToDelete ? userToDelete.email : ''"></span>)?
            </p>
            <p class="text-[11px] text-gray-400 mt-2">
                This will soft-delete the user account while preserving related transaction history and logs.
            </p>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" @click="deleteConfirmModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                    Cancel
                </button>
                <form :action="'{{ url('/admin/users') }}/' + (userToDelete ? userToDelete.id : '')" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                        Confirm Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

