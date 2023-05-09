<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{$data['CompanyName']}}</title>
</head>
<body>
    <h3>Hello!</h3>
    <p>You are receiving this email because we received a email verification OTP request.</p>
    <br>
    <p>This is your OTP for email verification : {{$data['otp']}}</p>
    <br>
    <p>This will only be valid for 10 minutes.</p>
    <br>
    <p>If you did not request a OTP, no further action is required. And Please make sure to change your account's password.</p>

    <br>
    <p>Thank You</p>

</body>
</html>