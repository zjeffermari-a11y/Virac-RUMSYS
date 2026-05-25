<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Billing;
use Illuminate\Support\Facades\DB;

class DeletePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:delete {payment_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes a specific payment and resets the associated billing status to unpaid.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $paymentId = $this->argument('payment_id');

        if (!is_numeric($paymentId)) {
            $this->error('Please provide a valid numeric Payment ID.');
            return 1;
        }

        $payment = Payment::find($paymentId);

        if (!$payment) {
            $this->error("No payment found with ID: {$paymentId}");
            return 1;
        }

        $billing = Billing::find($payment->billing_id);

        if (!$billing) {
            $this->error("Could not find the associated bill for this payment. Aborting.");
            return 1;
        }

        if ($this->confirm("Are you sure you want to delete the payment of '{$payment->amount_paid}' for Billing ID #{$billing->id} (Period: {$billing->period_start->format('M Y')})?")) {
            try {
                DB::transaction(function () use ($payment, $billing) {
                    // First, reset the billing status
                    $billing->status = 'unpaid';
                    $billing->save();

                    // Then, delete the payment record
                    $payment->delete();

                    $this->info("Success! Payment {$payment->id} has been deleted.");
                    $this->info("Billing record #{$billing->id} has been reset to 'unpaid'.");
                });
            } catch (\Exception $e) {
                $this->error('An error occurred: ' . $e->getMessage());
                return 1;
            }
        } else {
            $this->info('Operation cancelled.');
        }

        return 0;
    }
}