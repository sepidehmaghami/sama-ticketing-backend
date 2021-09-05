<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [LoginController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
Route::put('/user/{id}', [LoginController::class, 'updateUser'])->middleware('auth:sanctum');
Route::get('/user', [LoginController::class, 'getUser'])->middleware('auth:sanctum');
Route::get('/userslist', [LoginController::class, 'getUsersList'])->middleware('auth:sanctum');
Route::delete('/deleteUser', [LoginController::class, 'deleteUser'])->middleware('auth:sanctum');
Route::post('/password_reset_email', [LoginController::class, 'passwordResetEmail']);
Route::post('/reset_password', [LoginController::class, 'resetPassword']);
Route::get('/report', [LoginController::class, 'report'])->middleware('auth:sanctum');

Route::get('/ticket', [TicketController::class, 'list'])->middleware('auth:sanctum');
Route::get('/ticket/{id}', [TicketController::class, 'show'])->middleware('auth:sanctum');
Route::post('/ticket', [TicketController::class, 'store'])->middleware('auth:sanctum');
Route::put('/ticket/{id}', [TicketController::class, 'update'])->middleware('auth:sanctum');
// Route::delete('/ticket/{id}', [TicketController::class, 'delete'])->middleware('auth:sanctum');