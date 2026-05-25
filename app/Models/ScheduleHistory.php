<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleHistory extends Model
{
    use HasFactory;

    // Define table name if it's not the plural of the model name
    protected $table = 'schedule_histories';

    public $timestamps = false; 
    
    // Timestamps are handled automatically
    // We only need to define 'updated_at' if we want to disable it.
    const UPDATED_AT = null; // This table only uses created_at

    protected $fillable = [
        'schedule_id',
        'field_changed',
        'old_value',
        'new_value',
        'changed_by',
        'changed_at',
    ];
    
    /**
     * Defines the relationship to the User who made the change.
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}