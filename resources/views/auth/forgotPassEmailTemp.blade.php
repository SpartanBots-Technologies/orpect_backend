<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['CompanyName']}}</title>
    <style>
       body{
        font-family: Roboto,sans-serif!important;
        margin: 0;
        padding: 0;
       }
    </style>
</head>
<body style="background: #f3f3f3;  display: flex; justify-content: center; align-items: center; height: 100vh;  ">
<section>
    <table cellpadding="0" cellspacing="0" border="0" align="center" style="background: #fff;"   >
        <tr>
            <td style="max-width: 700px;">
                
                <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
                    <tr>
                        <td style="padding: 10px; height: 60px;  background-color: #fff; text-align: center;  border-bottom: 1px solid #f4f4f4;">
                        <img src="https://orpect.com/static/media/orpect1.dfabd9d606236eba4ca2.png" width="200"/>
                        </td>
                    </tr>
                </table>
    
               
                <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
                    <tr>
                        <td style="padding: 20px; ">
                            <h2 style="text-align: center; font-size: 26px; font-weight: 600; margin-top: 0; color: #134d75;">Hello!</h2>
                            <p> You are receiving this email because we received a password reset request.</p>
                       
                            <p>This is your link for password reset :</p>
                            <p style="text-align: center; font-size: 20px; background: #134d75; color: #fff; padding: 8px 20px; font-size: 20px; border-radius: 5px; width: 100px;  margin: 10px auto;"><a href="{{$data['link']}}" target="_blank" style="text-decoration: none; color: #fff;">Click here</a> </p>
                        
                        <p>This link will only be valid for 10 minutes.</p>
                  
                    <p style="line-height: 2rem;">If you did not request a Password Reset link, no further action is required. And Please make sure to change your account's password.                    </p>
       
                <p  > Thank You </p>
                <p  > Team <b>ORPECT</b> </p>
                        </td>
                    </tr>
                </table>
    
           
                <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
                    <tr>
                        <td style="height: 40px;  text-align: center; background: #f6a21e; line-height: 2rem;">
                          <!-- Need More Help? <br/> -->
                          <!-- <a href="mailto:support@orpect.com" style="color: #134d75; font-weight: 600; text-decoration: none ; ">We're Here Ready to talk</a> -->
                        
                           <span  style="font-size: 15px; ">© COPYRIGHT 2023 <a href="{{$data['websiteLink']}}" style="color: rgb(19, 77, 117); text-decoration: none; font-weight: 600;">ORPECT LLC.</a> All Rights Reserved.</span> 
 
                        </td>
                      
                </table>
            </td>
        </tr>
    </table>
       
</section>
</body>
</html>
