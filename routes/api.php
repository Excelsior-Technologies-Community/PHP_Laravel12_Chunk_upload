<?php

use App\Http\Controllers\FileChunkController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('upload')->group(function () {
    Route::post('/chunk', [FileChunkController::class, 'uploadChunk']);
    Route::post('/progress', [FileChunkController::class, 'getProgress']);
});
Route::post('/upload', [UploadController::class, 'upload']);