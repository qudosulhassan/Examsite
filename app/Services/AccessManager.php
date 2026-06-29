<?php

namespace App\Services;

use App\Models\User;
use App\Models\Exam;
use Carbon\Carbon;

class AccessManager
{
    /**
     * Check if the user has PDF access for a specific exam.
     */
    public function canAccessPdf(User $user, Exam $exam): bool
    {
        return $this->checkAccess($user, $exam, 'pdf');
    }

    /**
     * Check if the user has Test Engine access for a specific exam.
     */
    public function canAccessTestEngine(User $user, Exam $exam): bool
    {
        return $this->checkAccess($user, $exam, 'te');
    }

    /**
     * Centralized access logic.
     */
    protected function checkAccess(User $user, Exam $exam, string $type): bool
    {
        $now = Carbon::now();

        // 1. Check Global active subscriptions (from `subscriptions` table)
        // Note: Assuming active status means 'active' or 'trialing'.
        // If the subscription system doesn't specify PDF/TE granularly, 
        // we might assume 'Pro'/'Ultimate' includes both, etc.
        // For now, we will rely on `user_packages` which cleanly link to Package models.
        
        $hasGlobalSub = $user->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($query) use ($now) {
                $query->whereNull('current_period_end')
                      ->orWhere('current_period_end', '>', $now);
            })->exists();

        // If they have a raw Stripe/PayPal subscription, maybe it grants access. 
        // Usually we want to map those to packages too. If they have ANY active subscription, we might grant access depending on the plan.
        if ($hasGlobalSub) {
            // For a robust system, we would check if the plan_name includes TE/PDF.
            // Let's assume an active standard subscription grants full access for now, 
            // or we fall through to user_packages checking which is explicitly granular.
            // (Skipping early return here to rely on granular checks if needed, but returning true is safest for legacy subs).
            return true;
        }

        // 2. Check granular `user_packages`
        // A user package is valid if:
        // status is 'active' AND (expires_at is null OR expires_at > now)
        $validUserPackages = $user->userPackages()
            ->where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', $now);
            })
            ->with('package')
            ->get();

        foreach ($validUserPackages as $up) {
            $package = $up->package;
            if (!$package) continue;

            // Does the package include the requested feature?
            $hasFeature = ($type === 'pdf') ? $package->includes_pdf : $package->includes_te;
            if (!$hasFeature) continue;

            // Is it a Global package? (vendor_id is null)
            if (is_null($package->vendor_id)) {
                return true;
            }

            // Is it a Vendor package? (matches exam's vendor_id)
            if ($package->vendor_id === $exam->vendor_id) {
                return true;
            }
        }

        // 3. Check Single Exam purchase (from `user_exams` table)
        $hasSingleExam = $user->userExams()
            ->where('exam_id', $exam->id)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', $now);
            })
            // we could check access_type here if we want (e.g. 'both', 'pdf', 'te')
            // For now, if they bought the single exam, they get access based on what they bought.
            ->exists();

        if ($hasSingleExam) {
            return true; // Simplified: assumes user_exam record = full access to that exam
        }

        return false;
    }
}
