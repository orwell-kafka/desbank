<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $table = 'exam';
    protected $primaryKey = 'examId';
    public $timestamps = false;

    protected $fillable = [
        'tosId',
        'courseId',
        'title',
        'totalItems',
        'totalScore',
        'created',
    ];

    public function tos()
    {
        return $this->belongsTo(Tos::class, 'tosId', 'tosId');
    }

    /** A quiz is an exam that was built without a ToS. */
    public function isQuiz(): bool
    {
        return $this->tosId === null;
    }
}
