<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileChunkController extends Controller
{
    private const CHUNK_STORAGE_PATH = 'temp/chunks';
    private const FINAL_STORAGE_PATH = 'uploads';

    /**
     * Upload a single chunk
     */
    public function uploadChunk(Request $request)
    {
        $validated = $request->validate([
            'session_id'    => 'required|string|max:255',
            'chunk_number'  => 'required|integer|min:1',
            'total_chunks'  => 'required|integer|min:1',
            'chunk'         => 'required|file',
            'filename'      => 'required|string|max:255',
        ]);

        $sessionId = $validated['session_id'];
        $chunkNumber = $validated['chunk_number'];
        $chunkPath = $this->getChunkPath($sessionId);

        // Store the chunk
        $chunkFile = $request->file('chunk');
        Storage::disk('local')->put(
            "{$chunkPath}/chunk_{$chunkNumber}",
            file_get_contents($chunkFile->getRealPath())
        );

        // Check if we have all chunks
        if ($this->hasAllChunks($sessionId, $validated['total_chunks'])) {
            return $this->assembleFile($sessionId, $validated['filename'], $validated['total_chunks']);
        }

        return response()->json([
            'success' => true,
            'chunk' => $chunkNumber,
            'message' => 'Chunk uploaded successfully'
        ]);
    }

    /**
     * Get upload progress (which chunks are already uploaded)
     */
    public function getProgress(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string|max:255',
        ]);

        $sessionId = $validated['session_id'];
        $chunkPath = $this->getChunkPath($sessionId);

        if (!Storage::disk('local')->exists($chunkPath)) {
            return response()->json([
                'uploaded_chunks' => [],
                'total_uploaded' => 0
            ]);
        }

        $files = Storage::disk('local')->files($chunkPath);
        $uploadedChunks = collect($files)
            ->map(function ($file) {
                preg_match('/chunk_(\d+)$/', $file, $matches);
                return isset($matches[1]) ? (int) $matches[1] : null;
            })
            ->filter()
            ->sort()
            ->values();

        return response()->json([
            'uploaded_chunks' => $uploadedChunks,
            'total_uploaded' => $uploadedChunks->count()
        ]);
    }

    /**
     * Check if all chunks are uploaded
     */
    private function hasAllChunks(string $sessionId, int $expectedTotal): bool
    {
        $chunkPath = $this->getChunkPath($sessionId);
        $uploadedChunks = count(Storage::disk('local')->files($chunkPath));

        return $uploadedChunks === $expectedTotal;
    }

    /**
     * Assemble all chunks into the final file
     */
    private function assembleFile(string $sessionId, string $filename, int $totalChunks)
    {
        $chunkPath = $this->getChunkPath($sessionId);
        $finalPath = self::FINAL_STORAGE_PATH . '/' . $filename;

        // Create empty file or overwrite existing
        Storage::disk('public')->put($finalPath, '');

        // Append chunks in order
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkContent = Storage::disk('local')->get("{$chunkPath}/chunk_{$i}");
            Storage::disk('public')->append($finalPath, $chunkContent);
        }

        // Clean up temporary chunks
        Storage::disk('local')->deleteDirectory($chunkPath);

        Log::info("File assembled successfully", [
            'session_id' => $sessionId,
            'filename' => $filename,
            'chunks' => $totalChunks
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'url' => Storage::disk('public')->url($finalPath),
            'size' => Storage::disk('public')->size($finalPath)
        ]);
    }

    /**
     * Get the storage path for chunks
     */
    private function getChunkPath(string $sessionId): string
    {
        return self::CHUNK_STORAGE_PATH . '/' . $sessionId;
    }
}