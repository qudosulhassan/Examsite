<div class="space-y-6" x-data="{ redirectSubTab: 'redirects', resolveModalOpen: false, current404Id: null, current404Url: '' }">
    <!-- Header with Sub-tab Switcher -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                <h4 class="text-base font-extrabold text-navy">404 Errors &amp; 301 Redirect Manager</h4>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">Automated Loop Protection</span>
            </div>
            <p class="text-xs text-gray-500">
                Preserve link equity, fix broken inbound backlinks, and turn dead 404 URLs into permanent 301 redirects.
            </p>
        </div>

        <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200">
            <button type="button" @click="redirectSubTab = 'redirects'" :class="redirectSubTab === 'redirects' ? 'bg-white text-navy font-bold shadow-sm' : 'text-gray-600 hover:text-navy'" class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                <span>Active Redirects</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-gray-200 text-gray-700">{{ $redirects->total() }}</span>
            </button>
            <button type="button" @click="redirectSubTab = 'not_found'" :class="redirectSubTab === 'not_found' ? 'bg-white text-navy font-bold shadow-sm' : 'text-gray-600 hover:text-navy'" class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                <span>404 Error Log</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-100 text-rose-700 font-bold">{{ $notFoundLogs->count() }}</span>
            </button>
        </div>
    </div>

    <!-- SUB-TAB 1: REDIRECT RULES -->
    <div x-show="redirectSubTab === 'redirects'" class="space-y-6">
        <!-- Add New Redirect Rule Form -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider border-b border-gray-150 pb-3">+ Create New Redirect Rule</h5>

            <form action="{{ route('admin.seo.redirects.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                <div class="md:col-span-5">
                    <label for="old_url" class="block text-xs font-bold text-navy mb-1">Source URL (Path)</label>
                    <input type="text" name="old_url" id="old_url" placeholder="/old-exam-page" required class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                </div>

                <div class="md:col-span-4">
                    <label for="new_url" class="block text-xs font-bold text-navy mb-1">Destination URL</label>
                    <input type="text" name="new_url" id="new_url" placeholder="/exams/vendor/new-slug" required class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                </div>

                <div class="md:col-span-2">
                    <label for="status_code" class="block text-xs font-bold text-navy mb-1">Status Code</label>
                    <select name="status_code" id="status_code" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                        <option value="301">301 (Permanent)</option>
                        <option value="302">302 (Temporary)</option>
                        <option value="307">307 (Temporary)</option>
                        <option value="308">308 (Permanent)</option>
                    </select>
                </div>

                <div class="md:col-span-1">
                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm transition">
                        Add
                    </button>
                </div>
            </form>
        </div>

        <!-- Redirects List Table -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-150 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h5 class="text-xs font-bold text-navy uppercase tracking-wider">Configured Redirect Rules ({{ $redirects->total() }})</h5>

                <!-- Search form -->
                <form action="{{ route('admin.seo.index') }}" method="GET" class="flex items-center space-x-2">
                    <input type="hidden" name="tab" value="redirects">
                    <input type="text" name="redirect_search" value="{{ request('redirect_search') }}" placeholder="Search redirects..." class="text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-1 px-3">
                    <button type="submit" class="text-xs font-bold text-navy bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-lg transition">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Source (Old URL)</th>
                            <th class="px-6 py-3 text-left">Destination (New URL)</th>
                            <th class="px-4 py-3 text-center">Type</th>
                            <th class="px-4 py-3 text-center">Hits</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-mono">
                        @forelse($redirects as $r)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-navy font-semibold max-w-xs truncate" title="{{ $r->old_url }}">
                                    {{ $r->old_url }}
                                </td>
                                <td class="px-6 py-3 text-gray-700 max-w-xs truncate" title="{{ $r->new_url }}">
                                    <span class="text-gray-400 mr-1">&rarr;</span> {{ $r->new_url }}
                                </td>
                                <td class="px-4 py-3 text-center font-sans">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $r->status_code == 301 ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $r->status_code }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-navy font-sans">
                                    {{ number_format($r->hits_count ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center font-sans">
                                    <form action="{{ route('admin.seo.redirects.toggle', $r->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ ($r->is_active ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ ($r->is_active ?? true) ? 'Active' : 'Disabled' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-3 text-right space-x-2 font-sans">
                                    <form action="{{ route('admin.seo.redirects.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this redirect rule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400 font-sans">
                                    No redirects configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($redirects->hasPages())
                <div class="p-4 border-t border-gray-150">
                    {{ $redirects->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- SUB-TAB 2: 404 LOG TRACKER -->
    <div x-show="redirectSubTab === 'not_found'" class="space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
            <div>
                <h5 class="text-sm font-bold text-navy">Detected 404 Error Hits</h5>
                <p class="text-xs text-gray-500">URLs requested by visitors or crawlers that returned HTTP 404 Not Found.</p>
            </div>

            <form action="{{ route('admin.seo.not-found.clear') }}" method="POST" onsubmit="return confirm('Clear 404 logs?');">
                @csrf
                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition">
                    Clear 404 Logs
                </button>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Missing URL</th>
                            <th class="px-4 py-3 text-center">Hits</th>
                            <th class="px-4 py-3 text-center">Crawler?</th>
                            <th class="px-4 py-3 text-left">Referrer</th>
                            <th class="px-4 py-3 text-right">Last Seen</th>
                            <th class="px-6 py-3 text-right">Resolve</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-mono">
                        @forelse($notFoundLogs as $log)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 text-rose-700 font-bold max-w-xs truncate" title="{{ $log->url }}">
                                    {{ $log->url }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold font-sans text-navy">
                                    {{ $log->hits_count }}
                                </td>
                                <td class="px-4 py-3 text-center font-sans">
                                    @if($log->is_crawler)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Bot</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">User</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 font-sans max-w-xs truncate" title="{{ $log->referrer ?? 'Direct / None' }}">
                                    {{ $log->referrer ?? 'Direct' }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500 font-sans">
                                    {{ $log->last_seen_at ? $log->last_seen_at->diffForHumans() : 'Recently' }}
                                </td>
                                <td class="px-6 py-3 text-right font-sans">
                                    @if($log->is_resolved)
                                        <span class="text-xs text-emerald-600 font-bold">Resolved &check;</span>
                                    @else
                                        <button type="button" @click="current404Id = {{ $log->id }}; current404Url = '{{ $log->url }}'; resolveModalOpen = true" class="px-2.5 py-1 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg transition shadow-sm">
                                            + 301 Redirect
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400 font-sans">
                                    Clean! No 404 errors logged in the system.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Resolve 301 Modal -->
    <div x-show="resolveModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="resolveModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form :action="'{{ url('admin/settings/seo/not-found') }}/' + current404Id + '/resolve'" method="POST">
                    @csrf
                    <div class="bg-navy px-6 py-4 text-white">
                        <h4 class="text-sm font-bold uppercase tracking-wide">Resolve 404 with 301 Permanent Redirect</h4>
                        <p class="text-xs text-gray-300 mt-0.5">Redirect future visitors and crawlers to the correct live page.</p>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Source 404 URL</label>
                            <input type="text" :value="current404Url" readonly class="w-full text-xs font-mono bg-gray-100 border-gray-200 rounded-lg text-gray-600">
                        </div>

                        <div>
                            <label for="destination_url" class="block text-xs font-bold text-navy uppercase mb-1">Destination URL (e.g. /exams/vendor/slug)</label>
                            <input type="text" name="destination_url" id="destination_url" placeholder="/" required class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex items-center justify-end space-x-3 border-t border-gray-150">
                        <button type="button" @click="resolveModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-navy">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm">Save 301 Redirect</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
