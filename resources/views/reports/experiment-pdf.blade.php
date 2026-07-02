<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Experiment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #222;
            margin: 24px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 22px;
        }

        .meta {
            margin-bottom: 18px;
            font-size: 13px;
        }

        .experiment {
            border: 1px solid #777;
            margin-bottom: 14px;
            padding: 10px;
            page-break-inside: avoid;
        }

        .experiment h2 {
            margin: 0 0 8px;
            font-size: 16px;
        }

        .row {
            margin-bottom: 6px;
            font-size: 12px;
        }

        .label {
            font-weight: 700;
        }

        .print-note {
            margin-top: 10px;
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
    <h1>Experiment Report</h1>
    <div class="meta">
        <div><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</div>
        <div><strong>Total Experiments:</strong> {{ $experiments->count() }}</div>
    </div>

    @forelse($experiments as $experiment)
        <div class="experiment">
            <h2>{{ $experiment->title }}</h2>
            <div class="row"><span class="label">Date:</span> {{ optional($experiment->experiment_date)->format('F d, Y') ?? 'N/A' }}</div>
            <div class="row"><span class="label">Created By:</span> {{ trim(($experiment->user->first_name ?? '') . ' ' . ($experiment->user->last_name ?? '')) ?: 'Unknown' }}</div>
            <div class="row"><span class="label">Objective:</span> {{ $experiment->objective }}</div>
            <div class="row"><span class="label">Materials:</span> {{ $experiment->materials ?: 'N/A' }}</div>
            <div class="row"><span class="label">Procedure:</span> {{ $experiment->procedure }}</div>
            <div class="row"><span class="label">Observations:</span> {{ $experiment->observations ?: 'N/A' }}</div>
            <div class="row"><span class="label">Result Summary:</span> {{ $experiment->result_summary ?: 'N/A' }}</div>
        </div>
    @empty
        <p>No experiments available.</p>
    @endforelse

    <div class="print-note">
        Use your browser print dialog and select "Save as PDF".
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
