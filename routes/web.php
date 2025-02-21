<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('generate-otp', [AuthController::class, 'generate'])->name('generate.otp');
Route::get('otp/verification/{user_id}', [AuthController::class, 'verify'])->name('verify');
Route::post('login-with-otp', [AuthController::class, 'loginWithOtp'])->name('login.otp');