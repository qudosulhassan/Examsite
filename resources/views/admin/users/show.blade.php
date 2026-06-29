@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">User Details & Access Controls</h1>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Users
        </a>
    </div>

    <!-- User Header Panel -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm flex items-center space-x-4">
        <img class="h-16 w-16 rounded-full border border-gray-200" src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=0A1628&background=00D4AA' }}" alt="">
        <div>
            <h2 class="text-xl font-bold text-navy">{{ $user->name }}</h2>
            <p class="text-sm text-gray-500 font-semibold">{{ $user->email }}</p>
            <div class="mt-2 flex space-x-2">
                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-cyan bg-opacity-15 text-navy border border-cyan border-opacity-30">
                    Role: {{ $user->role }}
                </span>
                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-gray-100 text-gray-600">
                    Joined: {{ $user->created_at->format('F d, Y') }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Granted Exams List (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-250 rounded-lg shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-sm font-bold text-navy">Granted Certification Access</h3>
                </div>
                <div class="divide-y divide-gray-150 text-xs">
                    @forelse($purchasedExams as $ue)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-start space-x-4">
                                <div class="h-10 w-10 rounded bg-navy text-white flex flex-col items-center justify-center font-bold">
                                    <span class="text-[9px] uppercase {{ $ue->access_type === 'pdf' ? 'text-orange' : 'text-cyan' }}">{{ $ue->access_type }}</span>
                                </div>
                                <div>
                                    <span class="font-bold text-navy text-sm block">
                                        {{ $ue->exam->exam_code }} - {{ $ue->exam->exam_name }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-semibold">
                                        @if($ue->access_type === 'pdf')
                                            Downloaded: {{ $ue->download_count }} / {{ $ue->max_downloads }} times
                                        @else
                                            Timed engine simulator access
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div>
                                <form action="{{ route('admin.users.revoke-access', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke access to this exam?')">
                                    @csrf
                                    <input type="hidden" name="user_exam_id" value="{{ $ue->id }}">
                                    <button type="submit" class="text-red-500 font-bold hover:underline">
                                        Revoke Access
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-400">
                            No manual or purchased certification access records found.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Grant Manual Access Form (1/3 width) -->
        <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm h-fit space-y-4">
            <h3 class="text-sm font-bold text-navy border-b border-gray-150 pb-3">Manually Grant Access</h3>
            
            <form action="{{ route('admin.users.grant-access', $user->id) }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Exam Select -->
                <div>
                    <label for="exam_id" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Select Exam</label>
                    <select name="exam_id" id="exam_id" required
                            class="w-full border-gray-300 rounded text-xs px-2.5 py-2 focus:border-cyan focus:ring-cyan">
                        <option value="">Choose Certification</option>
                        @foreach($exams as $ex)
                            <option value="{{ $ex->id }}">{{ $ex->exam_code }} ({{ $ex->exam_name }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Access Type Select -->
                <div>
                    <label for="access_type" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Access Type</label>
                    <select name="access_type" id="access_type" required
                            class="w-full border-gray-300 rounded text-xs px-2.5 py-2 focus:border-cyan focus:ring-cyan">
                        <option value="pdf">Printable PDF Guide Only</option>
                        <option value="engine">Web Test Engine Simulator</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-navy text-white text-xs font-bold py-2.5 rounded shadow hover:bg-opacity-95 transition">
                    Grant Access Privileges
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
