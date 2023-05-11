<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
