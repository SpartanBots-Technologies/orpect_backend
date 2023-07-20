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

//Employee login, signup, forget
Route::post('/registerEmployee', [AuthController::class, 'registerEmployee']);
Route::post('/sendOTPEmployee', [AuthController::class, 'sendOTPEmployee']);

Route::group(['prefix'=>'admin'],function(){
    Route::post('/loginAdmin', [AuthController::class, 'loginAdmin']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPasswordAdmin']);
    Route::post('/reset-password', [AuthController::class, 'resetPasswordAdmin']);
    Route::post('/isTokenValid', [AuthController::class, 'isTokenValid']);
});

Route::post('/sendVerificationOtp', [AuthController::class, 'sendEmailVerificationOtp']);
Route::get('/checkOtp', [AuthController::class, 'checkOtp']);
Route::get('/checkDomain', [AuthController::class, 'checkDomain']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotpassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('isTokenValid', [AuthController::class, 'isTokenValid']);
Route::post('/sendSupportMail', [AuthController::class, 'sendSupportMail']);
Route::get('getDesignations', [SuperAdminController::class, 'getDesignations']);
Route::get('getCompanyTypes', [SuperAdminController::class, 'getCompanyTypes']);

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['middleware' => 'user'],function(){
        Route::post('/logout', [AuthController::class, 'logoutUser']);

        Route::post('/addEmployee', [EmployeeController::class, 'addEmployee']);
        Route::post('/uploadCSV', [EmployeeController::class, 'uploadEmployeeUsingCSV']);
        Route::get('/getCurrentEmployees', [EmployeeController::class, 'getCurrentEmployees']);
        Route::get('/getEmployeeById/{id}', [EmployeeController::class, 'getEmployeeById'])->middleware('separateNameAndId');
        Route::get('/getExEmployees', [EmployeeController::class, 'getExEmployees']);
        Route::get('/getNonJoiners', [EmployeeController::class, 'getNonJoiners']);
        Route::post('/updateEmployee/{id}', [EmployeeController::class, 'updateEmployee'])->middleware('separateNameAndId');
        Route::post('/updateEmployeeImage/{id}', [EmployeeController::class, 'updateEmployeeImage'])->middleware('separateNameAndId');
        Route::delete('/deleteEmployee/{id}', [EmployeeController::class, 'deleteEmployee'])->middleware('separateNameAndId');
        Route::post('/addReview', [EmployeeController::class, 'addReview']);
        Route::post('/rateAndReview/{id}', [EmployeeController::class, 'rateAndReview'])->middleware('separateNameAndId');
        Route::get('/searchEmployeeGlobally', [EmployeeController::class, 'searchEmployeeGlobally']);
        Route::get('/getTotalEmployees/{id}', [EmployeeController::class, 'getTotalEmployees'])->middleware('separateNameAndId');
        Route::get('/viewGlobalSearchedEmp/{id}', [EmployeeController::class, 'viewReviewGlobalSearch'])->middleware('separateNameAndId');
        Route::get('/getExEmployeesAndNonJoiners', [EmployeeController::class, 'getExEmployeesAndNonJoiners']);

        Route::get('/getUser', [UserController::class, 'getUser']);
        Route::post('/addPositions', [UserController::class, 'addPositions']);
        Route::post('/updatePosition/{id}', [UserController::class, 'updatePosition']);
        Route::get('/getPositions', [UserController::class, 'getPositions']);
        Route::delete('/removePosition/{id}', [UserController::class, 'removePosition']);
        Route::get('/getPositionAlreadyInUse/{position}', [UserController::class, 'getPositionAlreadyInUse']);
        Route::post('/updateProfile', [UserController::class, 'updateProfile']);
        Route::post('/updateUserImage', [UserController::class, 'updateUserImage']);
        Route::post('/updatePassword', [UserController::class, 'updateUserPassword']);
    });

    //apis for admin protected with admin middleware and sanctum
    Route::group(['prefix'=>'admin', 'middleware' => 'admin'],function(){
        Route::post('/logoutAdmin', [AuthController::class, 'logoutAdmin']);
        Route::get('/getAdmin', [SuperAdminController::class, 'getAdmin']);
        Route::get('/searchGloballyAdmin', [SuperAdminController::class, 'searchGloballyAdmin']);
        Route::get('/getDashboardTotals', [SuperAdminController::class, 'dashboardWidgetCounts']);
        Route::post('/updateProfileAdmin', [SuperAdminController::class, 'updateProfileAdmin']);

        Route::group(['middleware' => 'admin.is_master'],function(){
            Route::post('/addAdmin', [SuperAdminController::class, 'addAdmin']);
            Route::post('/updateAdmin/{id}', [SuperAdminController::class, 'updateSubAdmin'])->middleware('separateNameAndId');
            Route::post('/updateAdminPassword', [SuperAdminController::class, 'updateAdminPassword']);
            Route::post('/updateSubAdminPassword/{id}', [SuperAdminController::class, 'updateSubAdminPassword'])->middleware('separateNameAndId');
            Route::get('/getAllAdmins', [SuperAdminController::class, 'getAllAdmins']);
            Route::get('/getAdminById/{id}', [SuperAdminController::class, 'getAdminById'])->middleware('separateNameAndId');
            Route::delete('/deleteAdmin/{id}', [SuperAdminController::class, 'deleteAdmin'])->middleware('separateNameAndId');
        });

        Route::get('/getEmpReviewForAdmin/{id}', [EmployeeController::class, 'getEmpReviewForAdmin'])->middleware('separateNameAndId');
        Route::get('/getCurrentEmployees', [EmployeeController::class, 'getCurrentEmployees']);
        Route::get('/getEmployeeById/{id}', [EmployeeController::class, 'getEmployeeByIdForAdmin'])->middleware('separateNameAndId');
        Route::get('/getExEmployees', [EmployeeController::class, 'getExEmployees']);
        Route::get('/getNonJoiners', [EmployeeController::class, 'getNonJoiners']);
        Route::delete('/deleteEmployeeByAdmin/{id}', [EmployeeController::class, 'deleteEmployee'])->middleware('separateNameAndId');

        Route::get('/getCompanies', [UserController::class, 'getCompanies']);
        Route::get('/getCompanyById/{id}', [UserController::class, 'getCompanyById'])->middleware('separateNameAndId');
        Route::delete('/deleteCompany/{id}', [UserController::class, 'deleteCompany'])->middleware('separateNameAndId');
        Route::delete('/rejectCompany/{id}', [UserController::class, 'rejectCompany'])->middleware('separateNameAndId');
        Route::post('/verifyCompany/{id}', [UserController::class, 'verifyCompany'])->middleware('separateNameAndId');
        Route::get('/getPendingVerificationRequests', [UserController::class, 'getPendingVerificationRequests']);
        Route::get('/getDeletedCompanies', [UserController::class, 'getDeletedCompanies']);
        Route::post('/restoreCompany/{id}', [UserController::class, 'restoreCompany'])->middleware('separateNameAndId');
        Route::delete('/permanentlyDeleteCompany/{id}', [UserController::class, 'permanentlyDeleteCompany'])->middleware('separateNameAndId');
    });
});