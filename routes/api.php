<?php

use App\Http\Controllers\PasswordManagement;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(UserController::class)->group(function(){
    Route::match(["post", "get"],"/register", "newUser");
    Route::match(["post", "get"],"/login", "login")->name("login");
    
    // proctected routes
    Route::middleware(["auth:sanctum"])->group(function(){
        Route::post("/logout", "logout");
        Route::post("/authUserData", "logged_user");
        Route::post("/change-password", "changePassword");
    }); 
});

Route::controller(PasswordManagement::class)->group(function(){
    Route::match(["get","post"],"password_change_mail",'PassChangeMail');
    Route::match(["get","post"],"reset-password/{token}",'resetPassword');
});
