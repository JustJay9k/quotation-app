<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoices Export</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }

        .header {
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .meta {
            color: #475569;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-weight: 700;
        }

        .amount {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Invoices</h1>
        <p class="meta">Generated: {{ $generatedAt->format('Y-m-d H:i') }}</p>
        <p class="meta">Total records: {{ $rows->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice #</th>
                <th>Customer Name</th>
                <th>Status</th>
                <th>Due Date</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['number'] }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['due'] }}</td>
                    <td class="amount">{{ number_format($row['amount'] ?? 0, 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No invoices found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
