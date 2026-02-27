# PHP_Laravel12_Chunk_upload
Complete step-by-step implementation of large file uploads in Laravel 12 using chunk upload technique.

This project demonstrates two different approaches:

1. Custom Chunk Upload Implementation
2. Using pion/laravel-chunk-upload Package

Both methods support large file uploads, resume functionality, and progress tracking.

---

## Prerequisites

* PHP 8.2 or higher
* Composer installed
* Laravel 12 installed

---

# Method 1: Custom Chunk Upload Implementation

This approach provides full control over upload logic and file assembly.

## Step 1: Create a New Laravel Project

```bash
composer create-project laravel/laravel laravel12-chunk-upload
cd laravel12-chunk-upload
```

---

## Step 2: Create the Controller

```bash
php artisan make:controller FileChunkController
```

---

## Step 3: Configure the Controller

Edit:

app/Http/Controllers/FileChunkController.php

The controller should handle:

* Uploading individual chunks
* Checking upload progress
* Verifying all chunks are uploaded
* Assembling the final file
* Cleaning temporary chunk files

Storage Structure:

* Chunks stored in: storage/app/temp/chunks
* Final file stored in: storage/app/public/uploads
* Temporary chunk directory deleted after assembly

---

## Step 4: Set Up API Routes

Edit routes/api.php:

```php
use App\Http\Controllers\FileChunkController;
use Illuminate\Support\Facades\Route;

Route::prefix('upload')->group(function () {
    Route::post('/chunk', [FileChunkController::class, 'uploadChunk']);
    Route::post('/progress', [FileChunkController::class, 'getProgress']);
});
```

---

## Step 5: Create Frontend Blade View

Create:

resources/views/upload.blade.php

Frontend Features:

* File input
* 1MB chunk splitting
* Progress bar
* Cancel upload button
* Resume support
* Success and error handling

Chunks uploaded to:

/api/upload/chunk

Progress checked via:

/api/upload/progress

---

## Step 6: Add Web Route

Edit routes/web.php:

```php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('upload');
});
```

---

## Step 7: Create Storage Symlink

```bash
php artisan storage:link
```

This makes uploaded files publicly accessible.

---

# Method 2: Using pion/laravel-chunk-upload Package

This method uses a maintained Laravel package for chunk handling.

## Step 1: Install the Package

```bash
composer require pion/laravel-chunk-upload
```

---

## Step 2: Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Pion\Laravel\ChunkUpload\Providers\ChunkUploadServiceProvider"
```

---

## Step 3: Create Upload Controller

```bash
php artisan make:controller UploadController
```

Edit:

app/Http/Controllers/UploadController.php

Controller Responsibilities:

* Use FileReceiver
* Automatically detect chunks
* Save final file to public disk
* Delete temporary chunks
* Return upload progress percentage

---

## Step 4: Add API Route

Edit routes/api.php:

```php
use App\Http\Controllers\UploadController;

Route::post('/upload', [UploadController::class, 'upload']);
```

---

## Step 5: Frontend Using Resumable.js

Install via NPM:

```bash
npm install resumablejs
```

Or include via CDN in Blade view.

Create:

resources/views/upload-package.blade.php

Frontend Features:

* Drag and drop upload area
* Automatic chunking
* Parallel uploads
* Progress badge per file
* Success and error UI
* Large file support up to server limits

---

# Testing the Implementation

## Start Laravel Server

```bash
php artisan serve
```

Custom Version:

[http://localhost:8000](http://localhost:8000)
<img width="1271" height="552" alt="image" src="https://github.com/user-attachments/assets/1c4bae37-118f-44af-a7be-480ee90ee49f" />

Package Version:

[http://localhost:8000/upload-package](http://localhost:8000/upload-package)

---

## Test with Postman

### Custom Implementation

Method: POST
URL: [http://localhost:8000/api/upload/chunk](http://localhost:8000/api/upload/chunk)
Body: form-data
Field name: chunk

---

### Package Implementation

Method: POST
URL: [http://localhost:8000/api/upload](http://localhost:8000/api/upload)
Body: form-data
Field name: file

---

# Project Structure

```
laravel12-chunk-upload/
├── app/
│   └── Http/
│       └── Controllers/
│           ├── FileChunkController.php
│           └── UploadController.php
├── resources/
│   └── views/
│       ├── upload.blade.php
│       └── upload-package.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
│   ├── app/
│   │   ├── public/uploads/
│   │   └── temp/chunks/
│   └── logs/
└── public/
    └── storage/
```

---

# Key Features

Chunked uploads
Files are split into smaller pieces before uploading.

Resume capability
Interrupted uploads continue from the last uploaded chunk.

Progress tracking
Real-time upload progress monitoring.

Low memory usage
Entire file is never loaded into memory.

Validation support
Supports file type and size validation.

Automatic cleanup
Temporary chunks deleted after final assembly.

---

# Notes

The custom implementation provides greater flexibility but requires more manual handling.

The package approach is easier, robust, and integrates well with frontend libraries like Resumable.js and Dropzone.

Both methods support large file uploads up to server configuration limits.

Final uploaded files are stored in:

storage/app/public/uploads

Run the following to make files publicly accessible:

```bash
php artisan storage:link
```

Choose the method that best fits your project requirements and scalability needs.

---

## License

MIT License

---

## Author

Mihir Mehta
Laravel Developer
