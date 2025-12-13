<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class ApplyPendingRateChanges extends Command
{
    protected $signature = 'billing:apply-pending-changes {date? : The date to check for (YYYY-MM-DD)}';
    protected $description = 'Applies pending rate and setting changes with effectivity dates in the current month, regenerates bills, and sends SMS notifications.';

    public function handle()
    {
        $dateInput = $this->argument('date');
        $today = $dateInput ? Carbon::parse($dateInput) : Carbon::today();
        
        $this->info("Checking for pending changes with effectivity dates in: {$today->format('F Y')}...");
        
        $currentMonthStart = $today->copy()->startOfMonth();
        $currentMonthEnd = $today->copy()->endOfMonth();
        $currentMonthStartStr = $currentMonthStart->format('Y-m-d');
        $currentMonthEndStr = $currentMonthEnd->format('Y-m-d');
        
        $hasChanges = false;
        
        try {
            DB::beginTransaction();
            
            // 1. Apply pending rate changes
            $hasEffectivityDate = DB::getSchemaBuilder()->hasColumn('rate_histories', 'effectivity_date');
            if ($hasEffectivityDate) {
                $pendingRateChanges = DB::table('rate_histories as rh')
                    ->join('rates as r', 'rh.rate_id', '=', 'r.id')
                    ->whereNotNull('rh.effectivity_date')
                    ->whereDate('rh.effectivity_date', '>=', $currentMonthStart)
                    ->whereDate('rh.effectivity_date', '<=', $currentMonthEnd)
                    ->select('r.id as rate_id', 'rh.new_rate', 'r.utility_type', 'rh.effectivity_date')
                    ->get();
                
                foreach ($pendingRateChanges as $change) {
                    $currentRate = DB::table('rates')->where('id', $change->rate_id)->value('rate');
                    if ($currentRate != $change->new_rate) {
                        DB::table('rates')
                            ->where('id', $change->rate_id)
                            ->update(['rate' => $change->new_rate]);
                        
                        $this->info("  ✓ Applied rate change for {$change->utility_type}: ₱{$currentRate} → ₱{$change->new_rate} (effective {$change->effectivity_date})");
                        $hasChanges = true;
                    }
                }
            }
            
            // 2. Apply pending billing setting changes
            $hasBillingSettingEffectivityDate = DB::getSchemaBuilder()->hasColumn('billing_setting_histories', 'effectivity_date');
            if ($hasBillingSettingEffectivityDate) {
                $pendingSettingChanges = DB::table('billing_setting_histories as bsh')
                    ->join('billing_settings as bs', 'bsh.billing_setting_id', '=', 'bs.id')
                    ->whereNotNull('bsh.effectivity_date')
                    ->whereDate('bsh.effectivity_date', '>=', $currentMonthStart)
                    ->whereDate('bsh.effectivity_date', '<=', $currentMonthEnd)
                    ->select('bs.id as setting_id', 'bsh.field_changed', 'bsh.new_value', 'bs.utility_type', 'bsh.effectivity_date')
                    ->get();
                
                foreach ($pendingSettingChanges as $change) {
                    DB::table('billing_settings')
                        ->where('id', $change->setting_id)
                        ->update([$change->field_changed => $change->new_value]);
                    
                    $this->info("  ✓ Applied billing setting change for {$change->utility_type} - {$change->field_changed}: {$change->new_value} (effective {$change->effectivity_date})");
                    $hasChanges = true;
                }
            }
            
            DB::commit();
            
            // 3. If there are changes, regenerate bills and send SMS
            if ($hasChanges) {
                $this->info("\nChanges detected. Regenerating bills...");
                
                // Get billing IDs for current month before deletion
                $billingIds = DB::table('billing')
                    ->where('period_start', '>=', $currentMonthStartStr)
                    ->where('period_start', '<=', $currentMonthEndStr)
                    ->pluck('id')
                    ->toArray();
                
                // Delete associated payments first
                if (!empty($billingIds)) {
                    DB::table('payments')
                        ->whereIn('billing_id', $billingIds)
                        ->delete();
                }
                
                // Delete existing bills for current month
                DB::table('billing')
                    ->where('period_start', '>=', $currentMonthStartStr)
                    ->where('period_start', '<=', $currentMonthEndStr)
                    ->delete();
                
                // Regenerate bills
                Artisan::call('billing:generate', ['date' => $today->format('Y-m-d')]);
                $this->info("  ✓ Bills regenerated");
                
                // Small delay to ensure bills are generated
                sleep(2);
                
                // Send SMS notifications
                Artisan::call('sms:send-billing-statements', ['--force' => true]);
                $this->info("  ✓ SMS notifications sent");
                
                $this->info("\n✅ All pending changes applied successfully!");
            } else {
                $this->info("No pending changes with effectivity dates in {$today->format('F Y')}.");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error applying pending changes: " . $e->getMessage());
            return 1;
        }
    }
}

