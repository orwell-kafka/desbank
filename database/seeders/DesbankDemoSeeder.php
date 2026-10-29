<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Local development data only. Creates a separate, already-approved instructor
 * with two courses, six topics, 90 questions (15 per topic), two ToS, and two generated exams.
 *
 * Run: php artisan db:seed --class=DesbankDemoSeeder
 * Optional (Windows cmd): set DESBANK_DEMO_PASSWORD=your-strong-local-password
 *
 * This does not modify the application's DatabaseSeeder.php or other users.
 */
final class DesbankDemoSeeder extends Seeder
{
    private const EMAIL = 'demo.instructor@desbank.test';

    private const BLOOM_CONFIGURATION = [
        ['Remembering',   'Multiple Choice',    1],
        ['Understanding', 'True/False',         1],
        ['Applying',      'Fill in the Blanks', 1],
        ['Analyzing',     'Enumeration',        2],
        ['Evaluating',    'Matching Type',      1],
        ['Creating',      'Essay',              4],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Demo seeding is disabled in production.');
        }

        $this->checkSchema();

        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'desbank_demo_content.json';
        if (! is_file($filename)) {
            throw new RuntimeException('Missing desbank_demo_content.json: extract BOTH seeder files into database/seeders/.');
        }
        $courses = json_decode((string) file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($courses) || count($courses) !== 2) {
            throw new RuntimeException('Unexpected demo data format. Expected two courses.');
        }

        // Existing 12-question/topic demo installations are upgraded in place.
        // Never overwrite the demo password, exams, manually edited questions,
        // administrator, or unrelated instructors/courses.
        $existingAccount = DB::table('accounts')->where('email', self::EMAIL)->first();
        if ($existingAccount !== null) {
            if ($existingAccount->role !== 'Instructor') {
                throw new RuntimeException('Demo email belongs to a non-instructor. No changes were made.');
            }
            $added = $this->upgradeExistingQuestions($courses, (int) $existingAccount->accountId);
            $this->command?->info('Demo questions are now 15 per topic (6 topics, 90 total).');
            $this->command?->line('New questions added: ' . $added);
            $this->command?->line('Existing instructor password, courses, ToS and exams were retained.');
            $this->command?->line('Email: ' . self::EMAIL);
            return;
        }

        // Guard against inserting course codes already owned by someone else.
        foreach ($courses as $course) {
            if (DB::table('courses')->where('courseCode', $course['courseCode'])->exists()) {
                throw new RuntimeException('Course code ' . $course['courseCode'] . ' already exists. No data was changed.');
            }
        }

        $provided = getenv('DESBANK_DEMO_PASSWORD');
        $password = $provided === false || $provided === ''
            ? bin2hex(random_bytes(10))
            : $provided;
        if (strlen($password) < 12) {
            throw new RuntimeException('DESBANK_DEMO_PASSWORD must be at least 12 characters. No data was changed.');
        }

