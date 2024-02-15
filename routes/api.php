<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentsController;


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

//Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


//Protected routes
Route::middleware('auth:sanctum')->group(function () {

    //Auth Controller
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    //User Controller
    Route::apiResource('/users', UserController::class);

    //Student Controller
    Route::POST("/students", [StudentsController::class, "update"]);
    Route::GET("/students", [StudentsController::class, "index"]);
    Route::GET("/students/{id}", [StudentsController::class, "get"]);
    Route::DELETE("/students/{id}", [StudentsController::class, "softDelete"]);
    
});