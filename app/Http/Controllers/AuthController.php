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

class AuthController extends Controller
{
    public function sendEmailVerificationOtp(Request $request){
        // dd($request->all());
        $inputValidation = Validator::make($request->all(), [
            "email" => 'required|email|unique:users,email',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Email not found',
                'errors' => $inputValidation->errors(),
            ], 422);
        }

        $randOtp = random_int(100000, 999999);
        $useremail = $request->email;
        // $domain =  config('services.react.domain');

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
        // EmailVerification::create([
        //     "email" => $request->email,
        //     "otp" => $randOtp,
        // ]);

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
            "termsNconditions" => 'required',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
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
        if( User::where('domain_name', $request->domainName)->exists() ){
            return response()->json([
                "status" => false,
                'message' => 'Domain already Exists',
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
        // $resetData = PasswordReset::where('token', $request->token)->get();
        try{
            $email = DB::table('password_reset_tokens')
                    ->select('email')
                    ->where('token', $request->token)
                    ->value('email');
            if(isset($request->token) && $email){
                $inputValidation = Validator::make($request->all(), [
                    "password" => 'required|confirmed'
                ]);
                if($inputValidation->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'Password does not match',
                        'errors' => $inputValidation->errors(),
                    ], 422);
                }

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
}
