<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function index()
    {
        $backupDir = storage_path('app/backups');
        $backupList = [];

        if (File::exists($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                // Hanya baca file yang berekstensi .sql
                if ($file->getExtension() === 'sql') {
                    $backupList[] = [
                        'filename' => $file->getFilename(),
                        'size' => round($file->getSize() / 1024, 2) . ' KB',
                        'created_at' => date('d M Y - H:i:s', $file->getMTime())
                    ];
                }
            }

            // Urutkan dari file yang paling baru dibuat
            usort($backupList, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        }

        return response()->json([
            'success' => true,
            'data' => $backupList
        ]);
    }

    public function store()
    {
        try {
            $dbName = config('database.connections.pgsql.database') ?: env('DB_DATABASE');
            $dbUser = config('database.connections.pgsql.username') ?: env('DB_USERNAME');
            $dbPass = config('database.connections.pgsql.password') ?: env('DB_PASSWORD');
            $dbHost = config('database.connections.pgsql.host') ?: env('DB_HOST', '127.0.0.1');
            $dbPort = config('database.connections.pgsql.port') ?: env('DB_PORT', '5432');

            if (empty($dbName) || empty($dbUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kredensial database kosong! Tolong jalankan perintah "php artisan config:clear" di terminal Anda.',
                ], 500);
            }

            $filename = "backup_" . $dbName . "_" . date('Y-m-d_H-i-s') . ".sql";

            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }

            $storagePath = $backupDir . '/' . $filename;
            $batPath = $backupDir . '/run_backup.bat';
            $errorLogPath = $backupDir . '/error.log';

            $storagePathWin = str_replace('/', '\\', $storagePath);
            $batPathWin = str_replace('/', '\\', $batPath);
            $errorLogPathWin = str_replace('/', '\\', $errorLogPath);

            $pgDumpPath = '"C:\Program Files\PostgreSQL\17\bin\pg_dump.exe"';

            $batContent = "@echo off\n";
            $batContent .= "set PGPASSWORD={$dbPass}\n";

            $batContent .= "{$pgDumpPath} -h \"{$dbHost}\" -p \"{$dbPort}\" -U \"{$dbUser}\" -F p -d \"{$dbName}\" -f \"{$storagePathWin}\" 2> \"{$errorLogPathWin}\"\n";
            $batContent .= "set PGPASSWORD=\n";

            file_put_contents($batPath, $batContent);

            exec('"' . $batPathWin . '"', $output, $returnVar);

            $errorDetail = file_exists($errorLogPath) ? file_get_contents($errorLogPath) : '';

            if (file_exists($batPath))
                @unlink($batPath);
            if (file_exists($errorLogPath))
                @unlink($errorLogPath);

            if (file_exists($storagePath) && filesize($storagePath) > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Database PostgreSQL berhasil dicadangkan.',
                    'filename' => $filename
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'PostgreSQL gagal melakukan backup via Windows Web Server.',
                'error_detail' => trim($errorDetail) ?: 'Unknown Error / File SQL tidak terbentuk.',
                'return_code' => $returnVar
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem internal.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function download($filename)
    {
        $path = storage_path("app/backups/" . $filename);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return response()->json(['message' => 'File tidak ditemukan'], 404);
    }

    public function destroy($filename)
    {
        $path = storage_path("app/backups/" . $filename);
        if (File::exists($path)) {
            File::delete($path);
            return response()->json(['success' => true, 'message' => 'File cadangan berhasil dihapus.']);
        }
        return response()->json(['message' => 'File tidak ditemukan'], 404);
    }
}
