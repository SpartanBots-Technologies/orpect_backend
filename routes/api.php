<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuperAdminController;

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
Route::group(['prefix'=>'admin'],function(){
    Route::post('/loginAdmin', [AuthController::class, 'loginAdmin']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPasswordAdmin']);
    Route::post('/reset-password', [AuthController::class, 'resetPasswordAdmin']);
});

Route::post('/sendVerificationOtp', [AuthController::class, 'sendEmailVerificationOtp']);
Route::get('/checkOtp', [AuthController::class, 'checkOtp']);
Route::get('/checkDomain', [AuthController::class, 'checkDomain']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotpassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('isTokenValid', [AuthController::class, 'isTokenValid']);
Route::get('getDesignations', [SuperAdminController::class, 'getDesignations']);
Route::get('getCompanyTypes', [SuperAdminController::class, 'getCompanyTypes']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logoutUser']);
    
    Route::post('/addEmployee', [EmployeeController::class, 'addEmployee']);
    Route::post('/uploadCSV', [EmployeeController::class, 'uploadEmployeeUsingCSV']);
    Route::get('/getCurrentEmployees', [EmployeeController::class, 'getCurrentEmployees']);
    Route::get('/getEmployeeById/{id}', [EmployeeController::class, 'getEmployeeById']);
    Route::get('/getExEmployees', [EmployeeController::class, 'getExEmployees']);
    Route::get('/getNonJoiners', [EmployeeController::class, 'getNonJoiners']);
    Route::post('/updateEmployee/{id}', [EmployeeController::class, 'updateEmployee']);
    Route::post('/updateEmployeeImage/{id}', [EmployeeController::class, 'updateEmployeeImage']);
    Route::delete('/deleteEmployee/{id}', [EmployeeController::class, 'deleteEmployee']);
    Route::post('/addReview', [EmployeeController::class, 'addReview']);
    Route::post('/rateAndReview/{id}', [EmployeeController::class, 'rateAndReview']);
    Route::get('/searchEmployeeGlobally', [EmployeeController::class, 'searchEmployeeGlobally']);
    Route::get('/getTotalEmployees/{id}', [EmployeeController::class, 'getTotalEmployees']);
    
    Route::get('/getUser', [UserController::class, 'getUser']);
    Route::post('/addPositions', [UserController::class, 'addPositions']);
    Route::post('/updatePosition/{id}', [UserController::class, 'updatePosition']);
    Route::get('/getPositions', [UserController::class, 'getPositions']);
    Route::delete('/removePosition/{id}', [UserController::class, 'removePosition']);
    Route::post('/updateProfile', [UserController::class, 'updateProfile']);
    Route::post('/updateUserImage', [UserController::class, 'updateUserImage']);
    Route::post('/updatePassword', [UserController::class, 'updateUserPassword']);
    // Route::middleware('auth.admin')->group(function () {
    // });
    Route::group(['prefix'=>'admin'],function(){
        Route::post('/logoutAdmin', [AuthController::class, 'logoutAdmin']);
        Route::post('/addAdmin', [SuperAdminController::class, 'addAdmin']);
        Route::post('/updateAdmin/{id}', [SuperAdminController::class, 'updateAdmin']);
        Route::post('/updateAdminPassword', [SuperAdminController::class, 'updateAdminPassword']);
        Route::get('/getAllAdmins', [SuperAdminController::class, 'getAllAdmins']);
        Route::get('/getAdminById/{id}', [SuperAdminController::class, 'getAdminById']);

        Route::get('/getCompanies', [UserController::class, 'getCompanies']);
        Route::get('/getCompanyById/{id}', [UserController::class, 'getCompanyById']);
        Route::delete('/deleteCompany/{id}', [UserController::class, 'deleteCompany']);
    });
});
