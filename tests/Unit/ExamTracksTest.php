<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Support\ExamTracks;
use PHPUnit\Framework\TestCase;

class ExamTracksTest extends TestCase
{
    private function exam(int $id, string $code): Exam
    {
        $e = new Exam();
        $e->id = $id;
        $e->exam_code = $code;

        return $e;
    }

    public function test_groups_microsoft_exams_by_code_prefix_and_keeps_new_prefixes_visible(): void
    {
        $exams = collect([
            $this->exam(1, 'AZ-900'), $this->exam(2, 'AZ-104'), $this->exam(3, 'AZ-305'),
            $this->exam(4, 'SC-300'), $this->exam(5, 'PL-900'), $this->exam(6, 'MB-800'),
            $this->exam(7, 'MS-700'), $this->exam(8, 'DP-420'), $this->exam(9, 'XX-999'),
        ]);

        $tracks = collect(ExamTracks::group($exams, 'microsoft'))->keyBy('key');

        $this->assertSame(['AZ-900', 'AZ-104', 'AZ-305'], $tracks['azure']['exams']->pluck('exam_code')->all());
        $this->assertSame(['SC-300'], $tracks['security']['exams']->pluck('exam_code')->all());
        $this->assertSame(['MS-700'], $tracks['m365']['exams']->pluck('exam_code')->all());
        $this->assertSame(['DP-420'], $tracks['data-ai']['exams']->pluck('exam_code')->all());
        $this->assertSame(['XX-999'], $tracks['more']['exams']->pluck('exam_code')->all());
        $this->assertSame('azure', array_values(ExamTracks::group($exams, 'microsoft'))[0]['key'], 'biggest track first');
        $this->assertSame(9, collect($tracks)->sum(fn ($t) => $t['exams']->count()), 'every exam appears in exactly one track');
    }

    public function test_empty_tracks_are_not_returned_and_unknown_vendors_have_none(): void
    {
        $this->assertCount(1, ExamTracks::group(collect([$this->exam(1, 'AZ-900')]), 'microsoft'));
        $this->assertSame([], ExamTracks::definitions('cisco'));
        $this->assertSame('more', ExamTracks::group(collect([$this->exam(1, '200-301')]), 'cisco')[0]['key']);
    }
}
