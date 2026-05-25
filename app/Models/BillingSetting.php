<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    use HasFactory;

    protected $table = 'billing_settings';

    protected $fillable = [
        'utility_type',
        'surcharge_rate',
        'monthly_interest_rate',
        'penalty_rate',
        'discount_rate',
    ];
}
