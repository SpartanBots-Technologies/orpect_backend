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
<body style="background: #f3f3f3;  display: flex; justify-content: center; align-items: center; height: 100vh; ">
<section>
    <table cellpadding="0" cellspacing="0" border="0" align="center" style="background: #fff;"   >
        <tr>
            <td style="max-width: 700px;">
                
                <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
                    <tr>
                        <td style="padding: 10px; height: 60px; background-color: #fff; text-align: center;  border-bottom: 1px solid #f4f4f4;">
                        <img src="https://orpect.com/static/media/orpect1.dfabd9d606236eba4ca2.png" width="200"/>
                        </td>
                    </tr>
                </table>
    
               
                <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
                    <tr>
                        <td style="padding: 20px; ">
                            <h2 style="text-align: center; font-size: 26px; font-weight: 600; margin-top: 0; color: #134d75;">Hello!</h2>
                          <h5 style="text-align: center; font-size: 20px; font-weight: 600; margin: 0; color: #134d75;">Congratulations! Your account has been successfully verified.</h5>
                       
                            <p>We appreciate your cooperation and patience. </p>
                            
                        <p style="line-height: 2rem;">If you have any questions or need assistance, please don't hesitate to reach out to our support team. We're here to help.</p>
                  
                    <p>Once again, welcome to  <a href="{{$data['websiteLink']}}" style="text-decoration: none; color: #134d75; font-weight: 600;">ORPECT</a>. Enjoy your experience with us! </p>
                    <p><a href="{{$data['websiteLogin']}}" style="text-decoration: none; color: #134d75; font-weight: 600;">Click here</a> to get redirected to ORPECT login page.</p>
       
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