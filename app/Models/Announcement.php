<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'content', 
        'is_active',
        'announcement_type',
        'related_section',
        'related_utility',
        'related_stall_id',
        'recipients',
        'effectivity_date',
    ];

    protected $casts = [
        'recipients' => 'array',
    ];
}

