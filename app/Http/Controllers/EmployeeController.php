<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
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
            'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
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
            $image = "";
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
                'emp_id' => $request->empId,
                'emp_name' => $request->empName,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'date_of_joining' => $request->dateOfJoining,
                'profile_image' => $image,
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

    public function uploadEmployeeUsingCSV(Request $request){
        $inputValidation = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv',
        ]);
        if($inputValidation->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Please upload file of type CSV',
                'errors' => $inputValidation->errors(),
            ], 422);
        }
        try {
            $added_by = Auth::user()->id;
            $image = '';
            $imgFolderPath = $request->image_path;
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
    
                if ($lineNumber === 1) {
                    continue;
                }
    
                $emp_id = $data[1];
                $emp_name = $data[2];
                $emp_email = $data[3];
                $emp_phone = $data[4];
                $emp_position = $data[5];
                $emp_doj = $data[6];
                $emp_image = $data[7];
    
                if ($emp_id != "" && $emp_name != "" && $emp_email != "" && $emp_phone != "") {
                    if ($imgFolderPath != "" && $emp_image != "") {
                        $imagePath = $imgFolderPath . '/' . $emp_image;
                        // dump(file_exists($imagePath));
                        // dump(is_readable($imagePath));
                        // dd($imagePath);
                        // dump(Storage::get($imagePath));
                        // dump(file_get_contents($imagePath));
                        // dd($imagePath);
                        if (file_exists($imagePath) && is_readable($imagePath)) {
                            $randomNumber = random_int(100000, 999999);
                            $date = date('YmdHis');
                            $filename = "IMG_" . $randomNumber . "_" . $date;
                            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                            $imageName = $filename . '.' . $extension;
                            $uploadPath = "uploads/users/profile_images/";
                            $image = $uploadPath . $imageName;
    
                            Storage::put($image, file_get_contents($imagePath));
                        }
                    }
    
                    $employee = Employee::create([
                        'emp_id' => $emp_id,
                        'emp_name' => $emp_name,
                        'email' => $emp_email,
                        'phone' => $emp_phone,
                        'position' => $emp_position,
                        'date_of_joining' => $emp_doj,
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

    // dd($allCurrentEmployees);
        // $searchValue = "";
        // $dropdown = "";
        // if( $request->has('searchText') ){
        //     $searchValue = $request->searchText;
        // }
        // if( $request->has('dropdown') ){
        //     $dropdown = $request->dropdown;
        // }
        // $allCurrentEmployees = Employee::where('added_by', '=', Auth::user()->id)
        //                         ->where('ex_employee', '=', 0)
        //                         ->where('non_joiner', '=', 0)
        //                         ->where('is_deleted', '=', 0)
        //                         ->orderBy('created_at', 'desc')
        //                         ->paginate(10);
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

    public function getExEmployees(){
        $employeeDetail = Employee::where('added_by', '=', Auth::user()->id)
                                ->where('ex_employee', '=', 1)
                                ->where('non_joiner', '<>', 1)
                                ->where('is_deleted', '=', 0)
                                ->orderBy('date_of_leaving', 'desc')
                                ->paginate(10);
        return response()->json([
            'status' => true,
            'exEmployee' => $employeeDetail,
        ], 200);
    }

    public function getNonJoiners(){
        $employeeDetail = Employee::where('added_by', '=', Auth::user()->id)
                                ->where('non_joiner', '=', 1)
                                ->where('ex_employee', '<>', 1)
                                ->where('is_deleted', '=', 0)
                                ->orderBy('date_of_leaving', 'desc')
                                ->paginate(10);
        return response()->json([
            'status' => true,
            'nonJoiners' => $employeeDetail,
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
        $employees = Employee::where(function ($query) use ($searchText) {
                        $query->where('emp_name', 'like', '%' . $searchText . '%')
                            ->orWhere('email', 'like', '%' . $searchText . '%')
                            ->orWhere('phone', 'like', '%' . $searchText . '%');
                    })
                    ->where(function ($query) {
                        $query->where('ex_employee', 1)
                            ->orWhere('non_joiner', 1);
                    })
                    ->paginate(12);

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
}
