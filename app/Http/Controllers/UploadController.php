<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        
        if ($receiver->isUploaded() === false) {
            return response()->json(['error' => 'No file uploaded.'], 400);
        }
        
        $save = $receiver->receive();
        
        if ($save->isFinished()) {
            // Save the file to permanent storage
            $file = $save->getFile();
            $fileName = $this->createFilename($file);
            
            // Store permanently
            $path = Storage::disk('public')->putFileAs(
                'uploads', 
                $file, 
                $fileName
            );
            
            // Delete chunk temp file
            unlink($file->getPathname());
            
            return response()->json([
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'name' => $fileName
            ]);
        }
        
        // Return progress information
        $handler = $save->handler();
        return response()->json([
            'done' => $handler->getPercentageDone(),
            'status' => true
        ]);
    }
    
    private function createFilename($file)
    {
        return md5(uniqid()) . '.' . $file->getClientOriginalExtension();
    }
}