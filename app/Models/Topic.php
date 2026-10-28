<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $table = 'topic';
    protected $primaryKey = 'topicId';
    public $timestamps = false;

    protected $fillable = [
        'courseId',
        'topicName',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseId', 'courseId');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'topicId', 'topicId');
    }
}