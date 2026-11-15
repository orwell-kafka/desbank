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
        'creditUnits',
        'coverImage',
    ];

    public const COVER_DIR = 'uploads/course-covers';

    /** Public URL of the cover picture, or null when the course has none. */
    public function coverUrl(): ?string
    {
        return $this->coverImage ? asset(self::COVER_DIR . '/' . basename($this->coverImage)) : null;
    }

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