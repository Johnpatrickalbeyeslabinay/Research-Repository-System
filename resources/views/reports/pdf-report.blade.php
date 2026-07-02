<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab Files Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #222;
            margin: 24px;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .meta {
            margin-bottom: 14px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #777;
            padding: 8px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #efefef;
        }

        .print-note {
            margin-top: 14px;
            font-size: 12px;
            color: #555;
        }

        @media print {
            .print-note {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1>Lab Files Report</h1>
    <div class="meta">
        <div><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</div>
        <div><strong>Folder:</strong> {{ $selectedFolder ?: 'All folders' }}</div>
        <div><strong>Date Range:</strong> {{ $startDate ?: 'Any' }} to {{ $endDate ?: 'Any' }}</div>
        <div><strong>Total Records:</strong> {{ $reports->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Folder</th>
                <th>Auto Tag</th>
                <th>Uploaded By</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>{{ $report['title'] }}</td>
                    <td>{{ $report['folder'] }}</td>
                    <td>{{ $report['tag_or_summary'] }}</td>
                    <td>{{ $report['created_by'] }}</td>
                    <td>{{ $report['date'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No matching records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="print-note">
        Use your browser print dialog and choose "Save as PDF" to download.
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
