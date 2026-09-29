<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Exam;
use App\Models\User;
use App\Services\AuditLogService;

class ReviewAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['exam.vendor', 'user']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('review_text', 'like', "%{$search}%")
                  ->orWhere('reviewer_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('exam', function ($eq) use ($search) {
                      $eq->where('exam_code', 'like', "%{$search}%")
                         ->orWhere('exam_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->filled('status')) {
            $query->where('is_approved', $request->status === 'approved');
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        $reviews = $query->latest()->paginate(15)->appends($request->query());

        $totalReviews = Review::count();
        $approvedReviews = Review::where('is_approved', true)->count();
        $pendingReviews = $totalReviews - $approvedReviews;
        $averageRating = round((float) Review::where('is_approved', true)->avg('rating'), 1);

        return view('admin.reviews.index', compact(
            'reviews', 'totalReviews', 'approvedReviews', 'pendingReviews', 'averageRating'
        ));
    }

    public function create()
    {
        $exams = Exam::with('vendor')->orderBy('exam_code')->get();
        $users = User::orderBy('name')->get();
        return view('admin.reviews.create', compact('exams', 'users'));
    }

    public function store(Request $request)
    {
        $data = $this->validateReview($request);

        $review = Review::create($data);

        AuditLogService::log(
            'review_created',
            "Created review for exam #{$review->exam_id} ({$review->rating}\u{2605})",
            null,
            ['review_id' => $review->id, 'exam_id' => $review->exam_id]
        );

        return redirect()->route('admin.reviews.index')->with('success', 'Review created successfully.');
    }

    public function edit(Review $review)
    {
        $exams = Exam::with('vendor')->orderBy('exam_code')->get();
        $users = User::orderBy('name')->get();
        return view('admin.reviews.edit', compact('review', 'exams', 'users'));
    }

    public function update(Request $request, Review $review)
    {
        $data = $this->validateReview($request);

        $review->update($data);

        AuditLogService::log(
            'review_updated',
            "Updated review #{$review->id} for exam #{$review->exam_id}",
            null,
            ['review_id' => $review->id, 'exam_id' => $review->exam_id]
        );

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated successfully.');
    }

    public function destroy(Review $review)
    {
        $examId = $review->exam_id;
        $review->delete();

        AuditLogService::log(
            'review_deleted',
            "Deleted review for exam #{$examId}",
            null,
            ['exam_id' => $examId]
        );

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }

    protected function validateReview(Request $request): array
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'user_id' => 'required|exists:users,id',
            'reviewer_name' => 'nullable|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:2000',
            'is_approved' => 'nullable|boolean',
        ]);

        $validated['is_approved'] = $request->boolean('is_approved');

        return $validated;
    }
}
