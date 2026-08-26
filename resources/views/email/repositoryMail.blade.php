<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Research Repository System</title>
</head>

<body>

    <h2>Research Repository System</h2>

    <p>Hello {{ $name }},</p>

    <p>Your account has been created.</p>

    <p>
        <strong>User ID:</strong> {{ $user_id }}
    </p>

    <p>
        <strong>Email:</strong> {{ $email }}
    </p>

    <p>
        <strong>Password:</strong> {{ $password }}
    </p>

    <p>
        <strong>Role:</strong> {{ ucfirst($role) }}
    </p>

    <p>
        You can now use these credentials to log in to the system.
    </p>

</body>
</html>
