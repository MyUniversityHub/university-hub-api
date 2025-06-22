<?php

namespace App\Http\Controllers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CloudinaryUploadController extends Controller
{

    public function uploadImage(Request $request)
    {
        try {
            // Kiểm tra xem có file 'image' không
            if (!$request->hasFile('image')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image file provided.'
                ], 400);
            }

            $file = $request->file('image');

            // Kiểm tra file có hợp lệ không
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file upload.'
                ], 400);
            }

            // Log thông tin file để debug
            Log::info('Uploading file:', [
                'originalName' => $file->getClientOriginalName(),
                'mimeType' => $file->getMimeType(),
                'size' => $file->getSize(),
                'realPath' => $file->getRealPath(),
            ]);

            // Upload lên Cloudinary
            $uploadResult = Cloudinary::upload($file->getRealPath(), [
                'folder' => 'uploads', // (Tùy chọn)
                'public_id' => 'custom_name_' . time(), // (Tùy chọn)
            ]);

            return response()->json([
                'success' => true,
                'url' => $uploadResult->getSecurePath(),
            ]);

        } catch (\Exception $e) {
            Log::error('Upload failed: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
