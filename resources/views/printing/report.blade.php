<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Monthly Report</title>
    {{-- Inline the compiled JavaScript for PDF generation --}}
    @if (!empty($chartJsContent))
        <script>
            {!! $chartJsContent !!}
        </script>
    @endif
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .report-container {
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 22px;
            margin: 0;
        }

        .header p {
            font-size: 13px;
            color: #666;
            margin: 5px 0 0;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #333;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f7f7f7;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .kpi-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }

        .kpi-label {
            font-size: 11px;
            color: #666;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: bold;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .chart-container {
            height: 180px;
        }

        .notes-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .notes-content {
            border: 1px solid #ddd;
            padding: 10px;
            min-height: 50px;
            background-color: #f9f9f9;
            border-radius: 5px;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="report-container">
        <div class="header">
            <h1>Monthly Operations Report</h1>
            <p>For the period of {{ $data['report_period'] }}</p>
        </div>

        <div class="section-title">Summary Overview</div>
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total Collections</div>
                <div class="kpi-value">₱{{ number_format($data['kpis']['total_collection'], 2) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Delinquent Vendors</div>
                <div class="kpi-value">{{ $data['kpis']['delinquent_vendors_count'] }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">New Vendors</div>
                <div class="kpi-value">{{ $data['kpis']['new_vendors'] }}</div>
            </div>
        </div>

        <div class="section-title">Monthly Collection Trends</div>
        <div class="chart-grid">
            <div class="chart-container"><canvas id="rentChart"></canvas></div>
            <div class="chart-container"><canvas id="electricityChart"></canvas></div>
            <div class="chart-container"><canvas id="waterChart"></canvas></div>
        </div>

        <div class="section-title">Delinquent Vendors</div>
        <table>
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th class="text-right">Amount Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['delinquent_vendors'] as $vendor)
                    <tr>
                        <td>{{ $vendor->name }} (Stall: {{ $vendor->stall->table_number }})</td>
                        <td class="text-right">₱{{ number_format($vendor->total_due, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No delinquent vendors for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if (!empty($notes))
            <div class="notes-section">
                <div class="section-title">Notes/Comments</div>
                <div class="notes-content">{{ $notes }}</div>
            </div>
        @endif
    </div>

    <script>
        // This script will run inside the headless browser to generate the charts
        document.addEventListener('DOMContentLoaded', function() {
            // Debug: Check if Chart.js is loaded
            console.log('Chart.js loaded:', typeof Chart !== 'undefined');
            console.log('Chart object:', typeof Chart);

            // Ensure Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            const chartData = @json($data['chart_data']);
            const utilityColors = {
                Rent: {
                    paid: 'rgba(79, 70, 229, 1)',
                    unpaid: 'rgba(79, 70, 229, 0.5)'
                },
                Electricity: {
                    paid: 'rgba(245, 158, 11, 1)',
                    unpaid: 'rgba(245, 158, 11, 0.5)'
                },
                Water: {
                    paid: 'rgba(59, 130, 246, 1)',
                    unpaid: 'rgba(59, 130, 246, 0.5)'
                }
            };

            const createBarChart = (canvasId, title, data, colors) => {
                const ctx = document.getElementById(canvasId)?.getContext('2d');
                if (!ctx) return;

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Paid', 'Unpaid'],
                        datasets: [{
                            label: title,
                            data: [parseFloat(data.paid) || 0, parseFloat(data.unpaid) || 0],
                            backgroundColor: [colors.paid, colors.unpaid],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: title,
                                font: {
                                    size: 14
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => '₱' + new Intl.NumberFormat('en-US').format(
                                        value)
                                }
                            }
                        }
                    }
                });
            };

            const utilities = ['Rent', 'Electricity', 'Water'];
            utilities.forEach(util => {
                const data = chartData.by_utility[util] || {
                    paid: 0,
                    unpaid: 0
                };
                createBarChart(`${util.toLowerCase()}Chart`, `${util} Collections`, data, utilityColors[
                    util]);
            });
            
            // Signal that all charts have been rendered (for PDF generation)
            window.chartsRendered = true;
            console.log('Charts rendered, window.chartsRendered =', window.chartsRendered);
        });
    </script>
</body>

</html>
