@extends('layouts.admin')

@section('title', 'Manage Exams - ' . $site->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-navy">Manage Exams: {{ $site->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan/10 text-cyan-800 border border-cyan/30">
                    {{ $assignedOverlays->total() }} Assigned
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                Control which global certification exams appear on <strong class="text-navy">{{ $site->host }}</strong> without duplicating question banks.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-3">
            <a href="{{ route('admin.sites.edit', ['site' => $site->id, 'tab' => 'general']) }}" class="px-4 py-2 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">
                Website Settings
            </a>
            <a href="{{ route('admin.sites.index') }}" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                &larr; Back to Websites
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Assign New Exam Quick Form -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Assign Global Exam to this Website</h3>
        <form action="{{ route('admin.sites.exams.attach', $site->id) }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <select name="exam_id" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                    <option value="">-- Select from available global exams --</option>
                    @foreach($allExams as $ex)
                        <option value="{{ $ex->id }}">{{ $ex->exam_code }} — {{ $ex->exam_name }} ({{ $ex->vendor->name ?? 'Vendor' }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-cyan hover:bg-cyan/90 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow-sm">
                + Assign Exam
            </button>
        </form>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
        <form method="GET" action="{{ route('admin.sites.exams', $site->id) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="sm:col-span-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search exam code or name..." class="w-full rounded-xl border-gray-300 text-sm focus:border-cyan focus:ring-cyan">
            </div>
            <div>
                <select name="vendor_id" class="w-full rounded-xl border-gray-300 text-sm focus:border-cyan focus:ring-cyan">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ $vendorFilter == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-navy text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">Filter</button>
                @if(!empty($search) || !empty($vendorFilter))
                    <a href="{{ route('admin.sites.exams', $site->id) }}" class="px-3 py-2 border rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 flex items-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Assigned Exams Table with Bulk Actions -->
    <form action="{{ route('admin.sites.exams.bulk', $site->id) }}" method="POST" id="bulkForm" class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        @csrf
        <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500 uppercase">Bulk Actions:</span>
                <select name="action" class="rounded-xl border-gray-300 text-xs focus:border-cyan focus:ring-cyan">
                    <option value="">Select action...</option>
                    <option value="activate">Set Status: Visible</option>
                    <option value="deactivate">Set Status: Hidden</option>
                    <option value="remove">Remove from this Website</option>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold hover:bg-slate-700">Apply</button>
            </div>
            <div class="text-xs text-gray-400 font-medium">
                Showing {{ $assignedOverlays->firstItem() ?? 0 }} to {{ $assignedOverlays->lastItem() ?? 0 }} of {{ $assignedOverlays->total() }} assigned exams
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left w-10">
                        <input type="checkbox" onclick="document.querySelectorAll('.exam-checkbox').forEach(c => c.checked = this.checked)" class="rounded text-cyan">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Exam Code & Title</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">SEO Title Override</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Article Content</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($assignedOverlays as $overlay)
                @php $exam = $overlay->exam; @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-4">
                        <input type="checkbox" name="exam_ids[]" value="{{ $exam->id }}" class="exam-checkbox rounded text-cyan">
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-navy text-base leading-tight">{{ $exam->exam_code }}</div>
                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $exam->exam_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-700">
                            {{ $exam->vendor->name ?? 'Vendor' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-600">
                        @if(!empty($overlay->meta_title))
                            <span class="font-bold text-navy">{{ $overlay->meta_title }}</span>
                        @else
                            <span class="text-gray-400 italic">Default resolved title</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                        @if(!empty($overlay->custom_article_content))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Custom Content</span>
                        @else
                            <span class="text-gray-400">Master Catalog</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($overlay->is_active)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Visible</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">Hidden</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs space-x-2">
                        <a href="{{ route('admin.sites.edit', ['site' => $site->id, 'tab' => 'exams']) }}" class="text-cyan font-bold hover:underline">Edit Overlay</a>
                        <form action="{{ route('admin.sites.exams.detach', ['site' => $site->id, 'exam' => $exam->id]) }}" method="POST" class="inline" onsubmit="return confirm('Remove {{ $exam->exam_code }} from this website?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 font-bold hover:underline">Remove</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">
                        No exams assigned to this website matching your filter. Use the selector above to assign exams.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-gray-200">
            {{ $assignedOverlays->links() }}
        </div>
    </form>
</div>
@endsection
