<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Account extends Authenticatable
{
    protected $table = 'accounts';
    protected $primaryKey = 'accountId';
    public $timestamps = false;

    // 'role' and 'status' are intentionally NOT fillable, so a request can never set them.
    // Code that needs to set them uses forceCreate()/forceFill() explicitly.
    protected $fillable = [
        'name',
        'email',
        'password',
        'tosPreparedName',
        'tosPreparedRole',
        'tosApprovedName',
        'tosApprovedRole',
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