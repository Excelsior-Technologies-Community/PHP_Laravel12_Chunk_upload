<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FileChunkController extends Controller
{
    private const CHUNK_STORAGE_PATH = 'temp/chunks';

    private const FINAL_STORAGE_PATH = 'uploads';

    /**
     * Upload a single chunk.
     */
    public function uploadChunk(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string|max:255',
            'chunk_number' => 'required|integer|min:1',
            'total_chunks' => 'required|integer|min:1',
            'chunk' => 'required|file',
            'filename' => 'required|string|max:255',
        ]);

        $sessionId = $validated['session_id'];

        $chunkNumber = $validated['chunk_number'];

        $chunkPath = $this->getChunkPath(
            $sessionId
        );

        /*
        |--------------------------------------------------------------------------
        | Store Chunk
        |--------------------------------------------------------------------------
        */

        $chunkFile = $request->file('chunk');

        Storage::disk('local')->put(
            "{$chunkPath}/chunk_{$chunkNumber}",
            file_get_contents(
                $chunkFile->getRealPath()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Check Whether All Chunks Exist
        |--------------------------------------------------------------------------
        */

        if (
            $this->hasAllChunks(
                $sessionId,
                $validated['total_chunks']
            )
        ) {
            return $this->assembleFile(
                $sessionId,
                $validated['filename'],
                $validated['total_chunks']
            );
        }

        return response()->json([
            'success' => true,
            'chunk' => $chunkNumber,
            'message' => 'Chunk uploaded successfully.',
        ]);
    }

    /**
     * Get upload progress.
     */
    public function getProgress(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string|max:255',
        ]);

        $sessionId = $validated['session_id'];

        $chunkPath = $this->getChunkPath(
            $sessionId
        );

        if (
            !Storage::disk('local')->exists(
                $chunkPath
            )
        ) {
            return response()->json([
                'uploaded_chunks' => [],
                'total_uploaded' => 0,
            ]);
        }

        $files = Storage::disk('local')->files(
            $chunkPath
        );

        $uploadedChunks = collect($files)
            ->map(function ($file) {
                preg_match(
                    '/chunk_(\d+)$/',
                    $file,
                    $matches
                );

                return isset($matches[1])
                    ? (int) $matches[1]
                    : null;
            })
            ->filter()
            ->sort()
            ->values();

        return response()->json([
            'uploaded_chunks' => $uploadedChunks,
            'total_uploaded' => $uploadedChunks->count(),
        ]);
    }

    /**
     * Check whether all chunks are uploaded.
     */
    private function hasAllChunks(
        string $sessionId,
        int $expectedTotal
    ): bool {
        $chunkPath = $this->getChunkPath(
            $sessionId
        );

        if (
            !Storage::disk('local')->exists(
                $chunkPath
            )
        ) {
            return false;
        }

        $files = Storage::disk('local')->files(
            $chunkPath
        );

        /*
        |--------------------------------------------------------------------------
        | Count only actual chunk files
        |--------------------------------------------------------------------------
        */

        $chunkCount = collect($files)
            ->filter(function ($file) {
                return preg_match(
                    '/chunk_\d+$/',
                    $file
                );
            })
            ->count();

        return $chunkCount === $expectedTotal;
    }

    /**
     * Assemble all chunks into the final file.
     */
    private function assembleFile(
        string $sessionId,
        string $filename,
        int $totalChunks
    ) {
        $chunkPath = $this->getChunkPath(
            $sessionId
        );

        /*
        |--------------------------------------------------------------------------
        | Sanitize Filename
        |--------------------------------------------------------------------------
        */

        $originalFilename = basename(
            $filename
        );

        $safeFilename = preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            $originalFilename
        );

        /*
        |--------------------------------------------------------------------------
        | Prevent Empty Filename
        |--------------------------------------------------------------------------
        */

        if (!$safeFilename) {
            $safeFilename = 'uploaded_file';
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Unique Filename
        |--------------------------------------------------------------------------
        */

        $storedFilename = time()
            . '_'
            . uniqid()
            . '_'
            . $safeFilename;

        $finalPath = self::FINAL_STORAGE_PATH
            . '/'
            . $storedFilename;

        /*
        |--------------------------------------------------------------------------
        | Final Physical Path
        |--------------------------------------------------------------------------
        */

        $disk = Storage::disk('public');

        $absolutePath = $disk->path(
            $finalPath
        );

        /*
        |--------------------------------------------------------------------------
        | Make Sure Directory Exists
        |--------------------------------------------------------------------------
        */

        $directory = dirname(
            $absolutePath
        );

        if (!is_dir($directory)) {
            mkdir(
                $directory,
                0755,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Assemble Binary File
        |--------------------------------------------------------------------------
        |
        | We use fopen/fwrite instead of Storage::append().
        | This prevents extra line breaks from being inserted
        | into binary files such as images, videos and PDFs.
        |
        */

        $output = fopen(
            $absolutePath,
            'wb'
        );

        if (!$output) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create final file.',
            ], 500);
        }

        try {
            for (
                $i = 1;
                $i <= $totalChunks;
                $i++
            ) {
                $chunkFilePath = Storage::disk('local')
                    ->path(
                        "{$chunkPath}/chunk_{$i}"
                    );

                if (!file_exists($chunkFilePath)) {
                    fclose($output);

                    if (file_exists($absolutePath)) {
                        unlink($absolutePath);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => "Chunk {$i} is missing.",
                    ], 422);
                }

                $input = fopen(
                    $chunkFilePath,
                    'rb'
                );

                if (!$input) {
                    fclose($output);

                    if (file_exists($absolutePath)) {
                        unlink($absolutePath);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => "Unable to read chunk {$i}.",
                    ], 500);
                }

                while (!feof($input)) {
                    $buffer = fread(
                        $input,
                        1024 * 1024
                    );

                    if ($buffer !== false) {
                        fwrite(
                            $output,
                            $buffer
                        );
                    }
                }

                fclose($input);
            }

            fclose($output);
        } catch (Throwable $e) {

            fclose($output);

            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }

            Log::error(
                'Chunk assembly failed.',
                [
                    'session_id' => $sessionId,
                    'filename' => $originalFilename,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to assemble uploaded file.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | File Information
        |--------------------------------------------------------------------------
        */

        $fileSize = filesize(
            $absolutePath
        );

        $mimeType = mime_content_type(
            $absolutePath
        ) ?: 'application/octet-stream';

        $extension = pathinfo(
            $originalFilename,
            PATHINFO_EXTENSION
        );

        /*
        |--------------------------------------------------------------------------
        | Save Upload History
        |--------------------------------------------------------------------------
        */

        try {

            $upload = Upload::create([
                'file_name' => $storedFilename,
                'original_name' => $originalFilename,
                'file_path' => $finalPath,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'file_size' => $fileSize,
                'status' => 'completed',
            ]);
        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Database Failed
            |--------------------------------------------------------------------------
            |
            | Delete the physical file so we don't leave an
            | untracked file in storage.
            |
            */

            if (
                $disk->exists($finalPath)
            ) {
                $disk->delete($finalPath);
            }

            Log::error(
                'Upload history could not be saved.',
                [
                    'session_id' => $sessionId,
                    'filename' => $originalFilename,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'File uploaded but upload history could not be saved.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | Clean Temporary Chunks
        |--------------------------------------------------------------------------
        */

        Storage::disk('local')
            ->deleteDirectory(
                $chunkPath
            );

        /*
        |--------------------------------------------------------------------------
        | Log
        |--------------------------------------------------------------------------
        */

        Log::info(
            'File assembled successfully.',
            [
                'session_id' => $sessionId,
                'filename' => $originalFilename,
                'stored_filename' => $storedFilename,
                'chunks' => $totalChunks,
                'upload_id' => $upload->id,
                'size' => $fileSize,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'upload_id' => $upload->id,
            'file_name' => $originalFilename,
            'size' => $fileSize,
            'url' => $disk->url($finalPath),
        ]);
    }

    /**
     * Get storage path for chunks.
     */
    private function getChunkPath(
        string $sessionId
    ): string {
        return self::CHUNK_STORAGE_PATH
            . '/'
            . $sessionId;
    }
}
