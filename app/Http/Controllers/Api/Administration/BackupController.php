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

            $storagePath = $backupDir . DIRECTORY_SEPARATOR . $filename;

            $pgDumpPath = $this->resolvePgDumpPath();
            if (!$this->isPgDumpAvailable($pgDumpPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'pg_dump tidak ditemukan atau tidak dapat dijalankan. Pastikan path di .env benar atau PostgreSQL sudah terinstal.',
                    'error_detail' => "Resolved pg_dump path: {$pgDumpPath}",
                ], 500);
            }

            // Susun Command Process (Tanpa --no-password agar PGPASSWORD dibaca normal)
            $process = new Process([
                $pgDumpPath,
                '-h',
                $dbHost,
                '-p',
                $dbPort,
                '-U',
                $dbUser,
                '-F',
                'p',
                '-f',
                $storagePath,
                $dbName,
            ]);

            // Oper PGPASSWORD secara langsung ke process environment
            $process->setEnv([
                'PGPASSWORD' => $dbPass,
                'PATH' => getenv('PATH') ?: '',
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            ]);

            $process->setWorkingDirectory($backupDir);
            $process->setTimeout(900);
            $process->run();

            $output = trim($process->getOutput());
            $errorDetail = trim($process->getErrorOutput());
            if (empty($errorDetail) && !empty($output)) {
                $errorDetail = $output;
            }

            if ($process->isSuccessful() && file_exists($storagePath) && filesize($storagePath) > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Database PostgreSQL berhasil dicadangkan.',
                    'filename' => $filename
                ]);
            }

            logger()->error('pg_dump backup failed', [
                'pg_dump_path' => $pgDumpPath,
                'database' => $dbName,
                'host' => $dbHost,
                'port' => $dbPort,
                'user' => $dbUser,
                'exit_code' => $process->getExitCode(),
                'error_detail' => $errorDetail,
                'output' => $output,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PostgreSQL gagal melakukan backup. Periksa error_detail untuk detail lebih lengkap.',
                'error_detail' => $errorDetail ?: 'Unknown Error / File SQL tidak terbentuk.',
                'return_code' => $process->getExitCode()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem internal.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    private function resolvePgDumpPath(): string
    {
        $customPath = env('PG_DUMP_PATH');
        if (!empty($customPath)) {
            return $customPath;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $defaultWindowsPath = 'C:/Program Files/PostgreSQL/17/bin/pg_dump.exe';
            if (file_exists($defaultWindowsPath)) {
                return $defaultWindowsPath;
            }
            return 'pg_dump.exe';
        }

        return 'pg_dump';
    }

    // private function resolvePgDumpPath(): string
    // {
    //     $customPath = env('PG_DUMP_PATH');
    //     if (!empty($customPath)) {
    //         return $customPath;
    //     }

    //     if (PHP_OS_FAMILY === 'Windows') {
    //         $defaultWindowsPath = 'C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe';
    //         if (file_exists($defaultWindowsPath)) {
    //             return $defaultWindowsPath;
    //         }
    //         return 'pg_dump.exe';
    //     }

    //     return 'pg_dump';
    // }

    private function isPgDumpAvailable(string $pgDumpPath): bool
    {
        try {
            $check = new Process([$pgDumpPath, '--version']);
            $check->setTimeout(10);
            $check->run();
            return $check->isSuccessful();
        } catch (\Throwable $e) {
            logger()->warning('pg_dump availability check failed', [
                'pg_dump_path' => $pgDumpPath,
                'exception' => $e->getMessage(),
            ]);
            return false;
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

