<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Adds real Remembering-level questions to the six DESBank demo topics so
 * exams with Remembering rows can be generated.
 *
 * Per topic: 5 Enumeration, 5 True/False, 3 Fill in the Blanks,
 * 2 Multiple Choice, 1 Essay (16 questions, 96 in total).
 *
 * Data file: database/seeders/desbank_extra_questions.json (put it next to this file).
 *
 * Preview only (Windows cmd):
 *   set DESBANK_QUESTIONS_DRY=1
 *   php artisan db:seed --class=DesbankRememberingQuestionsSeeder
 * Run for real:
 *   set DESBANK_QUESTIONS_DRY=
 *   php artisan db:seed --class=DesbankRememberingQuestionsSeeder
 * Other account (default is the demo instructor):
 *   set DESBANK_QUESTIONS_EMAIL=you@example.com
 *
 * Safe to run twice: a question whose text already exists in the same topic is skipped.
 * Nothing is ever updated or deleted.
 */
final class DesbankRememberingQuestionsSeeder extends Seeder
{
    private const DEFAULT_EMAIL = 'demo.instructor@desbank.test';

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('This seeder is disabled in production.');
        }

        $file = __DIR__ . DIRECTORY_SEPARATOR . 'desbank_extra_questions.json';
        if (! is_file($file)) {
            throw new RuntimeException('Missing desbank_extra_questions.json: copy it into database/seeders/ next to this file.');
        }
        $groups = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        foreach (['accounts', 'courses', 'topic', 'question'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Table '{$table}' is missing. Run php artisan migrate first. No data was changed.");
            }
        }

        $email = getenv('DESBANK_QUESTIONS_EMAIL') ?: self::DEFAULT_EMAIL;
        $dry = (string) getenv('DESBANK_QUESTIONS_DRY') !== '';

        $account = DB::table('accounts')->where('email', $email)->first();
        if ($account === null) {
            throw new RuntimeException("No account with email {$email}. Set DESBANK_QUESTIONS_EMAIL to your instructor's email. No data was changed.");
        }

        $hasCreated = Schema::hasColumn('question', 'created_at');
        $hasUpdated = Schema::hasColumn('question', 'updated_at');

        $added = 0;
        $skipped = 0;
        $missingTopics = [];

        DB::transaction(function () use ($groups, $account, $dry, $hasCreated, $hasUpdated, &$added, &$skipped, &$missingTopics) {
            foreach ($groups as $group) {
                $topics = DB::table('topic')
                    ->join('courses', 'courses.courseId', '=', 'topic.courseId')
                    ->where('courses.accountId', $account->accountId)
                    ->where('topic.topicName', $group['topicName'])
                    ->select('topic.topicId', 'courses.courseCode')
                    ->get();

                if ($topics->isEmpty()) {
                    $missingTopics[] = $group['topicName'];
                    continue;
                }

                foreach ($topics as $topic) {
                    $existing = array_flip(
                        DB::table('question')->where('topicId', $topic->topicId)->pluck('content')->all()
                    );
                    $addedHere = 0;

                    foreach ($group['questions'] as $q) {
                        if (isset($existing[$q['content']])) {
                            $skipped++;
                            continue;
                        }
                        if (! $dry) {
                            $row = [
                                'topicId'       => $topic->topicId,
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

                    $this->command?->line(sprintf(
                        '%s | %s: %d %s',
                        $topic->courseCode,
                        $group['topicName'],
                        $addedHere,
                        $dry ? 'would be added' : 'added'
                    ));
                }
            }
        });

        $this->command?->info(($dry ? 'DRY RUN, nothing written. ' : '') . "Questions " . ($dry ? 'to add' : 'added') . ": {$added}. Already present, skipped: {$skipped}.");
        $this->command?->line("Account: {$email}");
        if ($missingTopics !== []) {
            $this->command?->warn('Topics not found for this account (no questions added for them): ' . implode('; ', $missingTopics));
        }
    }
}
