{{--
    Practice-test engine page (PL-900 style), shared by the dashboard and the free demo.
    Expects: $exam, $config (see App\Services\TestEngine\EngineSession::config), $backUrl, $backLabel
--}}
@php
    $isExam = $config['mode'] === 'exam';
    $cssVer = @filemtime(public_path('test-engine/engine.css')) ?: 1;
    $jsVer = @filemtime(public_path('test-engine/engine.js')) ?: 1;
    $siteName = app(\App\Services\SiteContext::class)->branding()['name'] ?? config('app.name');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $exam->exam_code }} {{ $isExam ? 'Exam Simulation' : 'Practice Test' }} — {{ $siteName }}</title>
<link rel="icon" href="{{ asset('favicon-32x32.png') }}">
<script>try{var t=localStorage.getItem("etb-engine-theme");if(t)document.documentElement.dataset.theme=t;}catch(e){}</script>
<link rel="stylesheet" href="{{ asset('test-engine/engine.css') }}?v={{ $cssVer }}">
</head>
<body>
<header class="header">
  <div class="header-row">
    <button class="icon-btn menu-btn" id="menuBtn" aria-label="Open question navigator">☰</button>
    <div class="brand">
      <div class="logo">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(preg_replace('/[^A-Za-z]/', '', $exam->exam_code), 0, 2)) ?: 'Q' }}</div>
      <div>{{ $exam->exam_code }} {{ $isExam ? 'Exam' : 'Practice' }}<small>{{ \Illuminate\Support\Str::limit($exam->exam_name, 48) }}</small></div>
    </div>
    <div class="progress-wrap">
      <div class="progress-meta"><span id="progText">Question 1 of 0</span><span id="progPct">0% complete</span></div>
      <div class="progress-bar"><div id="progBar"></div></div>
    </div>
    <div class="stats">
      @if($isExam)
        <span class="pill info" id="timer">⏱ 00:00</span>
        <span class="pill ok" id="stOk">✓ 0 saved</span>
      @else
        <span class="pill ok" id="stOk">✓ 0</span>
        <span class="pill bad" id="stBad">✗ 0</span>
        <span class="pill warn hide-xs" id="stRev">👁 0</span>
      @endif
    </div>
    <button class="icon-btn" id="themeBtn" aria-label="Toggle theme">◐</button>
    <a class="back" href="{{ $backUrl }}">✕ {{ $backLabel }}</a>
  </div>
</header>

<div class="backdrop" id="backdrop"></div>
<div class="layout">
  <aside class="sidebar" id="sidebar" aria-label="Question navigator">
    <div class="side-head">
      <h2>Question Navigator</h2>
      <input class="search" id="search" type="search" placeholder="{{ $isExam ? 'Search questions…' : 'Search unlocked questions…' }}" autocomplete="off">
      <div class="filter-row" id="filters">
        <button class="chip active" data-f="all">All</button>
        @if($isExam)
          <button class="chip" data-f="answered">Answered</button>
          <button class="chip" data-f="unanswered">Unanswered</button>
        @else
          <button class="chip" data-f="correct">Correct</button>
          <button class="chip" data-f="incorrect">Incorrect</button>
          <button class="chip" data-f="revealed">Revealed</button>
        @endif
        <button class="chip" data-f="flagged">Flagged</button>
      </div>
    </div>
    <div class="nav-grid" id="navGrid"></div>
    <div class="legend">
      @if($isExam)
        <span style="--c:var(--info)">Answered</span><span style="--c:var(--warn)">Flagged</span>
      @else
        <span style="--c:var(--ok)">Correct</span><span style="--c:var(--bad)">Incorrect</span><span style="--c:var(--warn)">Revealed</span><span style="--c:var(--border)">Locked</span>
      @endif
    </div>
  </aside>

  <main id="main">
    <section class="card" id="qCard" aria-live="polite"></section>
    <section class="card complete hidden" id="doneCard"></section>
  </main>
</div>

<script>window.ETB_ENGINE = @json($config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);</script>
<script src="{{ asset('test-engine/engine.js') }}?v={{ $jsVer }}"></script>
</body>
</html>
