{{-- Links to the vendor's own pages plus a reminder that fees and rules come from the vendor. Needs $exam. --}}
@php $officialLinks = \App\Support\OfficialSources::for($exam); $vendorLabel = $exam->vendor->name ?? 'the vendor'; @endphp
<section id="official-sources" class="scroll-mt-32" aria-labelledby="official-sources-title" style="margin:2rem 0;padding:1.5rem;border:1px solid #e5e7eb;border-radius:1rem;background:#f9fafb;">
    <h2 id="official-sources-title" style="font-size:1.25rem;font-weight:800;color:#0a1628;margin:0 0 .5rem;">Confirm the official {{ $exam->exam_code }} requirements</h2>
    <p style="font-size:.95rem;line-height:1.6;color:#4b5563;margin:0;">
        Exam fees, duration, languages, delivery options and retirement dates are set by {{ $vendorLabel }} and can change, so check {{ $vendorLabel }}'s official pages before you book.
        {{ config('app.name') }} is independent study material and is not affiliated with {{ $vendorLabel }}.
    </p>
    @if(!empty($officialLinks))
        <ul style="margin:.75rem 0 0;padding-left:1.25rem;font-size:.95rem;line-height:1.8;">
            @foreach($officialLinks as $link)
                <li><a href="{{ $link['url'] }}" rel="noopener" target="_blank" style="text-decoration:underline;color:#0a1628;">{{ $link['label'] }}</a></li>
            @endforeach
        </ul>
    @endif
</section>
