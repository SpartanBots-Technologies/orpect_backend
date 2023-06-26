<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use ZipArchive;
use App\Models\Employee;
use App\Models\User;
use App\Models\Position;

class EmployeeController extends Controller
{
    public function addEmployee(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "empId" => 'required',
            "empName" => 'required',
            "email" => 'required|email:filter',
            "phone" => 'required',
            "position" => 'required',
            "dateOfJoining" => 'required',
            'image' => $request->image ? 'file|mimes:jpg,jpeg,png|max:2048' : '',
            'pan_number' => 'required',
            'linkedIn' => $request->linkedIn ? 'url' : '',
        ]);
        if($inputValidation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        $added_by = Auth::user()->id;
        $errorMsg = "";
        $cnt = 0;
        if(Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('emp_id', $request->empId)->exists()){
            $cnt++;
            $errorMsg = $errorMsg . 'Employee Id';
        }
        if(Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('phone', $request->phone)->exists()){
            $cnt++;
            $errorMsg = $errorMsg . ($errorMsg != "" ? ', Phone number' : 'Phone number');
        }
        if(Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('email', $request->email)->exists()){
            $cnt++;
            $errorMsg = $errorMsg . ($errorMsg != "" ? ', Email' : 'Email');
        }
        if(Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('emp_pan', $request->pan_number)->exists()){
            $cnt++;
            $errorMsg = $errorMsg . ($errorMsg != "" ? ', Tax number' : 'Tax number');
        }
        if($cnt > 0){
            return response()->json([ 'status' => false, 'message' => $errorMsg . ' already exists' ], 422);
        }

        try {
            $image = null;

            if($request->hasFile('image')) {
                $randomNumber = random_int(1000, 9999);
                $file = $request->image;
                $date = date('YmdHis');
                $filename = "IMG_" . $randomNumber . "_" . $date;
                $extension = strtolower($file->getClientOriginalExtension());
                $imageName = $filename . '.' . $extension;
                $uploadPath = "uploads/users/profile_images/";
                $imageUrl = $uploadPath . $imageName;
                $file->move($uploadPath, $imageName);
                $image = $imageUrl;
            }

            if( !Position::where('added_by', $added_by)->where('position', trim($request->position))->exists() ){
                Position::create([
                    "position" => trim($request->position),
                    "added_by" => $added_by,
                ]);
            }

            $employee = Employee::create([
                'emp_id' => $request->empId,
                'emp_name' => $request->empName,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'date_of_joining' => $request->dateOfJoining,
                'profile_image' => $image,
                'added_by' => $added_by,
                'date_of_birth' => $request->dateOfBirth ?? null,
                'emp_pan' => $request->pan_number,
                'permanent_address' => $request->permanentAddress ?? null,
                'city' => $request->city ?? null,
                'country' => $request->country ?? null,
                'state' => $request->state ?? null,
                'linked_in' => $request->linkedIn ?? null,
            ]);
            if($employee) {
                return response()->json([
                    'status' => true,
                    'message' => "saved successfully",
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => "some error occured",
            ], 400);
        } catch(\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    //update Employee will work for update ExEmployee also
    public function updateEmployee(Request $request, string $id){
        $inputValidation = Validator::make($request->all(), [
            "empId" => $request->nonjoiner != 1 ? 'required' : '',
            "empName" => 'required',
            "email" => 'required|email:filter',
            "phone" => 'required',
            "position" => $request->nonjoiner != 1 ? 'required' : '',
            "dateOfJoining" => $request->nonjoiner != 1 ? 'date' : '',
            'pan_number' => $request->nonjoiner != 1 ? 'required' : '',
            'linkedIn' => $request->linkedIn ? 'url' : '',
        ]);
        if($inputValidation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try {
            $employeeDetails = employee::find($id);
            $added_by = Auth::user()->id;
            $errorMsg = "";
            $cnt = 0;
            if($request->empId != $employeeDetails->emp_id 
                && Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('emp_id', $request->empId)->exists()){
                $cnt++;
                $errorMsg = $errorMsg . 'Employee Id';
            }
            if($request->phone != $employeeDetails->phone 
                && Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('phone', $request->phone)->exists()){
                $cnt++;
                $errorMsg = $errorMsg . ($errorMsg != "" ? ', Phone number' : 'Phone number');
            }
            if($request->email != $employeeDetails->email 
                && Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('email', $request->email)->exists()){
                $cnt++;
                $errorMsg = $errorMsg . ($errorMsg != "" ? ', Email' : 'Email');
            }
            if($request->nonjoiner != 1 && $request->pan_number != $employeeDetails->emp_pan 
                && Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('emp_pan', $request->pan_number)->exists()){
                $cnt++;
                $errorMsg = $errorMsg . ($errorMsg != "" ? ', Tax number' : 'Tax number');
            }
            if($cnt > 0){
                return response()->json([ 'status' => false, 'message' => $errorMsg . ' already exists' ], 422);
            }
            $employee = $employeeDetails->update([
                'emp_id' => $request->empId ? $request->empId : null,
                'emp_name' => $request->empName,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position ? $request->position : null,
                'date_of_joining' => $request->dateOfJoining ? $request->dateOfJoining : null,
                'date_of_birth' => $request->dateOfBirth ?? null,
                'emp_pan' => $request->pan_number ? $request->pan_number : null,
                'permanent_address' => $request->permanentAddress ?? null,
                'city' => $request->city ?? null,
                'country' => $request->country ?? null,
                'state' => $request->state ?? null,
                'linked_in' => $request->linkedIn ?? null,
            ]);
            if($employee) {
                return response()->json([
                    'status' => true,
                    'message' => "updated successfully",
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => "some error occured",
            ], 400);
        } catch(\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateEmployeeImage(Request $request, string $id){
        $inputValidation = Validator::make($request->all(), [
            'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
        ]);
        if($inputValidation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Please select a file of type jpg, jpeg or png. Max size 2MB',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try {
            $employeeDetails = employee::find($id);
            $image = null;
            $oldImage = $request->oldImageName;

            if($request->hasFile('image')) {
                $randomNumber = random_int(1000, 9999);
                $file = $request->image;
                $date = date('YmdHis');
                $filename = "IMG_" . $randomNumber . "_" . $date;
                $extension = strtolower($file->getClientOriginalExtension());
                $imageName = $filename . '.' . $extension;
                $uploadPath = "uploads/users/profile_images/";
                $imageUrl = $uploadPath . $imageName;
                $file->move($uploadPath, $imageName);
                $image = $imageUrl;
                if($oldImage != "" && File::exists($oldImage)) {
                    File::delete($oldImage);
                }
            } else {
                $image = $oldImage;
            }

            $employee = $employeeDetails->update([
                'profile_image' => $image,
            ]);
            if($employee) {
                return response()->json([
                    'status' => true,
                    'message' => "Profile image updated successfully",
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => "some error occured",
            ], 400);
        } catch(\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function uploadEmployeeUsingCSV(Request $request){
        $inputValidation = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv',
            'image_zip_folder' => $request->hasFile('image_zip_folder') ? 'file|mimes:zip' : '',
        ]);
        if($inputValidation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Please select CSV file for data and zip file for images (if uploading images)',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        $imgPath = "";
        $extractPath = "";
        if($request->hasFile('image_zip_folder')){
            try{
                $zipFolder = $request->file('image_zip_folder');
                $extractPath = 'uploads/zipFolder/' . date('Ymd') . "_" . time() ;
                $file = $zipFolder->getClientOriginalName();
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $zip = new ZipArchive();
                if ($zip->open($zipFolder->getRealPath()) === true) {
                    $zip->extractTo($extractPath . "/");
                    $zip->close();
                } else {
                    return response()->json(['message' => 'Failed to extract the image zip folder. Try Again.'], 400);
                }
                $imgPath = $extractPath . "/" . $filename . "/";
            }catch(\Exception $e){
                return response()->json(['message' => 'Failed to extract the image zip folder. Try Again.'], 400);
            }
        }

        try {
            $added_by = Auth::user()->id;
            $file = $request->file('csv_file');
            $filePath = $file->getRealPath();
            $handle = fopen($filePath, 'r');
            $dataUnableToInsert = [];
            $lineNumber = 0;
            $errCounter = 0;
            $successCounter = 0;

            DB::beginTransaction();

            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;
                $image = null;
                if ($lineNumber === 1) {
                    continue;
                }

                $emp_id = $data[1];
                $emp_name = $data[2];
                $emp_email = $data[3];
                $emp_phone = $data[4];
                $emp_position = $data[5];
                $emp_dob = $data[6];
                $emp_pan = $data[7];
                $emp_address = $data[8];
                $city = $data[9];
                $country = $data[10];
                $state = $data[11];
                $emp_doj = $data[12];
                $emp_dol = $data[13];
                $ex_emp = $data[14];
                $non_joiner = $data[15];
                $prfmce_rat = $data[16];
                $prfsnl_skl_rat = $data[17];
                $team_cmuntn_rat = $data[18];
                $attde_bhavr_rat = $data[19];
                $review = $data[20];
                $linked_in = $data[21];
                $lastCTC = $data[22];
                $emp_image = $data[23];

                if ( $emp_name != "" && $emp_email != "" && $emp_phone != "") {
                    $errorMsg = "";
                    $cnt = 0;
                    if($non_joiner != 1 && $emp_id == ""){
                        $cnt++;
                        $errorMsg = $errorMsg . 'Employee Id is missing';
                    }
                    if($non_joiner != 1 && $emp_id != "" && $emp_id != null 
                        && Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('emp_id', $emp_id)->exists()){
                        $cnt++;
                        $errorMsg = $errorMsg . 'Employee Id';
                    }
                    if(Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('phone', $emp_phone)->exists()){
                        $cnt++;
                        $errorMsg = $errorMsg . ($errorMsg != "" ? ', Phone number' : 'Phone number');
                    }
                    if(Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('email', $emp_email)->exists()){
                        $cnt++;
                        $errorMsg = $errorMsg . ($errorMsg != "" ? ', Email' : 'Email');
                    }
                    if($emp_pan != "" && $emp_pan != null 
                        && Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('emp_pan', $emp_pan)->exists()){
                        $cnt++;
                        $errorMsg = $errorMsg . ($errorMsg != "" ? ', Tax number' : 'Tax number');
                    }
                    if($cnt == 0){
                        if($ex_emp == 1 && $non_joiner == 0){
                            $ex_emp = 1;
                            $non_joiner = 0;
                            $emp_doj = $emp_doj != "" && Carbon::parse($emp_doj) ? Carbon::parse($emp_doj)->format('Y-m-d') : null;
                            $emp_dol = $emp_dol != "" && Carbon::parse($emp_dol) ? Carbon::parse($emp_dol)->format('Y-m-d') : null;
                            $prfmce_rat = is_numeric($prfmce_rat) && is_int($prfmce_rat + 0) && $prfmce_rat < 6 ? $prfmce_rat : 0;
                            $prfsnl_skl_rat = is_numeric($prfsnl_skl_rat) && is_int($prfsnl_skl_rat + 0) && $prfsnl_skl_rat < 6 ? $prfsnl_skl_rat : 0; 
                            $team_cmuntn_rat = is_numeric($team_cmuntn_rat) && is_int($team_cmuntn_rat + 0) && $team_cmuntn_rat < 6 ? $team_cmuntn_rat : 0;
                            $attde_bhavr_rat = is_numeric($attde_bhavr_rat) && is_int($attde_bhavr_rat + 0) && $attde_bhavr_rat < 6 ? $attde_bhavr_rat : 0;
                            $overall_rat = ($prfmce_rat + $prfsnl_skl_rat + $team_cmuntn_rat + $attde_bhavr_rat ) / 4;
                            $review = $review != "" ? $review : null;
                            $status_changed_at = now();
                            $lastCTC = preg_match('/^\d+$/', $lastCTC) ? $lastCTC : null;
                        }else if ($ex_emp == 0 && $non_joiner == 1){
                            $ex_emp = 0;
                            $non_joiner = 1;
                            $emp_doj = null;
                            $emp_dol = null;
                            $prfmce_rat = 0;
                            $prfsnl_skl_rat = 0; 
                            $team_cmuntn_rat = 0;
                            $attde_bhavr_rat = 0;
                            $overall_rat = 0;
                            $review = $review != "" ? $review : null;
                            $status_changed_at = now();
                            $lastCTC = null;
                        }else{
                            $ex_emp = 0;
                            $non_joiner = 0;
                            $emp_doj = $emp_doj != "" && Carbon::parse($emp_doj) ? Carbon::parse($emp_doj)->format('Y-m-d') : null;
                            $emp_dol = null;
                            $prfmce_rat = 0;
                            $prfsnl_skl_rat = 0; 
                            $team_cmuntn_rat = 0;
                            $attde_bhavr_rat = 0;
                            $overall_rat = 0;
                            $review = null;
                            $status_changed_at = null;
                            $lastCTC = preg_match('/^\d+$/', $lastCTC) ? $lastCTC : null;
                        }

                        if ($imgPath != "" && $emp_image != "") {
                            $imagePath = $imgPath . $emp_image;
                            try{
                                if (file_exists($imagePath) && is_readable($imagePath)) {
                                    $randomNumber = random_int(100000, 999999);
                                    $date = date('YmdHis');
                                    $filename = "IMG_" . $randomNumber . "_" . $date ;
                                    $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                                    $imageName = $filename . '.' . $extension;
                                    $uploadPath = "uploads/users/profile_images/";
                                    $image = $uploadPath . $imageName;
                                    rename($imagePath, $image);
                                }
                            }catch(\Exception $e){
                            }
                        }
                        $emp_dob = $emp_dob != "" && Carbon::parse($emp_dob) ? Carbon::parse($emp_dob)->format('Y-m-d') : null;

                        Employee::create([
                            'emp_id' => $emp_id != "" ? $emp_id : null,
                            'emp_name' => $emp_name,
                            'email' => $emp_email,
                            'phone' => $emp_phone,
                            'position' => $emp_position ? $emp_position : null,
                            'date_of_birth' => $emp_dob,
                            'date_of_joining' => $emp_doj,
                            'date_of_leaving' => $emp_dol,
                            'added_by' => $added_by,
                            'ex_employee' => $ex_emp,
                            'non_joiner' => $non_joiner,
                            'emp_pan' => $emp_pan ? $emp_pan : null,
                            'permanent_address' => $emp_address ? $emp_address : null,
                            'city' => $city ? $city : null,
                            'country' => $country ? $country : null,
                            'state' => $state ? $state : null,
                            'linked_in' => $linked_in ? $linked_in : null,
                            'profile_image' => $image,
                            'status_changed_at' => $status_changed_at,
                            'overall_rating' => $overall_rat,
                            'performance_rating' => $prfmce_rat,
                            'professional_skills_rating' => $prfsnl_skl_rat,
                            'teamwork_communication_rating' => $team_cmuntn_rat,
                            'attitude_behaviour_rating' => $attde_bhavr_rat,
                            'review' => $review,
                            'last_CTC' => $lastCTC,
                        ]);

                        $successCounter++;
                    }else{
                        $errCounter++;
                        $dataError = [
                            "emp_id" => $emp_id,
                            "emp_name" => $emp_name,
                            "email" => $emp_email,
                            "phone" => $emp_phone,
                            "message" => $errorMsg . ' already exists',
                        ];
                        $dataUnableToInsert[] = $dataError;
                    }
                } else {
                    $errCounter++;
                    $dataError = [
                        "emp_id" => $emp_id,
                        "emp_name" => $emp_name,
                        "email" => $emp_email,
                        "phone" => $emp_phone,
                        "message" => "Any of these four fields are missing",
                    ];
                    $dataUnableToInsert[] = $dataError;
                }
            }

            DB::commit();
            fclose($handle);
            if (!empty($extractPath) && file_exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }

            if($successCounter) {
                return response()->json([
                    'status' => true,
                    'message' => $successCounter . " employees saved successfully. " . $errCounter . " got error due to missing fields or already exists",
                    'errorList' => $dataUnableToInsert,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "some error occured. 0 files saved",
                    'errorList' => $dataUnableToInsert,
                ], 400);
            }
        } catch(\Exception $e) {
            if (!empty($extractPath) && file_exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
            return response()->json([
                'status' => false,
                'message' => strpos($e->getMessage(), 'array key') !== false ? 'Please make sure you have all the fields in CSV file' : $e->getMessage() ,
            ], 400);
        }
    }

    public function getCurrentEmployees(Request $request){
        $searchValue = $request->input('searchText', '');
        $position = $request->input('position', '');
        $id = $request->id ? $request->id : Auth::user()->id;
        $query = Employee::where('added_by', '=', $id)
            ->where('ex_employee', '=', 0)
            ->where('non_joiner', '=', 0)
            ->where('is_deleted', '=', 0);

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('emp_name', 'LIKE', "%$searchValue%")
                    ->orWhere('email', 'LIKE', "%$searchValue%")
                    ->orWhere('phone', 'LIKE', "%$searchValue%");
            });
        }

        if (!empty($position)) {
            $query->where('position', '=', $position);
        }

        $allCurrentEmployees = $query->orderBy('created_at', 'desc')
        ->paginate(10);

        return response()->json([
            'status' => true,
            'currentEmployees' => $allCurrentEmployees,
        ], 200);
    }

    public function getEmployeeById(string $id){
        $employeeDetail = Employee::where('added_by', '=', Auth::user()->id)
                                ->where('is_deleted', '=', 0)
                                ->where('id', '=', $id)
                                ->get();
        if($employeeDetail) {
            return response()->json([
                'status' => true,
                'employee' => $employeeDetail,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Employee Not Found",
            ], 404);
        }
    }

    public function getEmployeeByIdForAdmin(string $id){
        $employeeDetail = Employee::where('is_deleted', '=', 0)
                                ->where('id', '=', $id)
                                ->get();
        return response()->json([
            'status' => true,
            'employee' => $employeeDetail,
        ], 200);
    }

    public function deleteEmployee(string $id){
        $employee = Employee::find($id);
        if ($employee) {
            $employee->update([
                'is_deleted' => 1
            ]);
            return response()->json([
                'status' => true,
                'messsage' => 'Successfully deleted',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'messsage' => 'Employee not found',
            ], 404);
        }
    }

    public function getExEmployees(Request $request){
        $searchValue = $request->input('searchText', '');
        $position = $request->input('position', '');
        $id = $request->id ? $request->id : Auth::user()->id;
        $query = Employee::where('added_by', '=', $id)
                    ->where('ex_employee', '=', 1)
                    ->where('non_joiner', '=', 0)
                    ->where('is_deleted', '=', 0);

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('emp_name', 'LIKE', "%$searchValue%")
                    ->orWhere('email', 'LIKE', "%$searchValue%")
                    ->orWhere('phone', 'LIKE', "%$searchValue%");
            });
        }

        if (!empty($position)) {
            $query->where('position', '=', $position);
        }

        $employeeDetails = $query->orderBy('status_changed_at', 'desc')
        ->paginate(10);

        return response()->json([
            'status' => true,
            'exEmployee' => $employeeDetails,
        ], 200);
    }

    public function getNonJoiners(Request $request){
        $searchValue = $request->input('searchText', '');
        $position = $request->input('position', '');
        $id = $request->id ? $request->id : Auth::user()->id;
        $query = Employee::where('added_by', '=', $id)
                    ->where('non_joiner', '=', 1)
                    ->where('ex_employee', '=', 0)
                    ->where('is_deleted', '=', 0);

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('emp_name', 'LIKE', "%$searchValue%")
                    ->orWhere('email', 'LIKE', "%$searchValue%")
                    ->orWhere('phone', 'LIKE', "%$searchValue%");
            });
        }

        if (!empty($position)) {
            $query->where('position', '=', $position);
        }

        $employeeDetails = $query->orderBy('status_changed_at', 'desc')
        ->paginate(10);
        return response()->json([
            'status' => true,
            'nonJoiners' => $employeeDetails,
        ], 200);
    }

    public function rateAndReview(Request $request, string $id){
        $inputValidation = Validator::make($request->all(), [
            "exEmployee" => 'required',
            "nonJoiner" => 'required',
            "performanceRating" => 'required',
            "professionalSkillsRating" => 'required',
            "teamworkCommunicationRating" => 'required',
            "attitudeBehaviourRating" => 'required',
            "review" => 'required',
            "dateOfLeaving" => $request->dateOfLeaving ? 'date' : '',
        ]);
        if($inputValidation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try {
            $employeeDetails = employee::find($id);
            $rating = ($request->performanceRating + $request->professionalSkillsRating
                    + $request->teamworkCommunicationRating + $request->attitudeBehaviourRating) / 4;
            $employee = $employeeDetails->update([
                'ex_employee' => $request->exEmployee,
                'non_joiner' => $request->nonJoiner,
                'performance_rating' => $request->performanceRating ?? 0,
                'professional_skills_rating' => $request->professionalSkillsRating ?? 0,
                'teamwork_communication_rating' => $request->teamworkCommunicationRating ?? 0,
                'attitude_behaviour_rating' => $request->attitudeBehaviourRating ?? 0,
                'overall_rating' => $rating,
                'review' => $request->review,
                'date_of_leaving' => $request->dateOfLeaving ?? null,
                'status_changed_at' => now(),
                'last_CTC' => isset($request->lastCTC) && $request->lastCTC != "" && preg_match('/^\d+$/', $request->lastCTC) ? $request->lastCTC : null,
            ]);
            if($employee) {
                return response()->json([
                    'status' => true,
                    'message' => "Saved successfully",
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => "some error occured",
            ], 400);
        } catch(\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function searchEmployeeGlobally(Request $request){
        $searchText = $request->searchText;
        $emp = $request->input('emp', '');
        $employeesQuery = Employee::select(
            'employees.id',
            'employees.emp_name',
            'employees.email',
            'employees.phone',
            'employees.profile_image',
            'employees.ex_employee',
            'employees.non_joiner',
            DB::raw("
                CASE
                    WHEN employees.ex_employee = 1 AND employees.non_joiner = 0 THEN 'Ex Employee'
                    WHEN employees.ex_employee = 0 AND employees.non_joiner = 1 THEN 'Non Joiner'
                    WHEN employees.ex_employee = 0 AND employees.non_joiner = 0 THEN 'Current Employee'
                    ELSE 'Unknown'
                END AS employee_type
            "),
            'employees.overall_rating',
            'employees.performance_rating',
            'employees.professional_skills_rating',
            'employees.teamwork_communication_rating',
            'employees.attitude_behaviour_rating',
            DB::raw("DATE_FORMAT(employees.created_at, '%d-%m-%Y') AS added_on"),
            DB::raw("DATE_FORMAT(status_changed_at, '%d-%m-%Y') AS last_review_on"),
            'employees.linked_in',
            'users.company_name',
            DB::raw('(SELECT COUNT(*) FROM employees AS e2 WHERE (e2.phone = employees.phone OR e2.email = employees.email OR e2.emp_pan = employees.emp_pan)) AS total_reviews'),
        )
        ->join('users', 'users.id', '=', 'employees.added_by') // Joined `users` table
        ->where('employees.is_deleted', 0)
        ->where(function ($query) use ($searchText) {
            $query->where('employees.emp_name', 'like', '%' . $searchText . '%')
                ->orWhere('employees.email', 'like', '%' . $searchText . '%')
                ->orWhere('employees.emp_pan', 'like', '%' . $searchText . '%')
                ->orWhere('employees.phone', 'like', '%' . $searchText . '%');
        });
        
        if ($emp != '' && $emp == 'ex') {
            $employeesQuery->where('employees.ex_employee', 1)
                ->where('employees.non_joiner', 0);
        } elseif ($emp != '' && $emp == 'nonJoiner') {
            $employeesQuery->where('employees.ex_employee', 0)
                ->where('employees.non_joiner', 1);
        } else {
            $employeesQuery->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('employees.ex_employee', 1)
                        ->orWhere('employees.non_joiner', 1);
                });
            });
        }
        
        $employeesPaginated = $employeesQuery->paginate(10);

        if($employeesPaginated) {
            return response()->json([
                'status' => true,
                'employees' => $employeesPaginated,
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "No record found",
        ], 404);
    }

    public function addReview(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "empName" => 'required',
            "email" => 'required|email:filter',
            "phone" => 'required',
            "position" => 'required',
            "dateOfJoining" => $request->dateOfJoining ? 'date' : '',
            'image' => $request->image ? 'file|mimes:jpg,jpeg,png|max:2048' : '',
            'linkedIn' => $request->linkedIn ? 'url' : '',
            "exEmployee" => 'required',
            "nonJoiner" => 'required',
            "review" => 'required',
            "dateOfLeaving" => $request->dateOfLeaving ? 'date' : '',
        ]);
        if($inputValidation->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try {
            $added_by = Auth::user()->id;
            $errorMsg = "";
            $cnt = 0;
            if($request->empId != "" && $request->empId != null 
                && Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('emp_id', $request->empId)->exists()){
                $cnt++;
                $errorMsg = $errorMsg . 'Employee Id';
            }
            if(Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('phone', $request->phone)->exists()){
                $cnt++;
                $errorMsg = $errorMsg . ($errorMsg != "" ? ', Phone number' : 'Phone number');
            }
            if(Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('email', $request->email)->exists()){
                $cnt++;
                $errorMsg = $errorMsg . ($errorMsg != "" ? ', Email' : 'Email');
            }
            if($request->pan_number != "" && $request->pan_number != null 
                    && Employee::where('added_by', $added_by)->where('is_deleted', 0)->where('emp_pan', $request->pan_number)->exists()){
                $cnt++;
                $errorMsg = $errorMsg . ($errorMsg != "" ? ', Tax number' : 'Tax number');
            }
            if($cnt > 0){
                return response()->json([ 'status' => false, 'message' => $errorMsg . ' already exists' ], 422);
            }
            $image = null;

            if($request->hasFile('image')) {
                $randomNumber = random_int(1000, 9999);
                $file = $request->image;
                $date = date('YmdHis');
                $filename = "IMG_" . $randomNumber . "_" . $date;
                $extension = strtolower($file->getClientOriginalExtension());
                $imageName = $filename . '.' . $extension;
                $uploadPath = "uploads/users/profile_images/";
                $imageUrl = $uploadPath . $imageName;
                $file->move($uploadPath, $imageName);
                $image = $imageUrl;
            }
            if( isset($request->position) && trim($request->position) != "" && !Position::where('added_by', $added_by)->where('position', trim($request->position))->exists() ){
                Position::create([
                    "position" => trim($request->position),
                    "added_by" => $added_by,
                ]);
            }
            $rating = ( $request->performanceRating + $request->professionalSkillsRating
                    + $request->teamworkCommunicationRating + $request->attitudeBehaviourRating ) / 4;

            $employee = Employee::create([
                'emp_id' => $request->empId ?? null,
                'emp_name' => $request->empName,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'date_of_joining' => $request->dateOfJoining ?? null,
                'profile_image' => $image,
                'added_by' => $added_by,
                'date_of_birth' => $request->dateOfBirth,
                'emp_pan' => $request->pan_number ?? null,
                'permanent_address' => $request->permanentAddress,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'linked_in' => $request->linkedIn,
                'ex_employee' => $request->exEmployee,
                'non_joiner' => $request->nonJoiner,
                'review' => $request->review,
                'date_of_leaving' => $request->dateOfLeaving ?? null,
                'status_changed_at' => now(),
                'overall_rating' => $rating,
                'performance_rating' => $request->performanceRating ?? 0,
                'professional_skills_rating' => $request->professionalSkillsRating ?? 0,
                'teamwork_communication_rating' => $request->teamworkCommunicationRating ?? 0,
                'attitude_behaviour_rating' => $request->attitudeBehaviourRating ?? 0,
                'last_CTC' => isset($request->lastCTC) && $request->lastCTC != "" && preg_match('/^\d+$/', $request->lastCTC) ? $request->lastCTC : null,
            ]);
            if($employee) {
                return response()->json([
                    'status' => true,
                    'message' => "saved successfully",
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => "some error occured",
            ], 400);
        } catch(\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getTotalEmployees(String $id){
        $query = Employee::select('id', 'ex_employee', 'non_joiner')
                ->where('added_by', $id)
                ->where('is_deleted', 0)
                ->get();

        if(count($query) > 0) {
            $currentEmp = $query->where('ex_employee', 0)
                    ->where('non_joiner', 0);
            $exEmp = $query->where('ex_employee', 1)
                    ->where('non_joiner', 0);
            $nonJoiner = $query->where('ex_employee', 0)
                    ->where('non_joiner', 1);

            return response()->json([
                'status' => true,
                'totalCurrentEmp' => count($currentEmp),
                'totalExEmp' => count($exEmp),
                'totalNonJoiner' => count($nonJoiner),
                'totalSubReview' => count($exEmp) + count($nonJoiner),
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => "No record found",
        ], 400);
    }

    public function getEmployeeByIdForGlobalSearch(String $id){
        try{
            $employee = Employee::where('id', '=', $id)->first();
            if($employee) {

                if( Auth::user()->taken_membership == 0 ) {
                    $particularEmployee = Employee::select(
                        'id',
                        'emp_name',
                        'email',
                        'phone',
                        'profile_image',
                        'ex_employee',
                        'non_joiner',
                        'overall_rating',
                        'performance_rating',
                        'professional_skills_rating',
                        'teamwork_communication_rating',
                        'attitude_behaviour_rating',
                        DB::raw("
                            CASE
                                WHEN employees.ex_employee = 1 AND employees.non_joiner = 0 THEN 'Ex Employee'
                                WHEN employees.ex_employee = 0 AND employees.non_joiner = 1 THEN 'Non Joiner'
                                WHEN employees.ex_employee = 0 AND employees.non_joiner = 0 THEN 'Current Employee'
                                ELSE 'Unknown'
                            END AS employee_type
                        "),
                        DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y') AS added_on"),
                        'review',
                        'linked_in',
                        DB::raw("DATE_FORMAT(status_changed_at, '%d-%m-%Y') AS last_review_on"),
                        'last_CTC',
                        DB::raw("DATE_FORMAT(date_of_joining, '%d-%m-%Y') AS date_of_joining"),
                        DB::raw("DATE_FORMAT(date_of_leaving, '%d-%m-%Y') AS date_of_leaving"),
                    )
                    ->where('is_deleted', 0)
                    ->where(function ($query) use ($employee) {
                        if ($employee->emp_pan != null && $employee->emp_pan != '') {
                            $query->where('emp_pan', $employee->emp_pan)
                                ->orWhere('phone', $employee->phone)
                                ->orWhere('email', $employee->email);
                        } else {
                            $query->where('phone', $employee->phone)
                                ->orWhere('email', $employee->email);
                        }
                    })
                    ->where(function ($query) {
                        $query->where('ex_employee', 1)
                            ->orWhere('non_joiner', 1);
                    })
                    ->paginate(5);
                    return response()->json([
                        'status' => true,
                        'taken_membership' => 0,
                        'reviews' => $particularEmployee,
                    ], 200);
                } else if( Auth::user()->taken_membership == 1 ) {
                    $empUserWithMembership = Employee::select(
                        'employees.id',
                        'employees.emp_name',
                        'employees.phone',
                        'employees.profile_image',
                        'employees.ex_employee',
                        'employees.non_joiner',
                        'employees.overall_rating',
                        'employees.performance_rating',
                        'employees.professional_skills_rating',
                        'employees.teamwork_communication_rating',
                        'employees.attitude_behaviour_rating',
                        'employees.review',
                        'users.company_name',
                        'users.email AS company_email',
                    )
                    ->join('users', 'employees.added_by', '=', 'users.id')
                    ->where('employees.is_deleted', 0)
                    ->where(function ($query) use ($employee) {
                        if ($employee->emp_pan != null && $employee->emp_pan != '') {
                            $query->where('emp_pan', $employee->emp_pan)
                                ->orWhere('phone', $employee->phone)
                                ->orWhere('email', $employee->email);
                        } else {
                            $query->where('phone', $employee->phone)
                                ->orWhere('email', $employee->email);
                        }
                    })
                    ->where(function ($query) {
                        $query->where('employees.ex_employee', 1)
                            ->orWhere('employees.non_joiner', 1);
                    })
                    ->paginate(5);
                    return response()->json([
                        'status' => true,
                        'taken_membership' => 1,
                        'reviews' => $empUserWithMembership,
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "No record found",
                ], 404);
            }
        } catch(\Exception $e){
            return response()->json([ 'status' => false, 'message' => "Some error occured", ], 400);
        }
    }

    public function getExEmployeesAndNonJoiners(Request $request){
        $searchValue = $request->input('searchText', '');
        $position = $request->input('position', '');
        $emp = $request->input('emp', '');
        // $id = $request->id ? $request->id : Auth::user()->id;
        $id = Auth::user()->id;
        $query = Employee::where('added_by', '=', $id)
                    ->where('is_deleted', '=', 0)
                    ->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('ex_employee', 1)
                                ->orWhere('non_joiner', 1);
                        });
                    });

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('emp_name', 'LIKE', "%$searchValue%")
                    ->orWhere('email', 'LIKE', "%$searchValue%")
                    ->orWhere('phone', 'LIKE', "%$searchValue%");
            });
        }

        if (!empty($position)) {
            $query->where('position', '=', $position);
        }
        if (!empty($emp) && $emp == 'ex') {
            $query->where('ex_employee', '=', 1);
        }else if (!empty($emp) && $emp == 'nonJoiner') {
            $query->where('non_joiner', '=', 1);
        }
        $employeeDetails = $query->orderBy('status_changed_at', 'desc')
        ->paginate(10);

        return response()->json([
            'status' => true,
            'exEmployee' => $employeeDetails,
        ], 200);
    }

    public function getEmpReviewForAdmin(String $id){
        try{
            $employee = Employee::where('id', '=', $id)->first();
            if($employee) {
                $empReviewForAdmin = Employee::select(
                        'employees.id',
                        'employees.emp_name',
                        'employees.email',
                        'employees.phone',
                        'employees.profile_image',
                        'employees.ex_employee',
                        'employees.non_joiner',
                        'employees.overall_rating',
                        'employees.performance_rating',
                        'employees.professional_skills_rating',
                        'employees.teamwork_communication_rating',
                        'employees.attitude_behaviour_rating',
                        'employees.review',
                        DB::raw("
                            CASE
                                WHEN employees.ex_employee = 1 AND employees.non_joiner = 0 THEN 'Ex Employee'
                                WHEN employees.ex_employee = 0 AND employees.non_joiner = 1 THEN 'Non Joiner'
                                WHEN employees.ex_employee = 0 AND employees.non_joiner = 0 THEN 'Current Employee'
                                ELSE 'Unknown'
                            END AS employee_type
                        "),
                        'employees.linked_in',
                        'users.company_name',
                        'users.email AS company_email',
                        'users.company_phone AS company_phone',
                        'users.image AS company_logo',
                        'users.domain_name AS company_domain',
                        DB::raw("DATE_FORMAT(employees.created_at, '%d-%m-%Y') AS added_on"),
                        DB::raw("DATE_FORMAT(employees.status_changed_at, '%d-%m-%Y') AS last_review_on"),
                        'employees.last_CTC',
                        DB::raw("DATE_FORMAT(employees.date_of_joining, '%d-%m-%Y') AS date_of_joining"),
                        DB::raw("DATE_FORMAT(employees.date_of_leaving, '%d-%m-%Y') AS date_of_leaving"),
                    )
                    ->join('users', 'employees.added_by', '=', 'users.id')
                    ->where('employees.is_deleted', 0)
                    ->where(function ($query) use ($employee) {
                        if ($employee->emp_pan != null && $employee->emp_pan != '') {
                            $query->where('employees.emp_pan', $employee->emp_pan)
                                ->orWhere('employees.phone', $employee->phone)
                                ->orWhere('employees.email', $employee->email);
                        } else {
                            $query->where('employees.phone', $employee->phone)
                                ->orWhere('employees.email', $employee->email);
                        }
                    })
                    ->where(function ($query) {
                        $query->where('employees.ex_employee', 1)
                            ->orWhere('employees.non_joiner', 1);
                    })
                    ->paginate(5);
                return response()->json([
                    'status' => true,
                    'taken_membership' => 1,
                    'reviews' => $empReviewForAdmin,
                ], 200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => "No record found",
                ], 404);
            }
        } catch(\Exception $e){
            return response()->json([ 'status' => false, 'message' => "Some error occured", ], 400);
        }
    }
}