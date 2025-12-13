<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityReading extends Model
{
    use HasFactory;

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'utility_readings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'stall_id',
        'utility_type',
        'reading_date',
        'current_reading',
        'previous_reading',
        'consumption',
    ];

    /**
     * Get the stall that this reading belongs to.
     */
    public function stall(): BelongsTo
    {
        return $this->belongsTo(Stall::class);
    }

    /**
     * Get the edit requests for this utility reading.
     */
    public function editRequests(): HasMany
    {
        // This assumes a ReadingEditRequest has a 'reading_id' foreign key
        return $this->hasMany(ReadingEditRequest::class, 'reading_id');
    }
}