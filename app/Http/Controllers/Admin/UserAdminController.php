<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Exam;
use App\Models\UserExam;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $usersQuery = User::query();

        if ($search) {
            $usersQuery->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $usersQuery->orderBy('id', 'desc')->paginate(10);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function show(int $id)
    {
        $user = User::findOrFail($id);
        $exams = Exam::orderBy('exam_code')->get();
        
        $purchasedExams = UserExam::where('user_id', $user->id)
            ->with('exam')
            ->get();

        return view('admin.users.show', compact('user', 'exams', 'purchasedExams'));
    }

    public function edit(int $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,student',
        ]);

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        // Sync Spatie role
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')->with('success', 'User role updated successfully.');
    }

    public function grantAccess(Request $request, int $userId)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'access_type' => 'required|in:pdf,engine',
        ]);

        // Check if access already exists
        $exists = UserExam::where('user_id', $userId)
            ->where('exam_id', $request->exam_id)
            ->where('access_type', $request->access_type)
            ->exists();

        if ($exists) {
            return back()->with('error', 'User already has this access level granted.');
        }

        UserExam::create([
            'user_id' => $userId,
            'exam_id' => $request->exam_id,
            'access_type' => $request->access_type,
            'purchased_at' => now(),
            'max_downloads' => ($request->access_type === 'pdf') ? 3 : 0,
        ]);

        return back()->with('success', 'Exam access manual grant succeeded.');
    }

    public function revokeAccess(Request $request, int $userId)
    {
        $request->validate([
            'user_exam_id' => 'required|exists:user_exams,id',
        ]);

        $userExam = UserExam::where('id', $request->user_exam_id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $userExam->delete();

        return back()->with('success', 'Exam access revoked successfully.');
    }
}
