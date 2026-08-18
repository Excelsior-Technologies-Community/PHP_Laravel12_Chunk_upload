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

        $totalChunks = $validated['total_chunks'];

        /*
        |--------------------------------------------------------------------------
        | Store Chunk
        |--------------------------------------------------------------------------
        */

        $chunkPath = $this->getChunkPath($sessionId);

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
                $totalChunks
            )
        ) {
            return $this->assembleFile(
                $sessionId,
                $validated['filename'],
                $totalChunks
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

        $chunkPath = $this->getChunkPath($sessionId);

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
     * Verify the integrity of an uploaded file.
     *
     * Recalculates SHA-256 checksum from the physical file
     * and compares it with the checksum stored in database.
     */
    public function verify(Upload $upload)
    {
        if (!$upload->file_path) {
            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'File path is not available.',
            ], 404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($upload->file_path)) {
            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'Physical file could not be found.',
            ], 404);
        }

        try {
            $absolutePath = $disk->path(
                $upload->file_path
            );

            /*
            |--------------------------------------------------------------------------
            | Calculate Current SHA-256
            |--------------------------------------------------------------------------
            */

            $currentChecksum = hash_file(
                'sha256',
                $absolutePath
            );

            /*
            |--------------------------------------------------------------------------
            | Compare With Database
            |--------------------------------------------------------------------------
            */

            $verified = hash_equals(
                (string) $upload->checksum,
                (string) $currentChecksum
            );

            Log::info(
                'File integrity verification completed.',
                [
                    'upload_id' => $upload->id,
                    'filename' => $upload->original_name,
                    'verified' => $verified,
                ]
            );

            return response()->json([
                'success' => true,
                'verified' => $verified,
                'message' => $verified
                    ? 'File integrity verified successfully.'
                    : 'File integrity verification failed.',
                'data' => [
                    'upload_id' => $upload->id,
                    'stored_checksum' => $upload->checksum,
                    'current_checksum' => $currentChecksum,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error(
                'File integrity verification failed.',
                [
                    'upload_id' => $upload->id,
                    'filename' => $upload->original_name,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'Unable to verify file integrity.',
            ], 500);
        }
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
        | NEW: Calculate SHA-256 Checksum
        |--------------------------------------------------------------------------
        |
        | The complete assembled file is hashed here.
        |
        */

        try {
            $checksum = hash_file(
                'sha256',
                $absolutePath
            );
        } catch (Throwable $e) {
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }

            Log::error(
                'File checksum generation failed.',
                [
                    'session_id' => $sessionId,
                    'filename' => $originalFilename,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate file integrity checksum.',
            ], 500);
        }

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
                'checksum' => $checksum,
                'status' => 'completed',
            ]);
        } catch (Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | Database Failed
            |--------------------------------------------------------------------------
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
                'checksum' => $checksum,
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
            'checksum' => $checksum,
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