<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Request</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f6f8;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            max-width: 500px;
            margin: 80px auto;
            padding: 35px;
            background: #ffffff;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        h2 {
            margin-bottom: 10px;
            color: #072a89;
        }

        p {
            color: #555;
            line-height: 1.6;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="icon">✓</div>

        <h2>Access Request Updated</h2>

        <p>
            {{ $message }}
        </p>

    </div>

</body>
</html>