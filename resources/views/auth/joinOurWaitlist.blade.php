<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['company']}}</title>
    <style>
       body{
        font-family: Roboto,sans-serif!important;
        margin: 0;
        padding: 0;
       }
    </style>
</head>
<body style="background: #f3f3f3;  display: flex; justify-content: center; align-items: center; height: 100vh; padding: 10px;">
<section>
    <table cellpadding="0" cellspacing="0" border="0" align="center" style="background: #fff;"   >
        <tr>
            <td style="max-width: 700px;">
                
                <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
                    <tr>
                        <td style="padding: 10px; height: 60px; background-color: #fff; text-align: center;  border-bottom: 1px solid #f4f4f4;">
                        <img src="https://orpect.com/static/media/orpect1.dfabd9d606236eba4ca2.png" alt="orpect.com" width="200"/>
                        </td>
                    </tr>
                </table>
    
               
                <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
                    <tr>
                        <td style="padding: 20px;">
                            <p><b>Name</b> : {{$data['name']}}</p>
                            <p><b>Company Name</b> : {{$data['companyName']}}</p>
                            <p><b>Email</b> : {{$data['email']}}</p>
                            <p><b>Phone</b> : {{$data['phone']}}</p>
                        </td>
                    </tr>
                </table>
    
                <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
                    <tr>
                        <td style="height: 40px;  text-align: center; background: #f6a21e; line-height: 2rem;">
                           <span  style="font-size: 15px; ">© COPYRIGHT 2023 <a href="{{$data['websiteLink']}}" style="color: rgb(19, 77, 117); text-decoration: none; font-weight: 600;">ORPECT LLC.</a> All Rights Reserved.</span> 
                        </td>
                      
                </table>
            </td>
        </tr>
    </table>
       
</section>
</body>
</html>