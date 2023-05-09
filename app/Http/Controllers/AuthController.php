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
use App\Models\EmailVerification;

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
                EmailVerification::select('created_at')->where('email', $request->email)->where('otp', $request->otp)->delete();
                return response()->json([
                    'status' => false,
                    'message' => 'OTP Expired',
                ], 400);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'OTP Expired',
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
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }		

        User::create([
            "company_name" => $request->companyName,
            "company_type" => $request->companyType,
            "full_name" => $request->fullName,
            "designation" => $request->designation,
            "domain_name" => $request->domainName,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "email_verified" => 1,
            "role" => 1
        ]);

        if( Auth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ])){
            $user = Auth::user();
            $token = $user->createToken($user->email.'_api_token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token,
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
                'user' => $user,
                'token' => $token,
            ], 200);
        }
        return response()->json([
            'message' => 'Some error occured',
        ], 401);
    }
}
