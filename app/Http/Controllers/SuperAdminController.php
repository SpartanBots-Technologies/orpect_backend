<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

use App\Models\User;
use App\Models\SuperAdmin;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{

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

    public function getAllAdmins(){
        $allAdmins = SuperAdmin::select(
                                    'id',
                                    'fullname',
                                    'phone',
                                    'email',
                                    'image',
                                    'address',
                                    'city',
                                    'state',
                                    'country',
                                    'postal_code',
                                )
                    ->where('is_deleted', 0)
                    ->where('is_master', 0)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
        if($allAdmins){
            return response()->json([
                'status' => true,
                'allAdmins' => $allAdmins,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "No record Found",
            ], 404);
        }
    }

    public function getAdminById(String $id){
        $admin = SuperAdmin::select('id',
                                    'fullname',
                                    'phone',
                                    'email',
                                    'image',
                                    'address',
                                    'city',
                                    'state',
                                    'country',
                                    'postal_code',
                                    )
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();
        if($admin){
            return response()->json([
                'status' => true,
                'admin' => $admin,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "No record Found",
            ], 404);
        }
    }

}
