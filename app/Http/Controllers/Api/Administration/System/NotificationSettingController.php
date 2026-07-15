<?php

namespace App\Http\Controllers\Api\Administration\System;

use App\Models\Administration\System\NotificationSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = NotificationSetting::query();

        if ($search) {
            $query->where('module', 'like', "%{$search}%")
                ->orWhere('event', 'like', "%{$search}%")
                ->orWhere('recipient_role', 'like', "%{$search}%");
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('module')->orderBy('event')->paginate($entries)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string|max:100',
            'event' => 'required|string|max:100',
            'recipient_role' => 'required|string|max:100',
            'type' => 'required|in:EMAIL,SYSTEM,BOTH',
            'is_active' => 'boolean'
        ]);

        $setting = NotificationSetting::create($request->all());
        return response()->json(['success' => true, 'message' => 'Pengaturan Notifikasi berhasil ditambahkan!', 'data' => $setting]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => NotificationSetting::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $setting = NotificationSetting::findOrFail($id);

        $request->validate([
            'module' => 'required|string|max:100',
            'event' => 'required|string|max:100',
            'recipient_role' => 'required|string|max:100',
            'type' => 'required|in:EMAIL,SYSTEM,BOTH',
            'is_active' => 'boolean'
        ]);

        $setting->update($request->all());
        return response()->json(['success' => true, 'message' => 'Pengaturan Notifikasi berhasil diperbarui!', 'data' => $setting]);
    }

    public function destroy($id)
    {
        NotificationSetting::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Pengaturan Notifikasi berhasil dihapus!']);
    }
}
