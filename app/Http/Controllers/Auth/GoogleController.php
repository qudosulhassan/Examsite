<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Exception;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user with this google_id already exists
            $user = User::where('google_id', $googleUser->id)->first();

            if (!$user) {
                // Check if user with this email already exists (we link the account)
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    // Update user's google_id and avatar
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $user->avatar ?? $googleUser->avatar,
                    ]);
                } else {
                    // Create a new user
                    $user = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'role' => 'student',
                        'password' => Hash::make(Str::random(24)),
                        'email_verified_at' => now(), // Google emails are already verified
                    ]);

                    // Assign default student role
                    $studentRole = Role::firstOrCreate(['name' => 'student']);
                    $user->assignRole($studentRole);

                    try {
                        \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeMail($user));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Google signup welcome email failed: ' . $e->getMessage());
                    }
                }
            }

            Auth::login($user);

            // Check if user is admin or student and redirect accordingly
            if ($user->isAdmin()) {
                return redirect()->intended('/admin');
            }

            return redirect()->intended('/dashboard');

        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Google authentication failed. Please try again.');
        }
    }
}
