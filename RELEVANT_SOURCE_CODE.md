# Relevant Source Code Snippets - Virac Public Market RUMSYS

> **Note for Paper Submission**: The triple backticks (```) are markdown syntax for code formatting. 
> - **Remove them** when pasting into Word/Google Docs/PDF - use your editor's code formatting instead
> - **Remove them** when using LaTeX - use `verbatim`, `lstlisting`, or `minted` environments
> - **Keep them** only if your paper format supports markdown (e.g., some online journals)

## 1. Billing Calculation with Penalties and Interest

```php
// app/Models/Billing.php - getCurrentAmountDueAttribute()
public function getCurrentAmountDueAttribute(): float
{
    if ($this->status === 'paid') {
        return (float) $this->amount;
    }

    $today = Carbon::today();
    $originalDueDate = Carbon::parse($this->due_date);

    if ($today->gt($originalDueDate)) {
        $settings = BillingSetting::all()->keyBy('utility_type')->get($this->utility_type);
        $currentTotalDue = (float) $this->amount;

        if ($settings) {
            if ($this->utility_type === 'Rent') {
                // Rent: Surcharge + Monthly Interest
                $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
                $surcharge = $this->amount * ($settings->surcharge_rate ?? 0);
                $interest = $this->amount * ($settings->monthly_interest_rate ?? 0) * $interest_months;
                $currentTotalDue += $surcharge + $interest;
            } else {
                // Utilities: Penalty Rate
                $currentTotalDue += $this->amount * ($settings->penalty_rate ?? 0);
            }
        }
        return $currentTotalDue;
    }

    return (float) $this->amount;
}
```

## 2. Early Payment Discount Calculation

```php
// app/Http/Controllers/VendorController.php
if ($todayDay <= 15) {
    $billMonth = Carbon::parse($bill->period_start)->format('Y-m');
    if ($billMonth === $currentMonth && $bill->utility_type === 'Rent' 
        && $settings && (float)$settings->discount_rate > 0) {
        $discountAmount = $bill->original_amount * (float)$settings->discount_rate;
        $bill->display_amount_due = $bill->original_amount - $discountAmount;
    }
}
```

## 3. Real-time Billing Generation from Meter Readings

```php
// app/Http/Controllers/UtilityReadingController.php - storeBulk()
$consumption = $reading->current_reading - $finalPreviousReading;
if ($consumption < 0) {
    $consumption = 0;
}
$amount = $consumption * $electricityRate;

Billing::updateOrCreate(
    [
        'stall_id' => $reading->stall_id,
        'utility_type' => 'Electricity',
        'period_start' => $periodStart->toDateString(),
    ],
    [
        'period_end' => $periodEnd->toDateString(),
        'amount' => $amount,
        'previous_reading' => $finalPreviousReading,
        'current_reading' => $reading->current_reading,
        'consumption' => $consumption,
        'rate' => $electricityRate,
        'due_date' => $dueDate,
        'disconnection_date' => $disconnectionDate,
        'status' => 'unpaid'
    ]
);
```

## 4. SMS Notification with Template Processing

```php
// app/Services/SmsService.php - sendTemplatedSms()
$unpaidBills = $user->billings()->where('billing.status', 'unpaid')->get();
$billingSettings = BillingSetting::all()->keyBy('utility_type');
$today = Carbon::today();

foreach ($unpaidBills as $bill) {
    $originalDueDate = Carbon::parse($bill->due_date);
    if ($today->gt($originalDueDate)) {
        $settings = $billingSettings->get($bill->utility_type);
        if ($bill->utility_type === 'Rent' && $settings) {
            $interest_months = (int) floor($originalDueDate->floatDiffInMonths($today));
            $surcharge = $bill->amount * ($settings->surcharge_rate ?? 0);
            $interest = $bill->amount * ($settings->monthly_interest_rate ?? 0) * $interest_months;
            $bill->current_amount_due = $bill->amount + $surcharge + $interest;
        } else if ($settings) {
            $penalty = $bill->amount * ($settings->penalty_rate ?? 0);
            $bill->current_amount_due = $bill->amount + $penalty;
        }
    }
}

$replacements = [
    '{{vendor_name}}' => $user->name ?? 'N/A',
    '{{stall_number}}' => ($user->stall->table_number ?? 'N/A'),
    '{{total_due}}' => number_format($unpaidBills->sum('current_amount_due'), 2),
    '{{due_date}}' => $unpaidBills->sortBy('due_date')->first() 
        ? Carbon::parse($unpaidBills->sortBy('due_date')->first()->due_date)->format('M d, Y') 
        : 'N/A',
];

$finalMessage = str_replace(array_keys($replacements), array_values($replacements), $messageTemplate);
return $this->send($recipientNumber, $finalMessage);
```

## 5. Dashboard Analytics - Top Performing Vendors

```php
// app/Http/Controllers/Api/DashboardController.php - getTopPerformingVendors()
$vendorStats = DB::table('users')
    ->join('stalls', 'users.id', '=', 'stalls.vendor_id')
    ->join('billing', 'stalls.id', '=', 'billing.stall_id')
    ->leftJoin('payments', 'billing.id', '=', 'payments.billing_id')
    ->where('billing.status', 'paid')
    ->whereYear('billing.period_start', $year)
    ->select(
        'users.name',
        'stalls.table_number',
        DB::raw('COUNT(billing.id) as paid_bills_count'),
        DB::raw('SUM(CASE WHEN payments.payment_date <= billing.due_date THEN 1 ELSE 0 END) as on_time_bills_count')
    )
    ->groupBy('users.id', 'users.name', 'stalls.table_number')
    ->orderByRaw('(SUM(CASE WHEN payments.payment_date <= billing.due_date THEN 1 ELSE 0 END)) / (COUNT(billing.id)) DESC')
    ->limit(5)
    ->get();

$formattedVendors = $vendorStats->map(function ($vendor) {
    $onTimePercentage = round(($vendor->on_time_bills_count / $vendor->paid_bills_count) * 100);
    return [
        'name' => $vendor->name,
        'stall_number' => $vendor->table_number,
        'metric' => "{$onTimePercentage}% On-Time",
    ];
});
```

## 6. Audit Logging

```php
// app/Services/AuditLogger.php
public static function log($action, $module, $result = 'Success', $details = null)
{
    $user = Auth::user();
    if (is_array($details) || is_object($details)) {
        $details = json_encode($details);
    }

    AuditTrail::create([
        'user_id' => $user->id,
        'role_id' => $user->role_id,
        'action' => substr($action, 0, 100),
        'module' => substr($module, 0, 50),
        'result' => substr($result, 0, 50),
        'details' => $details,
    ]);
}
```

## 7. User-Billing Relationship (Many-to-Many Through Stall)

```php
// app/Models/User.php
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
```

