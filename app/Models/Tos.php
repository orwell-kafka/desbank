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
}