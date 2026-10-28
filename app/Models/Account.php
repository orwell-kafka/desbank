<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Account extends Authenticatable
{
    protected $table = 'accounts';
    protected $primaryKey = 'accountId';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'Instructor';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}