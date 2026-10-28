<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'question';
    protected $primaryKey = 'questionId';
    public $timestamps = true;

    protected $fillable = [
        'topicId',
        'bloomLevel',
        'questionType',
        'content',
        'choices',
        'correctAnswer',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topicId', 'topicId');
    }
}