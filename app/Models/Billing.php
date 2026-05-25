<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Billing extends Model
{
    use HasFactory;

    protected $table = 'billing';

    protected $fillable = [
        'stall_id',
        'utility_type',
        'period_start',
        'period_end',
        'amount',
        'due_date',
        'disconnection_date',
        'status',
        'previous_reading',
        'current_reading',
        'consumption',
        'rate',
        'other_fees',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'disconnection_date' => 'date',
        'amount' => 'decimal:2',
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'rate' => 'decimal:4',
        'other_fees' => 'decimal:2',
    ];

    public function stall(): BelongsTo
    {
        return $this->belongsTo(Stall::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getCurrentAmountDueAttribute(): float
    {
        if ($this->status === 'paid') {
            return (float) $this->amount;
        }

        $today = Carbon::today();
        $originalDueDate = Carbon::parse($this->due_date);

        if ($today->gt($originalDueDate)) {
            // Lazy load settings only when needed.
            $settings = BillingSetting::all()->keyBy('utility_type')->get($this->utility_type);
            $currentTotalDue = (float) $this->amount;

            if ($settings) {
                if ($this->utility_type === 'Rent') {
                    $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                    $surcharge = $this->amount * ($settings->surcharge_rate ?? 0);
                    $interest = $this->amount * ($settings->monthly_interest_rate ?? 0) * $interest_months;
                    $currentTotalDue += $surcharge + $interest;
                } else {
                    $currentTotalDue += $this->amount * ($settings->penalty_rate ?? 0);
                }
            }
            return $currentTotalDue;
        }

        return (float) $this->amount;
    }

    // Accessor for payment status
    public function getPaymentStatusAttribute(): string
    {
        if ($this->status === 'paid') {
            return 'Paid';
        } elseif ($this->due_date->isPast()) {
            return 'Overdue';
        } else {
            return 'Due';
        }
    }
}
