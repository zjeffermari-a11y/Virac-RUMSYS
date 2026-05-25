<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stall extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'section_id',
        'stall_number',
        'table_number',
        'area',
        'vendor_id',
        'daily_rate',   
        'monthly_rate',  
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'area' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Get the section that the stall belongs to.
     * A stall belongs to one section.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the vendor (user) that owns the stall.
     * A stall belongs to one vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the billing records for the stall.
     * A stall can have many billing records.
     */
    public function billings()
    {
        return $this->hasMany(Billing::class);
    }

    /**
     * Get the reading edit requests for the stall.
     * A stall can have many edit requests.
     */
    public function editRequests()
    {
        return $this->hasMany(ReadingEditRequest::class);
    }

    /**
     * Get the utility readings for the stall.
     * A stall can have many utility readings.
     */
    public function utilityReadings()
    {
        return $this->hasMany(UtilityReading::class);
    }
}
