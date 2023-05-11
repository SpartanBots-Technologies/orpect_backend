<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{$data['CompanyName']}}</title>
</head>
<body>
    <h3>Hello!</h3>
    <p>You are receiving this email because we received a password reset request.</p>
    <br>
    <p>This is your link for password reset : <a href="{{$data['link']}}" target="_blank">Click here</a></p>
    <br>
    <p>This link will only be valid for 10 minutes.</p>
    <br>
    <p>If you did not request a Password Reset link, no further action is required. And Please make sure to change your account's password.</p>

    <br>
    <p>Thank You</p>

</body>
</html>