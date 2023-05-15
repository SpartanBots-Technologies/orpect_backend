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

    public function getCurrentEmployees(){
        $allCurrentEmplyees = Employee::where('added_by', '=', Auth::user()->id)
                                ->where('ex_employee', '<>', 1)
                                ->where('non_joiner', '<>', 1)
                                ->where('is_deleted', '=', 0)
                                ->orderBy('created_at', 'desc')
                                ->get();
        return response()->json([
            'status' => true,
            'currentEmployees' => $allCurrentEmplyees,
        ], 200);
    }

    public function getEmployeeById(string $id){
        $employeeDetail = Employee::where('added_by', '=', Auth::user()->id)
                                ->where('is_deleted', '=', 0)
                                ->where('id', '=', $id)
                                ->get();
        return response()->json([
            'status' => true,
            'employee' => $employeeDetail,
        ], 200);
    }

    public function getExEmployees(){
        $employeeDetail = Employee::where('added_by', '=', Auth::user()->id)
                                ->where('ex_employee', '=', 1)
                                ->where('non_joiner', '<>', 1)
                                ->where('is_deleted', '=', 0)
                                ->orderBy('date_of_leaving', 'desc')
                                ->get();
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
                                ->get();
        return response()->json([
            'status' => true,
            'nonJoiners' => $employeeDetail,
        ], 200);
    }
}
