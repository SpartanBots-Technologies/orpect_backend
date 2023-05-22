<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/sendVerificationOtp', [AuthController::class, 'sendEmailVerificationOtp']);
Route::get('/checkOtp', [AuthController::class, 'checkOtp']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotpassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('isTokenValid', [AuthController::class, 'isTokenValid']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logoutUser']);

    Route::post('/addEmployee', [EmployeeController::class, 'addEmployee']);
    Route::post('/uploadCSV', [EmployeeController::class, 'uploadEmployeeUsingCSV']);
    Route::get('/getCurrentEmployees', [EmployeeController::class, 'getCurrentEmployees']);
    Route::get('/getEmployeeById/{id}', [EmployeeController::class, 'getEmployeeById']);
    Route::get('/getExEmployees', [EmployeeController::class, 'getExEmployees']);
    Route::get('/getNonJoiners', [EmployeeController::class, 'getNonJoiners']);
    Route::post('/updateEmployee/{id}', [EmployeeController::class, 'updateEmployee']);
    Route::delete('/deleteEmployee/{id}', [EmployeeController::class, 'deleteEmployee']);
    Route::post('/rateAndReview/{id}', [EmployeeController::class, 'rateAndReview']);
    Route::get('/searchEmployeeGlobally', [EmployeeController::class, 'searchEmployeeGlobally']);
    
    Route::get('/getUser', [UserController::class, 'getUser']);
    Route::post('/addPositions', [UserController::class, 'addPositions']);
    Route::get('/getPositions', [UserController::class, 'getPositions']);
    Route::delete('/removePosition/{id}', [UserController::class, 'removePosition']);
    Route::post('/updateProfile', [UserController::class, 'updateProfile']);
    Route::post('/updatePassword', [UserController::class, 'updateUserPassword']);
    // Route::middleware('auth.admin')->group(function () {
    // });
});