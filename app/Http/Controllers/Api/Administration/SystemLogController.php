<?php

namespace App\Http\Controllers\Api\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; // <-- Model bawaan Spatie

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $entries = $request->input('entries', 10);
        $search = $request->input('search');

        // 1. Tangkap parameter filter baru dari Vue
        $date = $request->input('date');
        $causer_id = $request->input('causer_id');

        $query = Activity::with('causer')->latest();

        // 2. Filter Berdasarkan Tanggal Spesifik (Jika dipilih)
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        // 3. Filter Berdasarkan ID User Pelaku (Jika dipilih)
        if ($causer_id) {
            $query->where('causer_id', $causer_id);
        }

        // 4. Filter Search Teks Global (Tetap dipertahankan)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate($entries);

        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'subject' => class_basename($log->subject_type),
                'causer' => $log->causer ? $log->causer->name : 'Sistem / Guest',
                'changes' => $log->properties,
                'created_at' => $log->created_at->format('d M Y - H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar System Logs berhasil diambil',
            'data' => $logs
        ]);
    }
}
