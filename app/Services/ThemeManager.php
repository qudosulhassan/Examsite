<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;

class ThemeManager
{
    protected ?string $activeTheme = 'default';

    public function setTheme(string $theme): void
    {
        $this->activeTheme = $theme;
    }

    public function getActiveTheme(): string
    {
        return $this->activeTheme ?: 'default';
    }

    public function registerThemePaths(): void
    {
        // No-op to avoid mutating static ViewFinder locations across HTTP requests
    }

    public function view(string $viewName, array $data = [], array $mergeData = [])
    {
        // 1. Try theme-specific view (e.g. pages.exams.show or themes.activeTheme.pages.exams.show)
        $candidates = [
            'themes.' . $this->activeTheme . '.' . $viewName,
            'themes.default.' . $viewName,
            $viewName,
            'pages.' . $viewName,
        ];

        foreach ($candidates as $candidate) {
            if (View::exists($candidate)) {
                return view($candidate, $data, $mergeData);
            }
        }

        return view($viewName, $data, $mergeData);
    }
}
