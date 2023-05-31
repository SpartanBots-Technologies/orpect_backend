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

class EmployeeController extends Controller
{
    public function addEmployee(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "empId" => 'required',
            "empName" => 'required',
            "email" => 'required',
            "phone" => 'required',
            "position" => 'required',
            "dateOfJoining" => 'required',
            'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'linkedIn' => 'sometimes|url',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try{
            $added_by = Auth::user()->id;
            $image = "";

            if($request->hasFile('image')){
                $randomNumber = random_int(1000, 9999);
                $file = $request->image;
                $date = date('YmdHis');
                $filename = "IMG_" . $randomNumber . "_" . $date;
                $extension = strtolower( $file->getClientOriginalExtension() );
                $imageName = $filename . '.' . $extension;
                $uploadPath = "uploads/users/profile_images/";
                $imageUrl = $uploadPath . $imageName;
                $file->move($uploadPath, $imageName);
                $image = $imageUrl;
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
                'date_of_birth' => $request->dateOfBirth,
                'emp_pan' => $request->pan_number,
                'permanent_address' => $request->permanentAddress,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'linked_in' => $request->linkedIn,
            ]);
            if($employee){
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

    public function updateEmployee(Request $request, string $id){
        $inputValidation = Validator::make($request->all(), [
            "empId" => 'required',
            "empName" => 'required',
            "email" => 'required',
            "phone" => 'required',
            "position" => 'required',
            "dateOfJoining" => 'required',
            'linkedIn' => 'sometimes|url',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try{
            $employeeDetails = employee::find($id);

            $employee = $employeeDetails->update([
                'emp_id' => $request->empId,
                'emp_name' => $request->empName,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'date_of_joining' => $request->dateOfJoining,
                'date_of_birth' => $request->dateOfBirth,
                'emp_pan' => $request->pan_number,
                'permanent_address' => $request->permanentAddress,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'linked_in' => $request->linkedIn,
            ]);
            if($employee){
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

    public function updateEmployeeImage(Request $request, string $id){
        $inputValidation = Validator::make($request->all(), [
            'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Please select a file of type jpg, jpeg or png. Max size 2MB',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try{
            $employeeDetails = employee::find($id);
            $image = null;
            $oldImage = $request->oldImageName;

            if($request->hasFile('image')){
                $randomNumber = random_int(1000, 9999);
                $file = $request->image;
                $date = date('YmdHis');
                $filename = "IMG_" . $randomNumber . "_" . $date;
                $extension = strtolower( $file->getClientOriginalExtension() );
                $imageName = $filename . '.' . $extension;
                $uploadPath = "uploads/users/profile_images/";
                $imageUrl = $uploadPath . $imageName;
                $file->move($uploadPath, $imageName);
                $image = $imageUrl;
                if($oldImage != "" && File::exists($oldImage)){
                    File::delete($oldImage);
                }
            }else{
                $image = $oldImage;
            }

            $employee = $employeeDetails->update([
                'profile_image' => $image,
            ]);
            if($employee){
                return response()->json([
                    'status' => true,
                    'message' => "Profile image updated successfully",
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

    public function uploadEmployeeUsingCSV(Request $request){
        $inputValidation = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv',
            'image_zip_folder' => 'sometimes|file|mimes:zip',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Please select CSV file and zip file of images',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        $zipFolder = $request->file('image_zip_folder');
        $extractPath = 'uploads/zipFolder/' . date('Ymd') . "_" . time() ;
        $file = $zipFolder->getClientOriginalName();
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $zip = new ZipArchive;
        if ( $zip->open( $zipFolder->getRealPath() ) === true ) {
            $zip->extractTo( $extractPath . "/");
            $zip->close();
        } else {
            return response()->json(['message' => 'Failed to extract the zip folder. Try Again.'], 400);
        }
        $imgPath = $extractPath . "/" . $filename . "/";

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
                $linked_in = $data[13];
                $emp_image = $data[14];

                if ($emp_id != "" && $emp_name != "" && $emp_email != "" && $emp_phone != "") {
                    if ($imgPath != "" && $emp_image != "") {
                        $imagePath = $imgPath . $emp_image;
                        if ( file_exists( $imagePath ) && is_readable( $imagePath ) ) {
                            $randomNumber = random_int(100000, 999999);
                            $date = date('YmdHis');
                            $filename = "IMG_" . $randomNumber . "_" . $date ;
                            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                            $imageName = $filename . '.' . $extension;
                            $uploadPath = "uploads/users/profile_images/";
                            $image = $uploadPath . $imageName;
                            rename($imagePath, $image);
                        }
                    }
                    $emp_dob = $emp_dob != "" && Carbon::parse($emp_dob) ? Carbon::parse($emp_dob)->format('Y-m-d') : null;
                    $emp_doj = $emp_doj != "" && Carbon::parse($emp_doj) ? Carbon::parse($emp_doj)->format('Y-m-d') : null;
                    $employee = Employee::create([
                        'emp_id' => $emp_id,
                        'emp_name' => $emp_name,
                        'email' => $emp_email,
                        'phone' => $emp_phone,
                        'position' => $emp_position ? $emp_position : null,
                        'date_of_birth' => $emp_dob,
                        'date_of_joining' => $emp_doj,
                        'emp_pan' => $emp_pan ? $emp_pan : null,
                        'permanent_address' => $emp_address ? $emp_address : null,
                        'city' => $city ? $city : null,
                        'country' => $country ? $country : null,
                        'state' => $state ? $state : null,
                        'linked_in' => $linked_in ? $linked_in : null,
                        'profile_image' => $image,
                        'added_by' => $added_by,
                    ]);
    
                    $successCounter++;
                } else {
                    $errCounter++;
                    $dataError = [
                        "emp_id" => $emp_id,
                        "emp_name" => $emp_name,
                        "email" => $emp_email,
                        "phone" => $emp_phone,
                    ];
                    $dataUnableToInsert[$errCounter] = $dataError;
                }
            }
    
            DB::commit();
            fclose($handle);
            File::deleteDirectory($extractPath);
    
            if($successCounter){
                return response()->json([
                    'status' => true,
                    'message' => $successCounter . " employees saved successfully. " . $errCounter . " got error due to missing fields",
                    'errorList' => $dataUnableToInsert,
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "some error occured. 0 files saved",
                ], 400);
            }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getCurrentEmployees(Request $request){
        $searchValue = $request->input('searchText', '');
        $position = $request->input('position', '');

        $query = Employee::where('added_by', '=', Auth::user()->id)
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
        if($employeeDetail){
            return response()->json([
                'status' => true,
                'employee' => $employeeDetail,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => "Employee Not Found",
            ], 404);
        }
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
        }else{
            return response()->json([
                'status' => false,
                'messsage' => 'Employee not found',
            ], 404);
        }
    }

    public function getExEmployees(Request $request){
        $searchValue = $request->input('searchText', '');
        $position = $request->input('position', '');
        $query = Employee::where('added_by', '=', Auth::user()->id)
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

        $employeeDetails = $query->orderBy('date_of_leaving', 'desc')
        ->paginate(10);

        return response()->json([
            'status' => true,
            'exEmployee' => $employeeDetails,
        ], 200);
    }

    public function getNonJoiners(Request $request){
        $searchValue = $request->input('searchText', '');
        $position = $request->input('position', '');
        $query = Employee::where('added_by', '=', Auth::user()->id)
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

        $employeeDetails = $query->orderBy('date_of_leaving', 'desc')
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
            "rating" => 'required',
            "review" => 'required',
            "dateOfLeaving" => 'required',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try{
            $employeeDetails = employee::find($id);

            $employee = $employeeDetails->update([
                'ex_employee' => $request->exEmployee,
                'non_joiner' => $request->nonJoiner,
                'rating' => $request->rating,
                'review' => $request->review,
                'date_of_leaving' => $request->dateOfLeaving,
            ]);
            if($employee){
                return response()->json([
                    'status' => true,
                    'message' => "Saved successfully",
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

    public function searchEmployeeGlobally(Request $request){
        $searchText = $request->searchText;
        $exEmp = $request->input('exEmp', 0);
        $nonJEmp = $request->input('nonJoinerEmp', 0);
        $currentEmp = $request->input('currentEmp', 0);
        $employeesQuery = Employee::where('is_deleted', 0)
                    ->where(function ($query) use ($searchText) {
                        $query->where('emp_name', 'like', '%' . $searchText . '%')
                            ->orWhere('email', 'like', '%' . $searchText . '%')
                            ->orWhere('emp_pan', 'like', '%' . $searchText . '%')
                            ->orWhere('phone', 'like', '%' . $searchText . '%');
                    });

                    if ($currentEmp == 1) {
                        $employeesQuery->where('added_by', Auth::user()->id)
                            ->where('ex_employee', 0)
                            ->where('non_joiner', 0);
                    } elseif ($exEmp == 1) {
                        $employeesQuery->where('ex_employee', 1)
                            ->where('non_joiner', 0);
                    } elseif ($nonJEmp == 1) {
                        $employeesQuery->where('ex_employee', 0)
                            ->where('non_joiner', 1);
                    } else{
                        $employeesQuery->where(function ($query) {
                            $query->where('added_by', Auth::user()->id)
                                ->orWhere(function ($query) {
                                    $query->where('ex_employee', 1)
                                        ->orWhere('non_joiner', 1);
                                });
                        });
                    }
        $employees = $employeesQuery->paginate(10);

        if($employees){
            return response()->json([
                'status' => true,
                'employees' => $employees,
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "No record found",
        ], 404);
    }

    public function addReview(Request $request){
        $inputValidation = Validator::make($request->all(), [
            "empId" => 'required',
            "empName" => 'required',
            "email" => 'required',
            "phone" => 'required',
            "position" => 'required',
            "dateOfJoining" => $request->dateOfJoining ? 'date' : '',
            'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'linkedIn' => $request->linkedIn ? 'url' : '',
            "exEmployee" => 'required',
            "nonJoiner" => 'required',
            "rating" => 'required',
            "review" => 'required',
            "dateOfLeaving" => $request->dateOfLeaving ? 'date' : '',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid data entered',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try{
            $added_by = Auth::user()->id;
            $image = "";

            if($request->hasFile('image')){
                $randomNumber = random_int(1000, 9999);
                $file = $request->image;
                $date = date('YmdHis');
                $filename = "IMG_" . $randomNumber . "_" . $date;
                $extension = strtolower( $file->getClientOriginalExtension() );
                $imageName = $filename . '.' . $extension;
                $uploadPath = "uploads/users/profile_images/";
                $imageUrl = $uploadPath . $imageName;
                $file->move($uploadPath, $imageName);
                $image = $imageUrl;
            }

            $employee = Employee::create([
                'emp_id' => $request->empId,
                'emp_name' => $request->empName,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'date_of_joining' => $request->dateOfJoining ?? null,
                'profile_image' => $image,
                'added_by' => $added_by,
                'date_of_birth' => $request->dateOfBirth,
                'emp_pan' => $request->pan_number,
                'permanent_address' => $request->permanentAddress,
                'city' => $request->city,
                'country' => $request->country,
                'state' => $request->state,
                'linked_in' => $request->linkedIn,
                'ex_employee' => $request->exEmployee,
                'non_joiner' => $request->nonJoiner,
                'rating' => $request->rating,
                'review' => $request->review,
                'date_of_leaving' => $request->dateOfLeaving ?? null,
                'status_changed_at' => now(),
            ]);
            if($employee){
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
}
