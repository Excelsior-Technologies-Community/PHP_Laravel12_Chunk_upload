<?php

use App\Http\Controllers\FileChunkController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('upload')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Custom Chunk Upload
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/chunk',
        [FileChunkController::class, 'uploadChunk']
    );

    Route::post(
        '/progress',
        [FileChunkController::class, 'getProgress']
    );
});

/*
|--------------------------------------------------------------------------
| File Integrity Verification
|--------------------------------------------------------------------------
*/

Route::get(
    '/uploads/{upload}/verify',
    [FileChunkController::class, 'verify']
)->name('api.uploads.verify');

/*
|--------------------------------------------------------------------------
| Package Upload
|--------------------------------------------------------------------------
*/

Route::post(
    '/upload',
    [UploadController::class, 'upload']
);