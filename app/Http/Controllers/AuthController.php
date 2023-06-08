<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\PasswordReset;
use App\Models\EmailVerification;
use App\Models\Position;
use App\Models\SuperAdmin;

class AuthController extends Controller
{
    public function sendEmailVerificationOtp(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "email" => 'required|email|unique:users,email',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Please enter a valid email',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        $badDomains = [
            "gmail.com",
            "yopmail.com",
            "yahoo.com",
            "robot-mail.com",
            "maildrop.cc",
            "dispostable.com",
            "mailinator.com",
            "guerrillamail.com",
        ];
        
        $domain = substr(strrchr($request->email, "@"), 1); // Extract the domain from the email

        if (in_array($domain, $badDomains)) {
            return response()->json([
                'status' => false,
                'messsage' => "Please enter company email",
            ], 422);
        }
        $randOtp = random_int(100000, 999999);
        $useremail = $request->email;

        $data = [
            'userName' => $useremail,
            'CompanyName' => 'Orpect',
            'otp' => $randOtp,
        ];

        EmailVerification::updateOrCreate(
            ['email' => $request->email],
            [
                "email" => $request->email,
                "otp" => $randOtp
            ]
        );

        if( 
            Mail::send('auth.OtpEmail', ['data' => $data], function ($message) use ($useremail){
            $message->from('testspartanbots@gmail.com', 'Orpect');
            $message->to($useremail)->subject('ORPECT - Email Verification OTP'); }) 
        ){
            return response()->json([
                'status' => true,
                'messsage' => 'Otp sent successfully',
            ], 200);
        }
        return response()->json([
            'message' => 'Some error occured',
        ], 401);
    }

    public function checkOtp(Request $request){
        // dd($request->all());
        $inputValidation = Validator::make($request->all(), [
            "email" => 'required|email',
            "otp" => 'required',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data',
                'errors' => $inputValidation->errors(),
            ], 422);
        }

        $timeVerification = EmailVerification::select('created_at')->where('email', $request->email)->where('otp', $request->otp)->first();
        if($timeVerification){
            $to = Carbon::createFromFormat('Y-m-d H:i:s', $timeVerification->created_at);
            $from = Carbon::createFromFormat('Y-m-d H:i:s', now());
            $diff_in_minutes = $to->diffInMinutes($from);
            if($diff_in_minutes <= 10 ){
                return response()->json([
                    'status' => true,
                    'messsage' => 'Otp matched, Email verified',
                ], 200);
            }else{
                EmailVerification::where('email', $request->email)->where('otp', $request->otp)->delete();
                return response()->json([
                    'status' => false,
                    'message' => 'OTP Expired',
                ], 400);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP',
            ], 404);
        }
    }

    public function checkDomain(Request $request){
        if($request->domain != "" && !User::where('domain_name', $request->domain)->exists() ){
            $badDomains = [
                            "gmail.com",
                            "yopmail.com",
                            "yahoo.com",
                            "robot-mail.com",
                            "maildrop.cc",
                            "dispostable.com",
                            "mailinator.com",
                            "guerrillamail.com",
                        ];

            if (!in_array($request->domain, $badDomains)) {
                return response()->json([
                    "status" => true,
                    'message' => 'Correct Domain',
                ], 200);
            } else {
                return response()->json([
                    "status" => false,
                    'message' => 'Please enter your company domain name',
                ], 422);
            }
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Domain already exists',
            ], 422);
        }
    }

    public function register(Request $request){

        $inputValidation = Validator::make($request->all(), [
            "companyName" => 'required',
            "companyType" => 'required',
            "fullName" => 'required',
            "designation" => 'required',
            "domainName" => 'required',
            "email" => 'required|email|unique:users,email',
            "password" => 'required|min:6|confirmed',
            "otp" => 'required',
            'companyPhone' => 'required|regex:/^[0-9]{10}$/',
            "registrationNumber" => 'required',
            "companySocialLink" => $request->companySocialLink ? 'url' : '',
            "termsNconditions" => 'required',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        if( User::where('company_phone', $request->companyPhone)->exists() ){
            return response()->json([
                "status" => false,
                'message' => 'Phone number already exists. Please use another number',
            ], 422);
        }
        if( User::where('domain_name', $request->domainName)->exists() ){
            return response()->json([
                "status" => false,
                'message' => 'Domain already Exists',
            ], 422);
        }
        if( EmailVerification::where('email', $request->email)->where('otp', $request->otp)->exists() ){
            $timeVerification = EmailVerification::select('created_at')->where('email', $request->email)->where('otp', $request->otp)->first();
            if($timeVerification){
                $to = Carbon::createFromFormat('Y-m-d H:i:s', $timeVerification->created_at);
                $from = Carbon::createFromFormat('Y-m-d H:i:s', now());
                $diff_in_minutes = $to->diffInMinutes($from);
                EmailVerification::where('email', $request->email)->where('otp', $request->otp)->delete();
                if($diff_in_minutes > 10 ){
                    return response()->json([
                        'status' => false,
                        'message' => 'OTP Expired',
                    ], 400);
                }
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => "Invalid OTP",
            ], 422);
        }
        $positionArr = ['Developer', 'Designer', 'Tester'];
        $user = User::create([
            "company_name" => $request->companyName,
            "company_type" => $request->companyType,
            "full_name" => $request->fullName,
            "designation" => $request->designation,
            "domain_name" => $request->domainName,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "company_phone" => $request->companyPhone,
            "company_address" => $request->companyAddress ?? null,
            "company_city" => $request->companyCity ?? null,
            "company_state" => $request->companyState ?? null,
            "company_country" => $request->companyCountry ?? null,
            "company_postal_code" => $request->companyPostalCode ?? null,
            "registration_number" => $request->registrationNumber,
            "webmaster_email" => $request->companyWebmasterEmail ?? null,
            "company_social_link" => $request->companySocialLink ?? null,
            "terms_and_conditions" => $request->termsNconditions,
            "email_verified" => 1,
            "role" => 1
        ]);
        foreach($positionArr as $position){
            Position::create([
                "position" => $position,
                "added_by" => $user->id,
            ]);
        }
        if( $user ){
            return response()->json([
                'status' => true,
                'message' => "User successfully registered",
            ], 200);
        }
        return response()->json([
            'message' => 'Some error occured',
        ], 401);
    }

    public function login(Request $request){

        $inputValidation = Validator::make($request->all(), [
            "email" => 'required|email',
            "password" => 'required|min:6',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        if( Auth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ])){
            $user = Auth::user();
            $token = $user->createToken($user->email.'_api_token')->plainTextToken;
            return response()->json([
                'status' => true,
                'user' => $user,
                'token' => $token,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'messsage' => "Email and password do not match.",
            ], 401); 
        }
        return response()->json([
            'status' => false,
            'message' => 'Some error occured',
        ], 401);
    }

    public function logoutUser(){
        Auth::user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => "Logout successfully",
        ], 200);
    }

    public function forgotpassword(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "email" => 'required|email'
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Email invalid',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        $useremail = $request->email;
        if(User::where('email', '=', $useremail)->exists()){
            $uid =  Str::uuid()->toString();
            $domain =  config('services.react.domain');

            $data = [
                'userName' => $useremail,
                'CompanyName' => 'Orpect',
                'link' => $domain.'/reset-password?token='.$uid,
            ];

            Mail::send('auth.forgotPassEmailTemp', ['data' => $data], function ($message) use ($useremail){
                $message->from('testspartanbots@gmail.com', 'Orpect');
                $message->to($useremail)->subject('ORPECT - Password Reset');
            });

            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            PasswordReset::updateOrCreate(
                ['email' => $useremail],
                [
                    'email' => $useremail,
                    'token' => $uid,
                    'created_at' => $datetime
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Email sent successfully',
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Email id not registered',
            ], 401);
        }
        
    }

    public function resetPassword(Request $request){

        $inputValidation = Validator::make($request->all(), [
            "password" => 'required|confirmed',
            "token" => 'required'
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try{
            $email = DB::table('password_reset_tokens')
                    ->select('email')
                    ->where('token', $request->token)
                    ->value('email');
            if( $email ){

                $user = User::where('email',$email)->first();
                $user->fill([
                    "password" => Hash::make($request->password)
                ]);
                $user->save();

                $user->tokens()->delete();
                PasswordReset::where('email', $email)->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Password reset successfully'
                ], 200);

            } else{
                return response()->json([
                    'status' => false,
                    'message' => 'Some error occured please ask for reset link again',
                ], 401);
            }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function isTokenValid(Request $request){
        if($request->token != ''){
            $tokenExists = PasswordReset::select('created_at')->where('token', $request->token)->first();
            if($tokenExists){
                $to = Carbon::createFromFormat('Y-m-d H:i:s', $tokenExists->created_at);
                $from = Carbon::createFromFormat('Y-m-d H:i:s', now());
                $diff_in_minutes = $to->diffInMinutes($from);

                if($diff_in_minutes <= 10 ){
                    return response()->json([
                        'status' => true,
                        'message' => 'Token is valid',
                    ], 200);
                }else{
                    PasswordReset::where('token', $request->token)->delete();
                    return response()->json([
                        'status' => false,
                        'message' => 'Token not valid',
                    ], 400);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Token not found',
                ], 404);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token not found',
            ], 404);
        }
    }

    public function loginAdmin(Request $request){

        $inputValidation = Validator::make($request->all(), [
            "email" => 'required|email',
            "password" => 'required|min:6',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }

        $admin = SuperAdmin::where('email', $request->email)->first();
        if ( $admin && Hash::check($request->password, $admin->password)) {
            $token = $admin->createToken($request->email.'_api_token')->plainTextToken;
            return response()->json([
                'status' => true,
                'user' => $admin,
                'token' => $token,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'messsage' => "Email and password do not match.",
            ], 401); 
        }

        return response()->json([
            'status' => false,
            'message' => 'Some error occured',
        ], 401);
    }

    public function logoutAdmin(){
        Auth::guard('admin')->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => "Logout successfully",
        ], 200);
    }

    public function forgotPasswordAdmin(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "email" => 'required|email'
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Email invalid',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        $useremail = $request->email;
        if(SuperAdmin::where('email', '=', $useremail)->exists()){
            $uid =  Str::uuid()->toString();
            $domain =  config('services.react.domain');

            $data = [
                'userName' => $useremail,
                'CompanyName' => 'Orpect',
                'link' => $domain.'/admin/reset-password?token='.$uid,
            ];

            Mail::send('auth.forgotPassEmailTemp', ['data' => $data], function ($message) use ($useremail){
                $message->from('testspartanbots@gmail.com', 'Orpect');
                $message->to($useremail)->subject('ORPECT - Password Reset');
            });

            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            PasswordReset::updateOrCreate(
                ['email' => $useremail],
                [
                    'email' => $useremail,
                    'token' => $uid,
                    'created_at' => $datetime
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Email sent successfully',
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Email id not registered',
            ], 401);
        }
        
    }

    public function resetPasswordAdmin(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "password" => 'required|confirmed',
            "token" => 'required'
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid Data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }

        try{
            $email = DB::table('password_reset_tokens')
                    ->select('email')
                    ->where('token', $request->token)
                    ->value('email');
            if($email){

                $user = SuperAdmin::where('email',$email)->first();
                $user->update([
                    "password" => Hash::make($request->password)
                ]);

                $user->tokens()->delete();
                PasswordReset::where('email', $email)->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Password reset successfully'
                ], 200);

            } else{
                return response()->json([
                    'status' => false,
                    'message' => 'Some error occured please ask for reset link again',
                ], 401);
            }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
