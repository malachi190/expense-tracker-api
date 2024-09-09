<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email verification</title>
</head>

<body>
    <p>Hello, {{ $user->name }}</p>

    <p>Your OTP code for verifying your email address is:</p>
    <h2>{{ $user->otp }}</h2>

    <p>This code is valid for the next 10 minutes.</p>

    <p>Thanks,</p>
    <p>Expense Tracker</p>
</body>

</html>
