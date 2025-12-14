<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingEditRequest extends Model
{
    // Make sure the table name is correct if not the default plural
    protected $table = 'reading_edit_requests';

    protected $fillable = [
        'reading_id', // Corrected from billing_id
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'date_of_approval_rejection',
    ];

    /**
     * Get the utility reading that this edit request belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function utilityReading(): BelongsTo
    {
        return $this->belongsTo(UtilityReading::class, 'reading_id');
    }

    /**
     * Get the user that requested the edit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user that approved the edit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}