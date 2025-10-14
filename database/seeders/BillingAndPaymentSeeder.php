<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Stall;
use App\Models\Billing;
use App\Models\Payment;
use Carbon\Carbon;

class BillingAndPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $vendorUser = User::where('name', 'Johnny Doe')->first();
        $stall = Stall::where('vendor_id', $vendorUser->id)->first();

        if (!$stall) {
            $this->command->error('Could not find the stall for Johnny Doe. Please run the UserAndStallSeeder first.');
            return;
        }

        // --- Data with Bill Breakdown Details ---
        $paymentHistory = [
            // --- August 2025 Bills (ALL PENDING) ---
            ['year' => 2025, 'month' => 8, 'category' => 'RENT', 'amount' => 2500.00, 'dueDate' => '2025-09-05', 'status' => 'pending'],
            ['year' => 2025, 'month' => 8, 'category' => 'ELECTRICITY', 'amount' => 3500.00, 'dueDate' => '2025-09-10', 'status' => 'pending', 'prev' => 1200, 'curr' => 1450, 'rate' => 14.00],
            ['year' => 2025, 'month' => 8, 'category' => 'WATER', 'amount' => 1950.00, 'dueDate' => '2025-09-08', 'status' => 'pending', 'prev' => 850, 'curr' => 900, 'rate' => 39.00],

            // --- July 2025 Bills (ALL PAID) ---
            ['year' => 2025, 'month' => 7, 'category' => 'RENT', 'amount' => 2500.00, 'dueDate' => '2025-08-05', 'status' => 'paid', 'paid_on' => '2025-08-04'],
            ['year' => 2025, 'month' => 7, 'category' => 'ELECTRICITY', 'amount' => 3100.00, 'dueDate' => '2025-08-10', 'status' => 'paid', 'paid_on' => '2025-08-08', 'prev' => 980, 'curr' => 1200, 'rate' => 14.09],
            ['year' => 2025, 'month' => 7, 'category' => 'WATER', 'amount' => 1700.00, 'dueDate' => '2025-08-08', 'status' => 'paid', 'paid_on' => '2025-08-07', 'prev' => 810, 'curr' => 850, 'rate' => 42.50],

            // --- April 2025 Bills (ALL PENDING) ---
            ['year' => 2025, 'month' => 4, 'category' => 'RENT', 'amount' => 2500.00, 'dueDate' => '2025-05-05', 'status' => 'pending'],
            ['year' => 2025, 'month' => 4, 'category' => 'ELECTRICITY', 'amount' => 3200.00, 'dueDate' => '2025-05-10', 'status' => 'pending', 'prev' => 750, 'curr' => 980, 'rate' => 13.91],
            ['year' => 2025, 'month' => 4, 'category' => 'WATER', 'amount' => 1800.00, 'dueDate' => '2025-05-08', 'status' => 'pending', 'prev' => 770, 'curr' => 810, 'rate' => 45.00],

            // --- March 2025 Bills (ALL PAID) ---
            ['year' => 2025, 'month' => 3, 'category' => 'RENT', 'amount' => 2500.00, 'dueDate' => '2025-04-05', 'status' => 'paid', 'paid_on' => '2025-04-03'],
            ['year' => 2025, 'month' => 3, 'category' => 'ELECTRICITY', 'amount' => 3200.00, 'dueDate' => '2025-04-10', 'status' => 'paid', 'paid_on' => '2025-04-08', 'prev' => 550, 'curr' => 750, 'rate' => 16.00],
            ['year' => 2025, 'month' => 3, 'category' => 'WATER', 'amount' => 1800.00, 'dueDate' => '2025-04-08', 'status' => 'paid', 'paid_on' => '2025-04-06', 'prev' => 730, 'curr' => 770, 'rate' => 45.00],

            // --- December 2024 Bills (ALL PAID) ---
            ['year' => 2024, 'month' => 12, 'category' => 'RENT', 'amount' => 2500.00, 'dueDate' => '2025-01-05', 'status' => 'paid', 'paid_on' => '2025-01-03'],
            ['year' => 2024, 'month' => 12, 'category' => 'ELECTRICITY', 'amount' => 3800.00, 'dueDate' => '2025-01-10', 'status' => 'paid', 'paid_on' => '2025-01-08', 'prev' => 300, 'curr' => 550, 'rate' => 15.20],
            ['year' => 2024, 'month' => 12, 'category' => 'WATER', 'amount' => 490.00, 'dueDate' => '2025-01-05', 'status' => 'paid', 'paid_on' => '2025-01-03', 'prev' => 720, 'curr' => 730, 'rate' => 49.00],
        ];


        foreach ($paymentHistory as $record) {
            // CORRECTED: Use 'year' and 'month' to create the billing period.
            $periodStart = Carbon::create($record['year'], $record['month'], 1);
            $periodEnd = $periodStart->copy()->endOfMonth();

            $billingData = [
                'stall_id' => $stall->id,
                'utility_type' => ucfirst(strtolower($record['category'])),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'amount' => $record['amount'],
                'due_date' => Carbon::parse($record['dueDate']),
                'disconnection_date' => Carbon::parse($record['dueDate'])->addDays(10),
                'status' => $record['status'] === 'pending' ? 'unpaid' : 'paid',
            ];

            // Add breakdown details if they exist
            if (isset($record['prev'])) {
                $consumption = $record['curr'] - $record['prev'];
                $billingData['previous_reading'] = $record['prev'];
                $billingData['current_reading'] = $record['curr'];
                $billingData['consumption'] = $consumption;
                $billingData['rate'] = $record['rate'];
                // You can add logic for other_fees here if needed
            }

            $billing = Billing::create($billingData);

            if ($record['status'] === 'paid') {
                Payment::create([
                    'billing_id' => $billing->id,
                    'amount_paid' => $record['amount'],
                    'payment_date' => Carbon::parse($record['paid_on']),
                    'penalty' => 0.00,
                    'discount' => 0.00,
                ]);
            }
        }
    }
}
