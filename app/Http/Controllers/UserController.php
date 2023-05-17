<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Position;

class UserController extends Controller
{
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
