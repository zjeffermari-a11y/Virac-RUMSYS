<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Billing;
use App\Models\BillingSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CorrectOldPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:correct-old-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrects the amount_paid for historical payments that did not include penalties or discounts.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting correction of historical payment records...');

        $billingSettings = BillingSetting::all()->keyBy('utility_type');

        // Find all payments to re-evaluate
        $paymentsToCorrect = Payment::with('billing')->get();

        if ($paymentsToCorrect->isEmpty()) {
            $this->info('No payments found in the database.');
            return 0;
        }

        $this->info("Found " . $paymentsToCorrect->count() . " payment records to re-evaluate.");
        $bar = $this->output->createProgressBar($paymentsToCorrect->count());
        $bar->start();

        $updatedCount = 0;

        foreach ($paymentsToCorrect as $payment) {
            $billing = $payment->billing;
            if (!$billing) {
                $bar->advance();
                continue;
            }

            $paymentDate = Carbon::parse($payment->payment_date);
            $originalDueDate = Carbon::parse($billing->due_date);
            $originalAmount = (float)$billing->amount;
            $finalAmount = $originalAmount;
            $settings = $billingSettings->get($billing->utility_type);

            // Logic to calculate what the amount SHOULD have been on the payment date
            if ($paymentDate->gt($originalDueDate)) {
                // Payment was LATE, calculate penalties
                if ($billing->utility_type === 'Rent' && $settings) {
                    $interest_months = (int) floor($originalDueDate->floatDiffInMonths($paymentDate));
                    $surcharge = $originalAmount * (float)($settings->surcharge_rate ?? 0);
                    $interest = $originalAmount * (float)($settings->monthly_interest_rate ?? 0) * $interest_months;
                    $finalAmount += $surcharge + $interest;
                } else if ($settings) {
                    $penalty = $originalAmount * (float)($settings->penalty_rate ?? 0);
                    $finalAmount += $penalty;
                }
            } else if ($paymentDate->day <= 15) {
                // Payment was ON TIME, check for discount
                $billMonth = Carbon::parse($billing->period_start)->format('Y-m');
                $paymentMonth = $paymentDate->format('Y-m');

                if ($billMonth === $paymentMonth && $billing->utility_type === 'Rent' && $settings && (float)$settings->discount_rate > 0) {
                    // Discount calculation: Original Price - (Original Price * discount_rate)
                    // Equivalent to: Original Price * (1 - discount_rate)
                    $finalAmount = $originalAmount - ($originalAmount * (float)$settings->discount_rate);
                }
            }

            // Update the payment record only if the calculated amount is different from what was stored
            if (round((float)$payment->amount_paid, 2) !== round($finalAmount, 2)) {
                $payment->amount_paid = $finalAmount;
                $payment->save();
                $updatedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nCorrection complete. Updated {$updatedCount} historical payment record(s).");
        return 0;
    }
}