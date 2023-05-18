<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Position;

class UserController extends Controller
{
    public function updateProfile(Request $request){
        $inputValidation = Validator::make($request->all(), [
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
        $userUpdated = $user->update([
            "company_name" => $request->companyName,
            "company_type" => $request->companyType,
            "full_name" => $request->fullName,
            "designation" => $request->designation,
        ]);
        if( $userUpdated ){
            return response()->json([
                'status' => true,
                'message' => "User successfully updated",
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
