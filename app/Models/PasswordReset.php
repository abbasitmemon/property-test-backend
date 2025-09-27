<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $fillable = ['email','token', 'created_at'];
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'email';
    public $incrementing = false;

    protected $hidden = [];

    public $timestamps = false;
}