        $summary = DB::transaction(function () use ($courses, $password): array {
            $accountId = DB::table('accounts')->insertGetId([
                'name' => 'Demo Instructor',
                'email' => self::EMAIL,
                'password' => Hash::make($password),
                'role' => 'Instructor',
                'status' => 'approved',
            ], 'accountId');

            $result = ['courses' => [], 'exams' => [], 'questionCount' => 0];

            foreach ($courses as $courseData) {
                $courseId = DB::table('courses')->insertGetId([
                    'accountId' => $accountId,
                    'courseName' => $courseData['courseName'],
                    'courseCode' => $courseData['courseCode'],
                ], 'courseId');

                $topicIds = [];
                $questionIds = [];
                foreach ($courseData['topics'] as $topicData) {
                    $topicId = DB::table('topic')->insertGetId([
                        'courseId' => $courseId,
                        'topicName' => $topicData['topicName'],
                    ], 'topicId');
                    $topicIds[] = $topicId;

                    foreach ($topicData['questions'] as $questionData) {
                        $id = $this->insertQuestion($topicId, $questionData);
                        $key = $questionData['bloomLevel'] . '|' . $questionData['questionType'];
                        $questionIds[$key][$topicId][] = $id;
                        $result['questionCount']++;
                    }
                }

                // 18 exam items: three questions of each of six supported types.
                // Total = 3*1 + 3*1 + 3*1 + 3*2 + 3*1 + 3*4 = 30 points.
                $tosId = DB::table('tos')->insertGetId([
                    'courseId' => $courseId,
                    'title' => '[DEMO] Midterm Exam Blueprint',
                    'examType' => 'Midterm',
                    'totalScore' => 30,
                    'contactHours' => 0,
                    'generatedWeight' => 0,
                ], 'tosId');

                foreach ($topicIds as $i => $topicId) {
                    DB::table('tostopic')->insert([
                        'tosId' => $tosId,
                        'topicId' => $topicId,
                        'contactHours' => 2,
                        'generatedWeight' => $i === 2 ? 33.34 : 33.33,
                        'assignedPoints' => 10,
                    ]);
                }

                $examId = DB::table('exam')->insertGetId(
                    $this->examRow($tosId),
                    'examId'
                );

                foreach (self::BLOOM_CONFIGURATION as [$level, $type, $points]) {
                    $key = $level . '|' . $type;
                    $selected = [];
                    foreach ($topicIds as $topicId) {
                        // The other question in each topic is left available for Replace.
                        $available = $questionIds[$key][$topicId] ?? [];
                        if (count($available) < 2) {
                            throw new RuntimeException("Insufficient sample questions for {$key} in topic {$topicId}.");
                        }
                        $selected[] = $available[0];
                    }

                    DB::table('tosbloom')->insert([
                        'tosId' => $tosId,
                        'bloomLevel' => $level,
                        'questionType' => $type,
                        'pointsPerItem' => $points,
                        'generatedItemCount' => 3,
                        'generatedScore' => 3 * $points,
                    ]);
                    DB::table('exambloomconfig')->insert([
                        'examId' => $examId,
                        'bloomLevel' => $level,
                        'numberItems' => 3,
                        'numScore' => 3 * $points,
                    ]);
                    foreach ($selected as $questionId) {
                        DB::table('examquestion')->insert([
                            'examId' => $examId,
                            'questionId' => $questionId,
                        ]);
                    }
                }

                $result['courses'][] = ['id' => $courseId, 'code' => $courseData['courseCode']];
                $result['exams'][] = ['id' => $examId, 'courseId' => $courseId];
            }

            return $result;
        });

