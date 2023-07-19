<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\SuperAdmin;
use App\Models\Employee;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{

    public function getAdmin(){
        $currentAdmin = SuperAdmin::where('is_deleted', 0)
                    ->where('id', Auth::guard('admin')->user()->id)
                    ->first();
        if($currentAdmin){
            return response()->json([
                'status' => true,
                'admin' => $currentAdmin,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "Admin not found",
            ], 404);
        }
    }

    public function updateProfileAdmin(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "fullname" => 'required',
            "phone" => 'required|regex:/^[0-9]{10}$/',
            'image' => $request->image ? 'file|mimes:jpg,jpeg,png|max:2048' : '',
            'email' => 'required|email:filter',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        $id = Auth::guard('admin')->user()->id;
        if(Auth::guard('admin')->user()->is_master == 1){
            if($request->email != Auth::guard('admin')->user()->email
                && SuperAdmin::where('email', $request->email)->exists()){
                    return response()->json([
                        'status' => false,
                        'message' => 'Email id already exists.',
                    ], 422);
            }
            $inputEmail = $request->email;
        }else{
            $inputEmail = Auth::guard('admin')->user()->email;
        }
        if( ( $request->phone != Auth::guard('admin')->user()->phone ) && 
            SuperAdmin::where('phone', $request->phone)->exists()){
                return response()->json([
                    'status' => false, 'message' => 'Phone number already exists',
                ], 422);
        }
        try{
            $adminDetails = SuperAdmin::where('id', $id)->first();
            $image = null;
            $oldImage = $request->oldImageName != "" ? $request->oldImageName : null;

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

            $adminUpdated = $adminDetails->update([
                'fullname' => $request->fullname,
                'email' => $inputEmail,
                'phone' => $request->phone,
                'image' => $image,
                'address' => $request->address != "" ? $request->address : null,
                'city' => $request->city != "" ? $request->city : null,
                'country' => $request->country != "" ? $request->country : null,
                'state' => $request->state != "" ? $request->state : null,
                'postal_code' => $request->postalCode != "" ? $request->postalCode : null,
            ]);
            if($adminUpdated){
                $updatedAdmin = SuperAdmin::find($id);
                return response()->json([
                    'status' => true,
                    'message' => "updated successfully",
                    'updatedAdmin' => $updatedAdmin,
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => "some error occured",
            ], 400);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => "Some exception occured",
            ], 400);
        }
    }

    public function addAdmin(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "fullname" => 'required',
            "email" => 'required|email:filter',
            "password" => 'required|confirmed',
            "phone" => 'required|regex:/^[0-9]{10}$/',
            'image' => $request->image ? 'file|mimes:jpg,jpeg,png|max:2048' : '',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        if( SuperAdmin::where('email', $request->email)->exists() ){
                return response()->json([
                    'status' => false, 'message' => 'Email already exists',
                ], 422);
        }
        if( SuperAdmin::where('phone', $request->phone)->exists() ){
                return response()->json([
                    'status' => false, 'message' => 'Phone already exists',
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
                'address' => $request->address != "" ? $request->address : null,
                'city' => $request->city != "" ? $request->city : null,
                'country' => $request->country != "" ? $request->country : null,
                'state' => $request->state != "" ? $request->state : null,
                'postal_code' => $request->postalCode != "" ? $request->postalCode : null,
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
                'message' => "Some exception occured",
            ], 400);
        }
    }

    public function updateSubAdmin(Request $request, String $id){
        $inputValidation = Validator::make($request->all(), [
            "fullname" => 'required',
            "email" => 'required|email',
            "phone" => 'required|regex:/^[0-9]{10}$/',
            'image' => $request->image ? 'file|mimes:jpg,jpeg,png|max:2048' : '',
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
                    'status' => false, 'message' => 'Phone already exists',
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
                'address' => $request->address != "" ? $request->address : null,
                'city' => $request->city != "" ? $request->city : null,
                'country' => $request->country != "" ? $request->country : null,
                'state' => $request->state != "" ? $request->state : null,
                'postal_code' => $request->postalCode != "" ? $request->postalCode : null,
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
                'message' => "Some exception occured",
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
        if($request->oldPassword == $request->newPassword){
            return response()->json([ 'status' => false, 'message' => "Old and New passwords are same. Please enter different password", ], 422);
        }
        try{
            $admin = SuperAdmin::find( Auth::guard('admin')->user()->id );
            if( $admin && Hash::check($request->oldPassword, $admin->password) ){
                $admin->update([
                    'password' => Hash::make($request->newPassword)
                ]);
                // Get the current token being used for authentication
                $currentToken = Auth::guard('admin')->user()->currentAccessToken();
                // Remove all tokens except the current one
                $admin->tokens->each(function ($token) use ($currentToken) {
                    if ($token->id !== $currentToken->id) {
                        $token->delete();
                    }
                });
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
                'message' => "Some exception occured",
            ], 400);
        }
    }

    public function updateSubAdminPassword(Request $request, String $id){
        $inputValidation = Validator::make($request->all(), [
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
            $adminIsMaster = SuperAdmin::find( Auth::guard('admin')->user()->id )->value('is_master');
            if($adminIsMaster){
                $subAdmin = SuperAdmin::find( $id );
                if( $subAdmin ){
                    if(Hash::check($request->newPassword, $subAdmin->password)){
                        return response()->json([ 'status' => false, 'message' => "Old and new passwords can't be same", ], 422);
                    }
                    $subAdmin->update([
                        'password' => Hash::make($request->newPassword)
                    ]);
                    // Remove all tokens associated with the subAdmin
                    $subAdmin->tokens()->delete();
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
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "You are not authorized to change admin's password",
                ], 400);
            }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => "Some exception occured",
            ], 400);
        }
    }

    public function getAllAdmins(){
        $allAdmins = SuperAdmin::select(
                                    DB::raw('CONCAT(LCASE(REPLACE(fullname, " ", "")),"_", id) AS sid'),
                                    'fullname',
                                    'phone',
                                    'email',
                                    'image',
                                    'address',
                                    'city',
                                    'state',
                                    'country',
                                    'postal_code',
                                    'created_at',
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
                ->where('is_master', 0)
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

    public function deleteAdmin(String $id){
        $admin = SuperAdmin::find($id);
        if($admin){
            if($admin->image != "" && File::exists($admin->image)) {
                File::delete($admin->image);
            }
            $admin->delete();
            return response()->json([
                'status' => true,
                'message' => "Successfully deleted",
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "No record Found",
            ], 404);
        }
    }

    public function getDesignations(){
        $designations = DB::table('designations')->select('id', 'designation')->get();
        if($designations){
            return response()->json([
                'status' => true,
                'designations' => $designations,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "No designation Found",
            ], 404);
        }
    }

    public function getCompanyTypes(){
        $companyTypes = DB::table('company_types')->select('id', 'type')->get();
        if($companyTypes){
            return response()->json([
                'status' => true,
                'companyTypes' => $companyTypes,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "No type Found",
            ], 404);
        }
    }

    public function dashboardWidgetCounts(){
        $totalCompanies = User::where('is_deleted', 0)
                            ->where('is_account_verified', 1)
                            ->count();;
        $pendingRequests = User::where('is_account_verified', 0)
                            ->where('is_deleted', 0)
                            ->count();
        $totalAdmins = SuperAdmin::where('is_deleted', 0)
                            ->where('is_master', 0)
                            ->count();
        return response()->json([
            'status' => true,
            'totalCompanies' => $totalCompanies,
            'pendingRequests' => $pendingRequests,
            'totalAdmins' => $totalAdmins,
        ], 200);
    }

    public function searchGloballyAdmin(Request $request){
        $searchText = $request->searchText;
        $filter = $request->filter == 'emp' ? 'emp' : 'org';
        if($filter == 'emp'){
            $query = Employee::where('is_deleted', '=', 0);

            if (!empty($searchText)) {
                $query->where(function ($query) use ($searchText) {
                    $query->where('emp_name', 'LIKE', "%$searchText%")
                        ->orWhere('emp_pan', 'LIKE', "%$searchText%")
                        ->orWhere('email', 'LIKE', "%$searchText%");
                });
            }

            $searchedResult = $query->orderBy('created_at', 'desc')
            ->paginate(10);
        }else{
            $query = User::select(
                            DB::raw('CONCAT(LCASE(REPLACE(company_name, " ", "")),"_", id) AS sid'),
                            'company_name',
                            'company_type',
                            'full_name',
                            'designation',
                            'domain_name',
                            'email',
                            'image',
                            'email_verified',
                            'email_verified_at',
                            'remember_token',
                            'created_at',
                            'updated_at',
                            'role',
                            'coupon',
                            'terms_and_conditions',
                            'is_deleted',
                            'deleted_by',
                            'company_phone',
                            'webmaster_email',
                            'company_address',
                            'company_city',
                            'company_state',
                            'company_country',
                            'company_postal_code',
                            'registration_number',
                            'company_social_link',
                            'is_account_verified',
                            'taken_membership',
                        )
                        ->where('is_deleted', '=', 0)
                        ->where('is_account_verified', '=', 1);
            if (!empty($searchText)) {
                $query->where(function ($query) use ($searchText) {
                    $query->where('company_name', 'LIKE', "%$searchText%")
                        ->orWhere('domain_name', 'LIKE', "%$searchText%");
                });
            }
            $searchedResult = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        }
        return response()->json([
            'status' => true,
            'searchResult' => $searchedResult,
        ], 200);
    }

}
