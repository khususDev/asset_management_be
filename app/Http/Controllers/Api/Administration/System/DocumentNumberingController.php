<?php

namespace App\Http\Controllers\Api\Administration\System;

use App\Http\Controllers\Controller;
use App\Models\Administration\System\DocumentNumbering;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentNumberingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = DocumentNumbering::with('department');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('module', 'like', "%{$search}%");
        }

        return response()->json(['success' => true, 'data' => $query->latest()->paginate($entries)]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string|max:100|unique:sys_document_numbering,module',
            'department' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'format' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:50',
            'digit_length' => 'required|integer|min:1|max:10',
            'reset_type' => 'required|in:NEVER,YEARLY,MONTHLY,DAILY',
            'is_active' => 'boolean'
        ]);

        $exists = DocumentNumbering::where('module', $request->module)
            ->where('department', $request->department)
            ->exists();

        if ($exists) {
            return response()->json(['errors' => ['module' => ['Format untuk modul dan departemen ini sudah ada.']]], 422);
        }

        // Saat buat baru, sequence otomatis 0
        $data = $request->all();
        $data['current_sequence'] = 0;

        $doc = DocumentNumbering::create($data);
        return response()->json(['success' => true, 'message' => 'Penomoran berhasil ditambahkan!', 'data' => $doc]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => DocumentNumbering::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $doc = DocumentNumbering::findOrFail($id);
        $request->validate([
            'module' => ['required', 'string', 'max:100', Rule::unique('sys_document_numbering')->ignore($id)],
            'department' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'format' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:50',
            'digit_length' => 'required|integer|min:1|max:10',
            'reset_type' => 'required|in:NEVER,YEARLY,MONTHLY,DAILY',
            'is_active' => 'boolean'
        ]);

        $exists = DocumentNumbering::where('module', $request->module)
            ->where('department', $request->department)
            ->exists();

        if ($exists) {
            return response()->json(['errors' => ['module' => ['Format untuk modul dan departemen ini sudah ada.']]], 422);
        }

        $doc->update($request->except('current_sequence')); // Cegah user update nomor urut dari form
        return response()->json(['success' => true, 'message' => 'Penomoran berhasil diperbarui!', 'data' => $doc]);
    }

    public function destroy($id)
    {
        DocumentNumbering::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Penomoran berhasil dihapus!']);
    }
}
