<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login with OTP</h1>

    <form action="{{ route('generate.otp') }}" method="POST">
        @csrf
        <label for="mobile">Mobile Number:</label>
        <input type="text" name="mobile" id="mobile" required>
        <button type="submit">Generate OTP</button>
    </form>

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
</body>
</html>
