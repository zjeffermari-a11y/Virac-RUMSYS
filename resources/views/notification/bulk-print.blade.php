<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bulk Billing Notification</title>
    <style>
        @page {
            size: 8.5in 5.5in landscape; /* Half coupon bond, landscape */
            margin: 0.5in;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
        }
        .container {
            width: 100%;
            height: 100%;
            border: 1px solid black;
            padding: 20px;
            box-sizing: border-box;
            page-break-after: always;
        }
        .container:last-child {
            page-break-after: auto;
        }
        h1 {
            text-align: center;
            margin-top: 0;
        }
        p {
            line-height: 1.6;
        }
        .details {
            margin-top: 30px;
        }
        .details strong {
            display: inline-block;
            width: 150px;
        }
    </style>
</head>
<body>
    @foreach ($users as $user)
        @php
            $bills = $user->billings;
            $stall = $user->stall;
        @endphp
        <div class="container">
            <h1>BILLING NOTIFICATION</h1>
            <p>
                Dear <strong>{{ $user->name }}</strong>,
            </p>
            <p>
                @php
                    $rentAmount = $bills->where('utility_type', 'Rent')->sum('amount');
                    $waterAmount = $bills->where('utility_type', 'Water')->sum('amount');
                    $electricityAmount = $bills->where('utility_type', 'Electricity')->sum('amount');
                    $totalDue = $bills->sum('amount');
                    $dueDate = $bills->isNotEmpty() ? $bills->first()->due_date->format('F d, Y') : 'N/A';

                    $message = str_replace(
                        ['{{rent_amount}}', '{{water_amount}}', '{{electricity_amount}}', '{{total_due}}', '{{due_date}}'],
                        [number_format($rentAmount, 2), number_format($waterAmount, 2), number_format($electricityAmount, 2), number_format($totalDue, 2), $dueDate],
                        $user->template
                    );
                @endphp
                {{ $message }}
            </p>

            @if ($bills->isNotEmpty())
            <div class="details">
                <p><strong>Stall Number:</strong> {{ $stall->table_number ?? 'N/A' }}</p>
                @foreach ($bills as $bill)
                    <p><strong>Bill Type:</strong> {{ $bill->utility_type }}</p>
                    <p><strong>Billing Period:</strong> {{ $bill->period_start->format('M d, Y') }} - {{ $bill->period_end->format('M d, Y') }}</p>
                    <p><strong>Amount Due:</strong> <strong>₱{{ number_format($bill->amount, 2) }}</strong></p>
                @endforeach
                <p><strong>Total Amount Due:</strong> <strong>₱{{ number_format($totalDue, 2) }}</strong></p>
                <p><strong>Due Date:</strong> {{ $dueDate }}</p>
            </div>
            @endif

            <p style="margin-top: 40px;">
                Please settle this amount on or before the due date to avoid penalties and disconnection.
            </p>
            <p>Thank you.</p>
        </div>
    @endforeach
    <script>
        // Automatically trigger the print dialog when the page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>