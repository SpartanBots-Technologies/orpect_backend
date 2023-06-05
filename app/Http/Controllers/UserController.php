<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Position;
use App\Models\Employee;

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
            "companyName" => 'required',
            "companyType" => 'required',
            "fullName" => 'required',
            "designation" => 'required',
            "companyPhone" => 'required|regex:/^[0-9]{10}$/',
            "registrationNumber" => 'required',
            "companySocialLink" => $request->companySocialLink ? 'url' : '',
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
            "company_phone" => $request->companyPhone,
            "company_address" => $request->companyAddress ?? null,
            "company_city" => $request->companyCity ?? null,
            "company_state" => $request->companyState ?? null,
            "company_country" => $request->companyCountry ?? null,
            "company_postal_code" => $request->companyPostalCode ?? null,
            "registration_number" => $request->registrationNumber,
            "webmaster_email" => $request->companyWebmasterEmail ?? null,
            "company_social_link" => $request->companySocialLink ?? null,
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

    public function updateUserImage(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "logoImage" => 'required|file|mimes:jpg,jpeg,png'
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'message' => 'Invalid image. Upload image of mime type jpg, jpeg & png',
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
        ]);
        if( $userUpdated ){
            return response()->json([
                'status' => true,
                'message' => "Image updated successfully",
                'newImage' => $image,
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
            $count = 0 ;
            $positionAlreadyExist = [];
            foreach($positions as $position) {
                if( !Position::where('position', trim($position))->where('added_by', Auth::user()->id)->exists() ){
                    Position::create([
                        "position" => trim($position),
                        "added_by" => Auth::user()->id,
                    ]);
                    $count++;
                }else{
                    $positionAlreadyExist[] = $position;
                }
            }
            if( count($positionAlreadyExist) ){
                return response()->json([
                    'status' => true,
                    'message' => $count . ($count == 1 ? " Position" : " Positions") . " saved. '" . implode(",", $positionAlreadyExist).  "' already exists.",
                ], 200);    
            }
            return response()->json([
                'status' => true,
                'message' => "Successfully added",
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => "Unable to add position. Please try again.",
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
            if( !Position::where('position', trim($request->position))->where('added_by', Auth::user()->id)->exists() ){
                $position->update([
                    "position" => trim($request->position)
                ]);
                return response()->json([
                    'status' => true,
                    'message' => "Successfully updated",
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Position already exists",
                ], 400);
            }
        
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

    public function getCompanies(){
        $allCompanies = User::where('is_deleted', 0)->where('is_account_verified', 0)->paginate(10);
        if($allCompanies){
            return response()->json([
                'status' => true,
                'allCompanies' => $allCompanies,
            ], 200);
        }else{
            return response()->json([ 'status' => false, 'message' => "No record Found", ], 404);
        }
    }

    public function getCompanyById(String $id){
        $company = User::where('id', $id)
                    ->where('is_deleted', 0)
                    ->first();
        if($company){
            return response()->json([
                'status' => true,
                'company' => $company,
            ], 200);
        }else{
            return response()->json([ 'status' => false, 'message' => "Company not found", ], 404);
        }
    }

    public function deleteCompany(String $id){
        $companydetails = User::find($id);
        if($companydetails){
            try{
                Employee::where('added_by', $id)->update([
                    'is_deleted' => 1,
                ]);
                $companydetails->update([
                    "is_deleted" => 1,
                ]);
                return response()->json([ 'status' => true, 'message' => "successfully deleted", ], 200);
            }catch(\Exception $e){
                dd($e);
            }
        }else{
            return response()->json([ 'status' => false, 'message' => "Some error occured", ], 400);
        }
    }
}
