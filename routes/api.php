<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;

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

    // Route::middleware('auth.admin')->group(function () {
    // });
});