<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TosExamPointsTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private Course $course;
    /** @var array<int, int> topic id by index */
    private array $topics = [];

    private const ROWS = [
        ['Remembering', 'Multiple Choice', 1, 30],
        ['Understanding', 'True/False', 1, 10],
        ['Applying', 'Enumeration', 2, 5],
        ['Analyzing', 'Essay', 10, 1],
    ]; // 30 + 10 + 10 + 10 = 60 points

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::create(['name' => 'T', 'email' => 't@example.test', 'password' => bcrypt('x'), 'role' => 'Instructor', 'status' => 'approved']);
        $this->course = Course::create(['accountId' => $this->account->accountId, 'courseName' => 'C', 'courseCode' => 'C-1']);

        foreach (['Alpha', 'Beta', 'Gamma'] as $i => $name) {
            $topicId = DB::table('topic')->insertGetId(['courseId' => $this->course->courseId, 'topicName' => $name], 'topicId');
            $this->topics[$i] = $topicId;
            foreach (self::ROWS as [$level, $type]) {
                for ($n = 0; $n < 12; $n++) {
                    Question::create(['topicId' => $topicId, 'bloomLevel' => $level, 'questionType' => $type, 'content' => "$name $level $n", 'correctAnswer' => 'a']);
                }
            }
        }
    }

    private function tosPayload(array $extra = []): array
    {
        $payload = [
            'title' => 'Test ToS', 'examType' => 'Midterm', 'totalScore' => 60,
            'topicId' => $this->topics,
            'contactHours' => [$this->topics[0] => 3, $this->topics[1] => 2, $this->topics[2] => 1],
            'bloomKey' => [], 'itemCount' => [], 'pointsPerItem' => [],
        ];
        foreach (self::ROWS as [$level, $type, $points, $count]) {
            $key = "$level|$type";
            $payload['bloomKey'][] = $key;
            $payload['itemCount'][$key] = $count;
            $payload['pointsPerItem'][$key] = $points;
        }

        return $extra + $payload;
    }

    private function examTopicPoints(int $examId): array
    {
        $points = [];
        foreach (self::ROWS as [$level, $type, $pts]) {
            $points["$level|$type"] = $pts;
        }
        $out = [];
        foreach (DB::table('examquestion')->join('question', 'question.questionId', '=', 'examquestion.questionId')->where('examId', $examId)->get() as $q) {
            $out[$q->topicId] = ($out[$q->topicId] ?? 0) + $points["{$q->bloomLevel}|{$q->questionType}"];
        }
        ksort($out);

        return $out;
    }

    private function assignedPoints(int $tosId): array
    {
        return DB::table('tostopic')->where('tosId', $tosId)->orderBy('topicId')->pluck('assignedPoints', 'topicId')->map(fn ($v) => (float) $v)->all();
    }

    public function test_exam_topic_points_match_the_tos_page_values(): void
    {
        $this->actingAs($this->account);

        $this->post(route('courses.tos.store', $this->course), $this->tosPayload())->assertRedirect();
        $tosId = (int) DB::table('tos')->value('tosId');

        $assigned = $this->assignedPoints($tosId);
        $this->assertEquals(60, array_sum($assigned));
        $this->assertGreaterThan(0, DB::table('tostopicbloom')->where('tosId', $tosId)->count());

        $this->post(route('courses.tos.generate', [$this->course, $tosId]))->assertRedirect();
        $examId = (int) DB::table('exam')->value('examId');
        $this->assertEquals($assigned, $this->examTopicPoints($examId));

        // Replacing questions keeps every topic's points.
        foreach (DB::table('examquestion')->where('examId', $examId)->pluck('questionId') as $questionId) {
            $this->post(route('courses.exams.replace', [$this->course, $examId, $questionId]));
        }
        $this->assertEquals($assigned, $this->examTopicPoints($examId));

        // Pages render.
        $this->get(route('courses.tos.show', [$this->course, $tosId]))->assertOk();
        $this->get(route('courses.tos.edit', [$this->course, $tosId]))->assertOk()->assertSee('Fine-tune topic distribution');
        $this->get(route('courses.exams.show', [$this->course, $examId]))->assertOk()->assertSee('Points by topic');
    }

    public function test_older_tos_without_a_stored_distribution_still_matches(): void
    {
        $this->actingAs($this->account);
        $this->post(route('courses.tos.store', $this->course), $this->tosPayload())->assertRedirect();
        $tosId = (int) DB::table('tos')->value('tosId');

        DB::table('tostopicbloom')->where('tosId', $tosId)->delete();

        $this->post(route('courses.tos.generate', [$this->course, $tosId]))->assertRedirect();
        $examId = (int) DB::table('exam')->value('examId');

        $this->get(route('courses.tos.show', [$this->course, $tosId]))->assertOk();
        $this->assertEquals($this->assignedPoints($tosId), $this->examTopicPoints($examId));
    }

    public function test_custom_distribution_is_validated_and_used(): void
    {
        $this->actingAs($this->account);
        $this->post(route('courses.tos.store', $this->course), $this->tosPayload())->assertRedirect();
        $tosId = (int) DB::table('tos')->value('tosId');

        // Counts that do not add up to Table 2 are rejected.
        $bad = [];
        foreach ($this->topics as $t) {
            foreach (self::ROWS as [$level, $type]) {
                $bad[$t]["$level|$type"] = 1;
            }
        }
        $this->put(route('courses.tos.update', [$this->course, $tosId]), $this->tosPayload(['customMatrix' => 1, 'matrix' => $bad]))
            ->assertSessionHasErrors('matrix');

        // A valid one is stored and drives the exam.
        $good = [];
        foreach ($this->topics as $i => $t) {
            foreach (self::ROWS as [$level, $type, , $count]) {
                $good[$t]["$level|$type"] = $i === 0 ? $count : 0; // everything on the first topic
            }
        }
        $this->put(route('courses.tos.update', [$this->course, $tosId]), $this->tosPayload(['customMatrix' => 1, 'matrix' => $good]))
            ->assertSessionHasNoErrors();

        $this->assertEquals([$this->topics[0] => 60.0, $this->topics[1] => 0.0, $this->topics[2] => 0.0], $this->assignedPoints($tosId));
    }
}
