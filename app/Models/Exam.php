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
        'totalItems',
        'totalScore',
        'created',
    ];

    public function tos()
    {
        return $this->belongsTo(Tos::class, 'tosId', 'tosId');
    }
}