        $this->command?->info('Desbank demo installed successfully.');
        $this->command?->line('Email: ' . self::EMAIL);
        $this->command?->line('Password (save this NOW; shown only at first creation): ' . $password);
        $this->command?->line('Status: approved | Role: Instructor');
        $this->command?->line('Courses: ' . count($summary['courses']) . ' | Topics: 6 | Questions: ' . $summary['questionCount'] . ' (15 per topic)');
        $this->command?->line('ToS: 2 | Exams: ' . count($summary['exams']) . ' | Questions per exam: 18');
        foreach ($summary['exams'] as $exam) {
            $this->command?->line('Exam preview: http://127.0.0.1:8000/courses/' . $exam['courseId'] . '/exams/' . $exam['id'] . '/format');
        }
        $this->command?->warn('Local testing only. Do not use these demo credentials or records on a public site.');
    }

    /**
     * Bring an earlier 12-question-per-topic demo up to 15 without duplication.
     * Existing data, account credentials, and generated exams are left untouched.
     */
    private function upgradeExistingQuestions(array $courses, int $accountId): int
    {
        return DB::transaction(function () use ($courses, $accountId): int {
            $added = 0;
            foreach ($courses as $courseData) {
                $course = DB::table('courses')
                    ->where('accountId', $accountId)
                    ->where('courseCode', $courseData['courseCode'])
                    ->first();
                if ($course === null) {
                    throw new RuntimeException('Demo course ' . $courseData['courseCode'] . ' not found under this demo account. No data was changed.');
                }
                foreach ($courseData['topics'] as $topicData) {
                    $topic = DB::table('topic')
                        ->where('courseId', $course->courseId)
                        ->where('topicName', $topicData['topicName'])
                        ->first();
                    if ($topic === null) {
                        throw new RuntimeException('Demo topic ' . $topicData['topicName'] . ' not found. No data was changed.');
                    }
                    $topicId = (int) $topic->topicId;
                    $existingQuestions = DB::table('question')->where('topicId', $topicId)->pluck('content')->all();
                    $count = count($existingQuestions);
                    if ($count < 12 || $count > 15) {
                        throw new RuntimeException("{$topicData['topicName']} has {$count} questions, expected 12–15; refusing to modify this topic. No data was changed.");
                    }
                    $seen = array_fill_keys($existingQuestions, true);
                    // Exactly three new, distinct questions per topic in version 2.
                    // Already installed questions are skipped, including on reruns.
                    foreach (array_slice($topicData['questions'], 12) as $questionData) {
                        if ($count >= 15) {
                            break;
                        }
                        if (isset($seen[$questionData['content']])) {
                            continue;
                        }
                        $this->insertQuestion($topicId, $questionData);
                        $seen[$questionData['content']] = true;
                        $added++;
                        $count++;
                    }
                    if ($count !== 15) {
                        throw new RuntimeException("Cannot reach 15 unique questions for topic {$topicData['topicName']}; no data was changed.");
                    }
                }
            }
            return $added;
        });
    }

    private function insertQuestion(int $topicId, array $questionData): int
    {
        $row = [
            'topicId' => $topicId,
            'bloomLevel' => $questionData['bloomLevel'],
            'questionType' => $questionData['questionType'],
            'content' => $questionData['content'],
            'choices' => isset($questionData['choices']) ? implode(',', $questionData['choices']) : null,
            'correctAnswer' => $questionData['correctAnswer'],
        ];
        if (Schema::hasColumn('question', 'created_at')) {
            $row['created_at'] = now();
        }
        if (Schema::hasColumn('question', 'updated_at')) {
            $row['updated_at'] = now();
        }
        return DB::table('question')->insertGetId($row, 'questionId');
    }

    private function examRow(int $tosId): array
    {
        $row = [
            'tosId' => $tosId,
            'totalItems' => 18,
            'totalScore' => 30,
            'created' => now(),
        ];
        $optional = [
            'paperSize' => 'Letter',
            'schoolName' => 'DESBank Demo Institute',
            'department' => 'Academic Testing Department',
            'footerText' => 'DEMO CONTENT - NOT FOR ACTUAL ASSESSMENT',
            'watermarkText' => 'SAMPLE',
            'headerTemplate' => 'classic',
            'footerTemplate' => 'simple',
            'partOrder' => 'Multiple Choice,True/False,Fill in the Blanks,Enumeration,Matching Type,Essay',
            'columnsPerPart' => json_encode(['Multiple Choice' => 2, 'True/False' => 2, 'Essay' => 1]),
            'headerTop' => 50,
            'fontFamily' => 'Arial',
            'fontSize' => 13,
        ];
        foreach ($optional as $field => $value) {
            if (Schema::hasColumn('exam', $field)) {
                $row[$field] = $value;
            }
        }
        return $row;
    }

    private function checkSchema(): void
    {
        // This is intentionally strict: unknown Claude-generated migrations may
        // have different columns. Stop BEFORE writing any data if incompatible.
        $required = [
            'accounts' => ['accountId', 'name', 'email', 'password', 'role', 'status'],
            'courses' => ['courseId', 'accountId', 'courseName', 'courseCode'],
            'topic' => ['topicId', 'courseId', 'topicName'],
            'question' => ['questionId', 'topicId', 'bloomLevel', 'questionType', 'content', 'choices', 'correctAnswer'],
            'tos' => ['tosId', 'courseId', 'title', 'examType', 'totalScore', 'contactHours', 'generatedWeight'],
            'tostopic' => ['tosId', 'topicId', 'contactHours', 'generatedWeight', 'assignedPoints'],
            'tosbloom' => ['tosId', 'bloomLevel', 'questionType', 'pointsPerItem', 'generatedItemCount', 'generatedScore'],
            'exam' => ['examId', 'tosId', 'totalItems', 'totalScore', 'created'],
            'examquestion' => ['examId', 'questionId'],
            'exambloomconfig' => ['examId', 'bloomLevel', 'numberItems', 'numScore'],
        ];
        foreach ($required as $table => $columns) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Required table '{$table}' is missing. Run the base schema migrations first.");
            }
            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    throw new RuntimeException("Required column '{$table}.{$column}' is missing. No demo data was changed.");
                }
            }
        }
    }
}
