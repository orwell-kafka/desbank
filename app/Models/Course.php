<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'courseId';
    public $timestamps = false;

    protected $fillable = [
        'accountId',
        'courseName',
        'courseCode',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'accountId', 'accountId');
    }

    public function topics()
    {
        return $this->hasMany(Topic::class, 'courseId', 'courseId');
    }
    public function tos()
    {
        return $this->hasMany(Tos::class, 'courseId', 'courseId');
    }
}