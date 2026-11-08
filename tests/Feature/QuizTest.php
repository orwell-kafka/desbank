<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    private Account $teacher;
    private Course $course;
    private int $topicId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Account::create(['name' => 'T', 'email' => 'quiz@example.test', 'password' => bcrypt('x'), 'role' => 'Instructor', 'status' => 'approved']);
        $this->actingAs($this->teacher);
        $this->course = Course::create(['accountId' => $this->teacher->accountId, 'courseName' => 'Cognate Elective 1', 'courseCode' => 'CPEC 1', 'creditUnits' => 4]);
        $this->topicId = DB::table('topic')->insertGetId(['courseId' => $this->course->courseId, 'topicName' => 'Intro'], 'topicId');

        for ($i = 1; $i <= 6; $i++) {
            DB::table('question')->insert([
                'topicId' => $this->topicId, 'content' => "MC $i", 'questionType' => 'Multiple Choice',
                'bloomLevel' => $i % 2 ? 'Remembering' : 'Applying', 'choices' => json_encode(['a', 'b', 'c', 'd']), 'correctAnswer' => 'A',
            ]);
        }
    }

    private function payload(array $over = []): array
    {
        return $over + ['title' => 'Quiz 1: Intro', 'topicId' => [$this->topicId], 'itemCount' => ['Multiple Choice' => 4], 'pointsPerItem' => ['Multiple Choice' => 2]];
    }

    public function test_quiz_is_created_without_a_tos_and_is_usable_like_an_exam(): void
    {
        $url = route('courses.quizzes.store', $this->course);
        $this->post($url, $this->payload())->assertSessionHasNoErrors()->assertRedirect();

        $exam = DB::table('exam')->first();
        $this->assertNull($exam->tosId);
        $this->assertSame('Quiz 1: Intro', $exam->title);
        $this->assertEquals(4, $exam->totalItems);
        $this->assertEquals(8, $exam->totalScore);

        $this->get(route('courses.exams.index', $this->course))->assertOk()->assertSee('Quiz 1: Intro');
        $this->get(route('courses.exams.show', [$this->course, $exam->examId]))->assertOk()->assertSee('Quiz 1: Intro');
        $this->get(route('courses.exams.answerkey', [$this->course, $exam->examId]))->assertOk()->assertSee('Quiz 1: Intro');

        $questionId = DB::table('examquestion')->where('examId', $exam->examId)->value('questionId');
        $this->post(route('courses.exams.replace', [$this->course, $exam->examId, $questionId]))->assertSessionHasNoErrors();
        $this->assertSame(4, DB::table('examquestion')->where('examId', $exam->examId)->whereNotNull('points')->count());

        $this->delete(route('courses.exams.destroy', [$this->course, $exam->examId]))->assertRedirect();
        $this->assertSame(0, DB::table('exam')->count());
    }

    public function test_quiz_needs_a_title_items_and_enough_questions(): void
    {
        $url = route('courses.quizzes.store', $this->course);
        $this->post($url, $this->payload(['title' => '']))->assertSessionHasErrors('title');
        $this->post($url, $this->payload(['itemCount' => ['Multiple Choice' => 0]]))->assertSessionHasErrors('quiz');
        $this->post($url, $this->payload(['itemCount' => ['Multiple Choice' => 9]]))->assertSessionHasErrors('quiz');
        $this->assertSame(0, DB::table('exam')->count());
    }

    public function test_another_teacher_cannot_open_the_quiz(): void
    {
        $this->post(route('courses.quizzes.store', $this->course), $this->payload());
        $examId = DB::table('exam')->value('examId');

        $other = Account::create(['name' => 'O', 'email' => 'other@example.test', 'password' => bcrypt('x'), 'role' => 'Instructor', 'status' => 'approved']);
        $this->actingAs($other)->get(route('courses.exams.show', [$this->course, $examId]))->assertForbidden();
    }
}
