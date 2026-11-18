<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Adds 732 real questions to the course "Programming Logic and Design" so that every combination of
 * Bloom level (Remembering ... Creating) and question type (Multiple Choice, True/False, Fill in the
 * Blanks, Enumeration, Matching Type, Essay) holds at least 20 questions. Each Bloom level has 122
 * questions and each question type has 122 questions, spread over six topics.
 * This is what the ToS needs: its Question Bank check counts questions per Bloom level and type.
 *
 * Data file: database/seeders/desbank_pld_questions.json (next to this file).
 *
 * Preview only (Windows cmd / cmder):
 *   set DESBANK_PLD_DRY=1
 *   php artisan db:seed --class=PldQuestionsSeeder
 * Run for real:
 *   set DESBANK_PLD_DRY=
 *   php artisan db:seed --class=PldQuestionsSeeder
 *
 * Optional:
 *   set DESBANK_PLD_COURSE=PLD-CODE   (course code, if the course name is not found or appears twice)
 *
 * A topic is matched by name inside the course (capitals ignored) and created when it is missing.
 * A question whose text already exists in the same topic is skipped. Nothing is updated or deleted.
 */
final class PldQuestionsSeeder extends Seeder
{
    private const COURSE_NAME = 'Programming Logic and Design';

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('This seeder is disabled in production.');
        }

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'desbank_pld_questions.json';
        if (! is_file($file)) {
            throw new RuntimeException('Missing desbank_pld_questions.json: copy it into database/seeders/ next to this file.');
        }
        $groups = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        foreach (['courses', 'topic', 'question'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Table '{$table}' is missing. Run php artisan migrate first. No data was changed.");
            }
        }

        $dry = (string) getenv('DESBANK_PLD_DRY') !== '';
        $code = (string) getenv('DESBANK_PLD_COURSE');

        $query = DB::table('courses');
        if ($code !== '') {
            $query->where('courseCode', $code);
        } else {
            $query->whereRaw('LOWER(TRIM(courseName)) = ?', [mb_strtolower(self::COURSE_NAME)]);
        }
        $courses = $query->get();

        if ($courses->isEmpty()) {
            throw new RuntimeException('Course not found (' . ($code !== '' ? "code {$code}" : self::COURSE_NAME) . '). Set DESBANK_PLD_COURSE to its course code. No data was changed.');
        }
        if ($courses->count() > 1) {
            throw new RuntimeException('More than one course matches: ' . $courses->pluck('courseCode')->implode(', ') . '. Set DESBANK_PLD_COURSE to the course code you want. No data was changed.');
        }
        $course = $courses->first();

        $hasCreated = Schema::hasColumn('question', 'created_at');
        $hasUpdated = Schema::hasColumn('question', 'updated_at');

        $added = 0;
        $skipped = 0;
        $newTopics = [];

        DB::transaction(function () use ($groups, $course, $dry, $hasCreated, $hasUpdated, &$added, &$skipped, &$newTopics) {
            foreach ($groups as $group) {
                $topic = DB::table('topic')
                    ->where('courseId', $course->courseId)
                    ->whereRaw('LOWER(TRIM(topicName)) = ?', [mb_strtolower($group['topicName'])])
                    ->first();

                if ($topic === null) {
                    $newTopics[] = $group['topicName'];
                    $topicId = $dry ? 0 : DB::table('topic')->insertGetId(['courseId' => $course->courseId, 'topicName' => $group['topicName']], 'topicId');
                } else {
                    $topicId = $topic->topicId;
                }

                $existing = $topicId ? array_flip(DB::table('question')->where('topicId', $topicId)->pluck('content')->all()) : [];
                $addedHere = 0;

                foreach ($group['questions'] as $q) {
                    if (isset($existing[$q['content']])) {
                        $skipped++;
                        continue;
                    }
                    if (! $dry) {
                        $row = [
                            'topicId'       => $topicId,
                            'bloomLevel'    => $q['bloomLevel'],
                            'questionType'  => $q['questionType'],
                            'content'       => $q['content'],
                            'choices'       => isset($q['choices']) ? implode(',', $q['choices']) : null,
                            'correctAnswer' => $q['correctAnswer'],
                        ];
                        if ($hasCreated) {
                            $row['created_at'] = now();
                        }
                        if ($hasUpdated) {
                            $row['updated_at'] = now();
                        }
                        DB::table('question')->insert($row);
                    }
                    $added++;
                    $addedHere++;
                }

                $this->command?->line(sprintf('%s: %d %s', $group['topicName'], $addedHere, $dry ? 'would be added' : 'added'));
            }
        });

        $this->command?->info(($dry ? 'DRY RUN, nothing written. ' : '') . 'Questions ' . ($dry ? 'to add' : 'added') . ": {$added}. Already present, skipped: {$skipped}.");
        $this->command?->line("Course: {$course->courseName} ({$course->courseCode})");
        if ($newTopics !== []) {
            $this->command?->warn(($dry ? 'Topics that would be created: ' : 'Topics created: ') . implode('; ', $newTopics));
        }
    }
}
