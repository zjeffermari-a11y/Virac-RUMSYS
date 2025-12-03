<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    public $timestamps = false; // The table has created_at but no updated_at

    protected $fillable = [
        'user_id',
        'role_id',
        'action',
        'module',
        'result',
        'details',
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
