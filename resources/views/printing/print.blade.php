<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        @if (count($users) > 1)
            Bulk Billing Statements
        @else
            Billing Statement for {{ $users->first()->name }}
        @endif
    </title>
    <style>
        @if (count($users) > 1)
            /* Bulk Printing - 2 statements per A4 landscape */
            @page {
                size: A4 landscape;
                margin: 0.4in;
            }

            * {
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 8pt;
                color: #000;
                margin: 0;
                padding: 0;
            }

            .page-container {
                display: flex;
                gap: 20px;
                page-break-after: always;
                width: 100%;
                height: calc(8.27in - 1.2in);
            }

            .page-container:last-child {
                page-break-after: auto;
            }

            .statement-container {
                flex: 1;
                border: 2px solid #000;
                padding: 12px;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
        @else
            /* Individual Printing - 1 statement per A4 portrait */
            @page {
                size: A4 portrait;
                margin: 0.5in;
            }

            * {
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 11pt;
                color: #000;
                margin: 0;
                padding: 0;
            }

            .page-container {
                page-break-after: always;
                width: 100%;
                min-height: calc(11.69in - 1in);
            }

            .page-container:last-child {
                page-break-after: auto;
            }

            .statement-container {
                border: 2px solid #000;
                padding: 25px;
                display: flex;
                flex-direction: column;
                min-height: calc(11.69in - 1in);
            }
        @endif

        @if (count($users) > 1)
            /* Bulk Printing Styles - Compact */
            .header {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 10px;
            }

            .header img {
                width: 35px;
                height: auto;
            }

            .header h1 {
                margin: 0;
                font-size: 10pt;
            }

            .vendor-info {
                margin-bottom: 10px;
                line-height: 1.3;
                font-size: 7.5pt;
            }

            .statement-title {
                text-align: center;
                font-weight: bold;
                font-size: 9pt;
                margin-bottom: 2px;
                text-transform: uppercase;
            }

            .statement-month {
                text-align: center;
                margin-bottom: 8px;
                font-size: 7.5pt;
            }

            th,
            td {
                padding: 4px;
                font-size: 7pt;
            }

            thead th {
                font-size: 7pt;
                padding-bottom: 6px;
            }

            tfoot tr td {
                font-size: 8pt;
                padding-top: 6px;
            }

            .footer-note {
                margin-top: auto;
                padding-top: 8px;
                font-size: 6pt;
                line-height: 1.2;
            }

            .footer-note p {
                margin: 2px 0;
            }
        @else
            /* Individual Printing Styles - Larger */
            .header {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 20px;
            }

            .header img {
                width: 60px;
                height: auto;
            }

            .header h1 {
                margin: 0;
                font-size: 16pt;
            }

            .vendor-info {
                margin-bottom: 25px;
                line-height: 1.6;
                font-size: 11pt;
            }

            .statement-title {
                text-align: center;
                font-weight: bold;
                font-size: 14pt;
                margin-bottom: 5px;
                text-transform: uppercase;
            }

            .statement-month {
                text-align: center;
                margin-bottom: 20px;
                font-size: 11pt;
            }

            th,
            td {
                padding: 8px;
                font-size: 10pt;
            }

            thead th {
                font-size: 10pt;
                padding-bottom: 10px;
            }

            tfoot tr td {
                font-size: 12pt;
                padding-top: 12px;
            }

            .footer-note {
                margin-top: auto;
                padding-top: 20px;
                font-size: 9pt;
                line-height: 1.4;
            }

            .footer-note p {
                margin: 5px 0;
            }
        @endif

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        thead th {
            border-bottom: 2px solid #000;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        tfoot tr td {
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: none;
        }
    </style>
</head>

<body>
    {{-- Group users into pairs (2 per page) --}}
    @foreach ($users->chunk(2) as $userPair)
        <div class="page-container">
            @foreach ($userPair as $user)
                <div class="statement-container">
                    <div class="header">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Logo">
                        <h1>Virac Public Market</h1>
                    </div>

                    <div class="vendor-info">
                        <div><strong>Name of Vendor:</strong> {{ $user->name }}</div>
                        <div><strong>Market Section:</strong> {{ $user->stall->section->name ?? 'N/A' }}</div>
                        <div><strong>Stall/Table No.:</strong> {{ $user->stall->table_number ?? 'N/A' }}</div>
                    </div>

                    <div class="statement-title">Billing Statement</div>
                    <div class="statement-month">For the Month of {{ $user->statementMonth }}</div>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 18%;">CATEGORY</th>
                                <th style="width: 25%;">PERIOD COVERED</th>
                                <th class="text-right" style="width: 15%;">AMOUNT DUE</th>
                                <th>DUE DATE</th>
                                <th class="text-right" style="width: 15%;">AMOUNT AFTER DUE</th>
                                <th>DISCONNECTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->currentBills as $bill)
                                <tr>
                                    <td>{{ $bill->utility_type }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bill->period_start)->format('M d') }} -
                                        {{ \Carbon\Carbon::parse($bill->period_end)->format('M d, Y') }}</td>
                                    <td class="text-right">₱{{ number_format($bill->current_amount_due, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}</td>
                                    <td class="text-right">₱{{ number_format($bill->amount_after_due, 2) }}</td>
                                    <td>{{ $bill->disconnection_date ? \Carbon\Carbon::parse($bill->disconnection_date)->format('M d, Y') : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 20px;">No charges for this month.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><strong>Total Amount Due on
                                        {{ \Carbon\Carbon::parse($user->dueDate)->format('F Y') }}</strong></td>
                                <td class="text-right"><strong>₱{{ number_format($user->totalAmountDue, 2) }}</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="footer-note">
                        <p><em>*You may pay your bill upon receipt at office of the market supervisor</em></p>
                        <p><strong>NOTE: THIS SERVES AS DISCONNECTION NOTICE</strong></p>
                        <p><strong>PAISI:</strong> An dai pagbayad kan saindong bill nangangahulugan nin temporaryong pagkaputol
                            kan saindong serbisyo nin kuryente. Kung nakabayad na, ipahiling sa samuyang diskonektor an opisyal
                            na resibo.</p>
                        <p><strong>DAI TABI PAG WALAON INING BILL ASIN ANG SAINDONG MGA RESIBO. MABALOS!</strong></p>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <script type="text/javascript">
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
