<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('upload');
});

/*
|--------------------------------------------------------------------------
| Upload Management
|--------------------------------------------------------------------------
*/

Route::get('/uploads', [
    UploadController::class,
    'index'
])->name('uploads.index');

Route::get('/uploads/dashboard', [
    UploadController::class,
    'dashboard'
])->name('uploads.dashboard');

Route::get('/uploads/{upload}', [
    UploadController::class,
    'show'
])->name('uploads.show');

Route::delete('/uploads/{upload}', [
    UploadController::class,
    'destroy'
])->name('uploads.destroy');
