<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        // 1. Validasi File (bisa gambar atau dokumen seperti PDF/Docx, max 5MB)
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        if ($request->file('file')) {
            $file = $request->file('file');

            // 2. Ambil ekstensi dan buat nama unik agar tidak bentrok di server
            $extension = $file->getClientOriginalExtension();
            $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $fileName = time() . '_' . $safeName . '.' . $extension;

            // 3. Simpan ke direktori 'public/uploads'
            // Jangan lupa jalankan 'php artisan storage:link' di terminal Anda agar bisa diakses web
            $filePath = $file->storeAs('uploads', $fileName, 'public');

            // 4. Kembalikan response berupa URL lengkap dan path relatifnya
            return response()->json([
                'success' => true,
                'message' => 'File berhasil diunggah.',
                'data' => [
                    'name' => $fileName,
                    'url' => asset('storage/' . $filePath),
                    'path' => $filePath,
                    'extension' => $extension,
                    'size' => $file->getSize()
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengunggah file.'
        ], 400);
    }
}
