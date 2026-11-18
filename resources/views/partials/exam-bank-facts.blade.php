{{--
    Facts about this exam's practice question bank, read from the questions themselves (see App\Support\ExamBankFacts).
    Needs $exam. Inline styles on purpose: the compiled CSS only has classes that were already in use.
--}}
@php $bank = \App\Support\ExamBankFacts::for($exam); @endphp
@if($bank)
<section id="question-bank" class="scroll-mt-32" aria-labelledby="question-bank-title" style="margin:2rem 0;padding:1.5rem;border:1px solid #e5e7eb;border-radius:1rem;background:#fff;">
    <h2 id="question-bank-title" style="font-size:1.5rem;font-weight:800;color:#0a1628;margin:0 0 .75rem;">{{ $exam->exam_code }} practice question bank at a glance</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
        <tbody>
            <tr>
                <th scope="row" style="text-align:left;padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#0a1628;width:38%;">Practice questions</th>
                <td style="padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#374151;">{{ number_format($bank['total']) }}</td>
            </tr>
            @if(!empty($bank['types']))
            <tr>
                <th scope="row" style="text-align:left;padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#0a1628;">Question formats</th>
                <td style="padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#374151;">
                    @foreach($bank['types'] as $label => $count){{ $label }} ({{ number_format($count) }})@if(!$loop->last), @endif @endforeach
                </td>
            </tr>
            @endif
            @if($bank['explained'] > 0)
            <tr>
                <th scope="row" style="text-align:left;padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#0a1628;">Questions with an explanation</th>
                <td style="padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#374151;">{{ number_format($bank['explained']) }} of {{ number_format($bank['total']) }}</td>
            </tr>
            @endif
            @if($bank['updated'])
            <tr>
                <th scope="row" style="text-align:left;padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#0a1628;">Page last updated</th>
                <td style="padding:.5rem .75rem;border-bottom:1px solid #f3f4f6;color:#374151;">{{ $bank['updated'] }}</td>
            </tr>
            @endif
            <tr>
                <th scope="row" style="text-align:left;padding:.5rem .75rem;color:#0a1628;">Free sample</th>
                <td style="padding:.5rem .75rem;color:#374151;">Request a <a href="{{ url('/free-demo') }}" style="text-decoration:underline;color:#0a1628;">free PDF sample</a> or try the <a href="{{ url('/test-engine') }}" style="text-decoration:underline;color:#0a1628;">timed test engine</a></td>
            </tr>
        </tbody>
    </table>
    @if(!empty($bank['topics']))
        <h3 style="font-size:1.05rem;font-weight:700;color:#0a1628;margin:1.25rem 0 .5rem;">Topics in the {{ $exam->exam_code }} questions</h3>
        <ul style="margin:0;padding-left:1.25rem;font-size:.9rem;line-height:1.7;color:#374151;">
            @foreach($bank['topics'] as $topic => $count)
                <li>{{ $topic }} ({{ number_format($count) }} {{ $count === 1 ? 'question' : 'questions' }})</li>
            @endforeach
        </ul>
    @endif
</section>
@endif
