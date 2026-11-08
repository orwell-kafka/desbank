<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tos extends Model
{
    protected $table = 'tos';
    protected $primaryKey = 'tosId';
    public $timestamps = false;

    protected $fillable = [
        'courseId',
        'title',
        'examType',
        'totalScore',
        'contactHours',
        'generatedWeight',
        'academicYear',
        'semester',
        'hoursPerWeek',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId', 'courseId');
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'tostopic', 'tosId', 'topicId')
            ->withPivot(['contactHours', 'generatedWeight', 'assignedPoints']);
    }

    /** The two exam types a ToS can be made for. Quizzes get no ToS. */
    public const EXAM_TYPES = ['Midterm', 'Final'];

    /** The generated "Subject Code/ Title" line, e.g. "CPEC 1: Cognate Elective 1". */
    public static function headingTitle(Course $course): string
    {
        return trim($course->courseCode . ': ' . $course->courseName);
    }
}
