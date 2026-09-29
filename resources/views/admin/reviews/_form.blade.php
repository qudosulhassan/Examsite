<div class="p-6 sm:p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="exam_id" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Exam</label>
            <select name="exam_id" id="exam_id" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-cyan focus:border-cyan">
                <option value="">Select Exam</option>
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ (string) old('exam_id', $review->exam_id ?? '') === (string) $exam->id ? 'selected' : '' }}>
                        {{ $exam->exam_code }} &mdash; {{ $exam->exam_name }} ({{ $exam->vendor->name ?? 'Unknown' }})
                    </option>
                @endforeach
            </select>
            @error('exam_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="user_id" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Verified Customer Account</label>
            <select name="user_id" id="user_id" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-cyan focus:border-cyan">
                <option value="">Select Registered User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (string) old('user_id', $review->user_id ?? '') === (string) $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            <p class="text-[11px] text-gray-400 mt-1">Ties this review to a real account so it counts as a "verified customer".</p>
            @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="reviewer_name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Display Name Override (Optional)</label>
            <input type="text" name="reviewer_name" id="reviewer_name" value="{{ old('reviewer_name', $review->reviewer_name ?? '') }}" placeholder="e.g. Sarah M." class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-cyan focus:border-cyan">
            <p class="text-[11px] text-gray-400 mt-1">Leave blank to show the account's full name.</p>
            @error('reviewer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="rating" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Rating</label>
            <select name="rating" id="rating" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-cyan focus:border-cyan">
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ (string) old('rating', $review->rating ?? 5) === (string) $i ? 'selected' : '' }}>
                        {{ $i }} Star{{ $i > 1 ? 's' : '' }} {{ str_repeat('★', $i) }}
                    </option>
                @endfor
            </select>
            @error('rating') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label for="review_text" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Review Text</label>
        <textarea name="review_text" id="review_text" rows="5" required maxlength="2000" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-cyan focus:border-cyan">{{ old('review_text', $review->review_text ?? '') }}</textarea>
        @error('review_text') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div class="flex items-center bg-gray-50 border border-gray-100 rounded-lg p-4">
        <input type="checkbox" name="is_approved" id="is_approved" value="1" {{ old('is_approved', $review->is_approved ?? true) ? 'checked' : '' }} class="h-4 w-4 text-cyan focus:ring-cyan border-gray-300 rounded">
        <label for="is_approved" class="ml-2.5 block text-sm text-gray-700 font-semibold">Approved (publish immediately on the exam page)</label>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.TomSelect) {
        new TomSelect('#exam_id', { placeholder: 'Search exams by code or name…', maxOptions: null, sortField: [] });
        new TomSelect('#user_id', { placeholder: 'Search users by name or email…', maxOptions: null, sortField: [] });
    }
});
</script>
