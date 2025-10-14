<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Prefer "guarded" for safety in admin panels.
     * Adjust if you truly need fillable instead.
     */
    protected $guarded = ['id', 'password', 'remember_token', 'email_verified_at', 'last_login', 'created_at', 'updated_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'application_date'  => 'date:Y-m-d', 
        'last_login'        => 'datetime',
        'password'          => 'hashed',
    ];

    /* -----------------------------------------------------------------
     | Relationships
     |------------------------------------------------------------------*/

    /**
     * Role for this user (Admin/Supervisor/Clerk/Vendor).
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Vendor’s stall (only for users with Vendor role).
     * stalls.vendor_id -> users.id
     */
    public function stall()
    {
        return $this->hasOne(Stall::class, 'vendor_id');
    }

    /**
     * Convenience: vendor’s Section via Stall.
     * users.id -> stalls.vendor_id -> sections.id (via stalls.section_id)
     */
    public function section()
    {
        return $this->hasOneThrough(
            Section::class,   // final
            Stall::class,     // intermediate
            'vendor_id',      // Stall.vendor_id (FK to users.id)
            'id',             // Section.id
            'id',             // User.id
            'section_id'      // Stall.section_id (FK to sections.id)
        );
    }

    /**
     * All billings for this vendor (through their stall).
     * users.id -> stalls.vendor_id -> billing.stall_id
     */
    public function billings()
    {
        return $this->hasManyThrough(
            Billing::class,   // final
            Stall::class,     // intermediate
            'vendor_id',      // Stall.vendor_id (FK -> users.id)
            'stall_id',       // Billing.stall_id (FK -> stalls.id)
            'id',             // User.id
            'id'              // Stall.id
        );
    }

    /**
     * Payments for this vendor (via their billings).
     * (Eloquent only supports one intermediate model for hasManyThrough,
     * so we expose a convenient QUERY builder instead of a relation.)
     *
     * Usage:
     *   $user->paymentsQuery()->latest('payment_date')->get();
     */
    public function paymentsQuery()
    {
        return Payment::whereIn(
            'billing_id',
            $this->billings()->select('billing.id') // subquery, efficient
        );
    }

    /**
     * Optional: audit trails performed by this user.
     */
    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class, 'user_id');
    }

    /* -----------------------------------------------------------------
     | Role helpers
     |------------------------------------------------------------------*/

    public function isRole(string $name): bool
    {
        // normalize to compare names like "admin", "vendor", etc.
        return optional($this->role)->name
            && strcasecmp($this->role->name, $name) === 0;
    }

    public function isAdminSupervisor(): bool      { return $this->isRole('Admin'); }
    public function isAdminAide(): bool { return $this->isRole('Staff'); }
    public function isMeterReaderClerk(): bool      { return $this->isRole('Meter Reader Clerk'); }
    public function isVendor(): bool     { return $this->isRole('Vendor'); }

    public function isActive(): bool
    {
        // assuming users.status = 'active' | 'inactive' | 'suspended' ...
        return strcasecmp((string) $this->status, 'active') === 0;
    }

    /* -----------------------------------------------------------------
     | Mutators / Accessors
     |------------------------------------------------------------------*/

    /**
     * Normalize PH-style contact numbers on set (keeps only digits).
     * Does not force a specific format; just strips non-digits safely.
     */
    protected function contactNumber(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_string($value)
                ? preg_replace('/\D+/', '', $value)
                : $value
        );
    }

    /**
     * Convenience accessor: formatted contact number (optional).
     * Example output: 0912-345-6789 (only if it matches 11 digits).
     */
    public function getContactNumberFormattedAttribute(): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $this->contact_number);
        if (strlen($d) === 11) {
            return substr($d, 0, 4) . '-' . substr($d, 4, 3) . '-' . substr($d, 7);
        }
        return $this->contact_number ?: null;
    }

    /* -----------------------------------------------------------------
     | Scopes
     |------------------------------------------------------------------*/

    /**
     * Scope: only vendors.
     */
    public function scopeVendors($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', 'Vendor'));
    }

    /**
     * Scope: active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getSemaphoreReadyContactNumber(): ?string
    {
        if (!$this->contact_number) {
            return null;
        }
        
        // Tinatanggal ang lahat ng non-digit characters
        $digits = preg_replace('/\D+/', '', $this->contact_number);
        
        // Kung nagsisimula sa '63' at may 12 digits, kino-convert sa '09...' format
        if (substr($digits, 0, 2) === '63' && strlen($digits) === 12) {
            return '0' . substr($digits, 2);
        }
        
        // Kung nasa tamang '09' format na at 11 digits, ibalik ito
        if (substr($digits, 0, 2) === '09' && strlen($digits) === 11) {
            return $digits;
        }

        // Kung hindi makilala ang format, ibalik ang null para hindi mag-send
        return null;
    }
}
