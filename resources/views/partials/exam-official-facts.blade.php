{{--
    Facts taken from the vendor's own study guide (Microsoft Learn), shown only for exams we have verified data for.
    Needs $exam. Inline styles are used on purpose: the compiled CSS only contains classes that were already in use.
--}}
@php
    $factGuide = (stripos((string) optional($exam->vendor)->name, 'microsoft') === 0)
        ? \App\Support\ExamGuideContent::load((string) $exam->slug)
        : null;
    $factDomains = $factGuide['domains'] ?? [];
    $factChecked = !empty($factGuide['fetchedAt']) ? \Carbon\Carbon::parse($factGuide['fetchedAt'])->format('F j, Y') : null;
@endphp
@if($factGuide && !empty($factDomains))
<section id="official-facts" class="scroll-mt-32" aria-labelledby="official-facts-title" style="margin:2rem 0;padding:1.5rem;border:1px solid #e5e7eb;border-radius:1rem;background:#fff;">
    <h2 id="official-facts-title" style="font-size:1.5rem;font-weight:800;color:#0a1628;margin:0 0 .75rem;">{{ $exam->exam_code }} exam facts from Microsoft Learn</h2>
    <p style="font-size:.95rem;line-height:1.6;color:#4b5563;margin:0 0 1rem;">
        Official exam name: <strong>{{ $factGuide['official'] ?? $exam->exam_name }}</strong>.
        Passing score: <strong>{{ \App\Support\ExamGuideContent::PASSING }}</strong> (a scaled score, not a percentage of questions).
        @if(!empty($factGuide['asOf']))Microsoft last updated this skills outline on {{ $factGuide['asOf'] }}.@endif
        @if($factChecked)We checked this page against it on {{ $factChecked }}.@endif
    </p>
    @if(!empty($factGuide['retire']) && ($factGuide['retire']['status'] ?? '') === 'retiring')
        <p style="font-size:.95rem;line-height:1.6;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:.75rem;padding:.75rem 1rem;margin:0 0 1rem;">
            Microsoft has announced that this exam retires on {{ $factGuide['retire']['date'] }}. You can still take it before that date, and a certification you already hold stays valid until it expires.
        </p>
    @endif
    <h3 style="font-size:1.05rem;font-weight:700;color:#0a1628;margin:0 0 .5rem;">Skills measured</h3>
    <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
        <thead>
            <tr>
                <th scope="col" style="text-align:left;padding:.5rem .75rem;border-bottom:2px solid #e5e7eb;color:#0a1628;">Skill area</th>
                <th scope="col" style="text-align:left;padding:.5rem .75rem;border-bottom:2px solid #e5e7eb;color:#0a1628;white-space:nowrap;">Share of the exam</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factDomains as $domain)
                <tr>
                    <td style="padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#374151;">{{ $domain['name'] }}</td>
                    <td style="padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#374151;white-space:nowrap;">{{ $domain['weight'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if(!empty($factGuide['url']))
        <p style="font-size:.85rem;color:#6b7280;margin:1rem 0 0;">Source: <a href="{{ $factGuide['url'] }}" rel="noopener" target="_blank" style="text-decoration:underline;color:#0a1628;">Microsoft Learn study guide for {{ $exam->exam_code }}</a>. Microsoft can change an exam, so confirm the current outline and price there before you book.</p>
    @endif
</section>
@endif
