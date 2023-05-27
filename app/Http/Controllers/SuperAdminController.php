<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Session;

use App\Models\User;
use App\Models\PasswordReset;
use App\Models\EmailVerification;
use App\Models\Position;
use App\Models\SuperAdmin;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
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
            $tokenExists = PasswordReset::select('created_at')->where('token', $request->token)->first();
            if($tokenExists){
                $to = Carbon::createFromFormat('Y-m-d H:i:s', $tokenExists->created_at);
                $from = Carbon::createFromFormat('Y-m-d H:i:s', now());
                $diff_in_minutes = $to->diffInMinutes($from);
                if($diff_in_minutes > 10 ){
                    PasswordReset::where('token', $request->token)->delete();
                    return response()->json([
                        'status' => false,
                        'message' => 'Token Expired',
                    ], 400);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Token not found',
                ], 404);
            }
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

    public function addAdmin(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "fullname" => 'required',
            "email" => 'required|email|unique:super_admins,email',
            "password" => 'required|confirmed',
            "phone" => 'required|regex:/^[0-9]{10}$/|unique:super_admins,phone',
            "address" => 'required',
            "city" => 'required',
            "state" => 'required',
            "country" => 'required',
            "postalCode" => 'required',
            'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try{
            $image = null;

            if($request->hasFile('image')){
                $randomNumber = random_int(1000, 9999);
                $file = $request->image;
                $date = date('YmdHis');
                $filename = "IMG_" . $randomNumber . "_" . $date;
                $extension = strtolower( $file->getClientOriginalExtension() );
                $imageName = $filename . '.' . $extension;
                $uploadPath = "uploads/admins/profile_images/";
                $imageUrl = $uploadPath . $imageName;
                $file->move($uploadPath, $imageName);
                $image = $imageUrl;
            }

            $adminCreated = SuperAdmin::create([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'image' => $image,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'postal_code' => $request->postalCode,
                'is_master' => 0,
            ]);
            if($adminCreated){
                return response()->json([
                    'status' => true,
                    'message' => "saved successfully",
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => "some error occured",
            ], 400);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateAdmin(Request $request, String $id){
        $inputValidation = Validator::make($request->all(), [
            "fullname" => 'required',
            "email" => 'required|email',
            "phone" => 'required|regex:/^[0-9]{10}$/',
            "address" => 'required',
            "city" => 'required',
            "state" => 'required',
            "country" => 'required',
            "postalCode" => 'required',
            'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        if( ( $request->email != SuperAdmin::where('id', $id)->value('email') ) && 
            SuperAdmin::where('email', $request->email)->exists()){
                return response()->json([
                    'status' => false, 'message' => 'Email already exists',
                ], 422);
        }
        if( ( $request->phone != SuperAdmin::where('id', $id)->value('phone') ) && 
            SuperAdmin::where('phone', $request->phone)->exists()){
                return response()->json([
                    'status' => false, 'message' => 'phone already exists',
                ], 422);
        }
        try{
            $adminDetails = SuperAdmin::where('id', $id)->first();
            $image = null;
            $oldImage = $request->oldImageName;

            if($request->hasFile('image')){
                $randomNumber = random_int(1000, 9999);
                $file = $request->image;
                $date = date('YmdHis');
                $filename = "IMG_" . $randomNumber . "_" . $date;
                $extension = strtolower( $file->getClientOriginalExtension() );
                $imageName = $filename . '.' . $extension;
                $uploadPath = "uploads/admins/profile_images/";
                $imageUrl = $uploadPath . $imageName;
                $file->move($uploadPath, $imageName);
                $image = $imageUrl;
                if($oldImage != "" && File::exists($oldImage)){
                    File::delete($oldImage);
                }
            }else{
                $image = $oldImage;
            }

            $adminCreated = $adminDetails->update([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'image' => $image,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'postal_code' => $request->postalCode,
            ]);
            if($adminCreated){
                return response()->json([
                    'status' => true,
                    'message' => "updated successfully",
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => "some error occured",
            ], 400);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateAdminPassword(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "oldPassword" => 'required',
            "newPassword" => 'required|confirmed'
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try{
            $user = User::select('password')->find( Auth::guard('admin')->user()->id );
            if( Hash::check($request->oldPassword, $user->password) ){
                $user->update([
                    'password' => Hash::make($request->newPassword)
                ]);
                return response()->json([
                    'status' => true,
                    'message' => "Password updated successfully",
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Old Password does not match",
                ], 400);
            }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getCompanies(){
        $allCompanies = User::where('is_deleted', 0)->where('is_account_verified', 0)->paginate(10);
        if($allCompanies){
            return response()->json([
                'status' => true,
                'allCompanies' => $allCompanies,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "No record Found",
            ], 404);

        }
    }

}
