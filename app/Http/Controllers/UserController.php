<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Position;

class UserController extends Controller
{
    public function getUser(){
        $currentUser = User::where('is_deleted', 0)
                    ->where('id', Auth::user()->id)
                    ->first();
        if($currentUser){
            return response()->json([
                'status' => true,
                'user' => $currentUser,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "User not found",
            ], 404);
        }
    }

    public function updateProfile(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "logoImage" => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
            "companyName" => 'required',
            "companyType" => 'required',
            "fullName" => 'required',
            "designation" => 'required',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        $user = User::find(Auth::user()->id);
        $oldLogoImage = $request->input('oldLogoImage', null);
        $image = null;
        if($request->hasFile('logoImage')){
            $randomNumber = random_int(1000, 9999);
            $file = $request->logoImage;
            $date = date('YmdHis');
            $filename = "LOGO_IMG_" . $randomNumber . "_" . $date;
            $extension = strtolower( $file->getClientOriginalExtension() );
            $imageName = $filename . '.' . $extension;
            $uploadPath = "uploads/users/logo_images/";
            $imageUrl = $uploadPath . $imageName;
            $file->move($uploadPath, $imageName);
            $image = $imageUrl;
            if($oldLogoImage != "" && File::exists($oldLogoImage)){
                File::delete($oldLogoImage);
            }
        }else{
            $image = $oldLogoImage;
        }
        $userUpdated = $user->update([
            "image" => $image,
            "company_name" => $request->companyName,
            "company_type" => $request->companyType,
            "full_name" => $request->fullName,
            "designation" => $request->designation,
        ]);
        if( $userUpdated ){
            $updatedUser = User::find($user->id);
            return response()->json([
                'status' => true,
                'message' => "User successfully updated",
                'user' => $updatedUser,
            ], 200);
        }
        return response()->json([
            'message' => 'Some error occured',
        ], 401);
    }

    public function updateUserPassword(Request $request){
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
            $user = User::find( Auth::user()->id );
            if(Hash::check($request->oldPassword, $user->password)){
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

    public function addPositions(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "positions" => 'required',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try {
            $positions = explode(",", $request->positions);
            foreach($positions as $position) {
                Position::create([
                    "position" => trim($position),
                    "added_by" => Auth::user()->id,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => "Successfully added",
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e,
            ], 400);
        }
    }

    public function getPositions(){
        $positions = Position::select('id', 'position')
                    ->where('added_by', Auth::user()->id)
                    ->get();
        return response()->json([
            'status' => true,
            'positions' => $positions,
        ], 200);
    }

    public function updatePosition(Request $request, String $id){
        $inputValidation = Validator::make($request->all(), [
            "position" => 'required',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
            ], 422);
        }
        $position = Position::where('id', $id)->first();
        if($position){
            $position->update([
                "position" => $request->position
            ]);
            return response()->json([
                'status' => true,
                'message' => "Successfully updated",
            ], 200);
        
        }else{
            return response()->json([
                'status' => false,
                'message' => "Unable to update position",
            ], 400);
        }


    }

    public function removePosition(String $id){
        $positionDeleted = Position::where('id', $id)->delete();
        if($positionDeleted){
            return response()->json([
                'status' => true,
                'message' => "successfully deleted",
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "Some error occured",
            ], 400);
        }
    }
}
