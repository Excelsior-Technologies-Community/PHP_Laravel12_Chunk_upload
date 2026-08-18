<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Normal file upload API.
     *
     * This keeps POST /api/upload working.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:102400',
            ],
        ]);

        $file = $request->file('file');

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        $storedName = time() . '_' . $originalName;

        $filePath = $file->storeAs(
            'uploads',
            $storedName,
            'public'
        );

        $upload = Upload::create([
            'file_name' => $storedName,
            'original_name' => $originalName,
            'file_path' => $filePath,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'file_size' => $fileSize,
            'status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data' => [
                'id' => $upload->id,
                'file_name' => $upload->file_name,
                'original_name' => $upload->original_name,
                'file_size' => $upload->file_size,
                'url' => Storage::disk('public')->url($filePath),
            ],
        ]);
    }

    /**
     * Upload history with search, filters and pagination.
     */
    public function index(Request $request)
    {
        $query = Upload::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('extension', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | File Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('extension')) {
            $query->where(
                'extension',
                $request->extension
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = (int) $request->get(
            'per_page',
            5
        );

        if (!in_array(
            $perPage,
            [5, 10, 25, 50, 100],
            true
        )) {
            $perPage = 10;
        }

        $uploads = $query
            ->oldest()
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $extensions = Upload::query()
            ->whereNotNull('extension')
            ->where('extension', '!=', '')
            ->select('extension')
            ->distinct()
            ->orderBy('extension')
            ->pluck('extension');

        $statuses = Upload::query()
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = $this->getStatistics();

        return view(
            'uploads.index',
            compact(
                'uploads',
                'extensions',
                'statuses',
                'statistics',
                'perPage'
            )
        );
    }

    /**
     * Statistics dashboard.
     */
    public function dashboard()
    {
        $statistics = $this->getStatistics();

        /*
        |--------------------------------------------------------------------------
        | File Type Statistics
        |--------------------------------------------------------------------------
        */

        $fileTypes = Upload::query()
            ->selectRaw(
                'extension, COUNT(*) as total'
            )
            ->whereNotNull('extension')
            ->where('extension', '!=', '')
            ->groupBy('extension')
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Uploads
        |--------------------------------------------------------------------------
        */

        $recentUploads = Upload::latest()
            ->limit(10)
            ->get();

        return view(
            'uploads.dashboard',
            compact(
                'statistics',
                'fileTypes',
                'recentUploads'
            )
        );
    }

    /**
     * Display uploaded file details.
     */
    public function show(Upload $upload)
    {
        return view('uploads.show', compact('upload'));
    }

    /**
     * Delete uploaded file.
     */
    public function destroy(Upload $upload)
    {
        /*
        |--------------------------------------------------------------------------
        | Delete physical file
        |--------------------------------------------------------------------------
        */

        if (
            $upload->file_path &&
            Storage::disk('public')->exists(
                $upload->file_path
            )
        ) {
            Storage::disk('public')->delete(
                $upload->file_path
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Delete database record
        |--------------------------------------------------------------------------
        */

        $upload->delete();

        return redirect()
            ->route('uploads.index')
            ->with(
                'success',
                'Uploaded file deleted successfully.'
            );
    }

    /**
     * Get upload statistics.
     */
    private function getStatistics(): array
    {
        $totalFiles = Upload::count();

        $completedFiles = Upload::where(
            'status',
            'completed'
        )->count();

        $failedFiles = Upload::where(
            'status',
            'failed'
        )->count();

        $todayFiles = Upload::whereDate(
            'created_at',
            today()
        )->count();

        $totalSize = Upload::sum(
            'file_size'
        );

        return [
            'total_files' => $totalFiles,
            'completed_files' => $completedFiles,
            'failed_files' => $failedFiles,
            'today_files' => $todayFiles,
            'total_size' => $this->formatBytes(
                $totalSize
            ),
        ];
    }

    /**
     * Format bytes.
     */
    private function formatBytes($bytes): string
    {
        if ($bytes <= 0) {
            return '0 Bytes';
        }

        $units = [
            'Bytes',
            'KB',
            'MB',
            'GB',
            'TB',
        ];

        $power = floor(
            log($bytes, 1024)
        );

        $power = min(
            $power,
            count($units) - 1
        );

        return round(
            $bytes / pow(1024, $power),
            2
        ) . ' ' . $units[$power];
    }
}
