<?php

namespace App\Http\Controllers\Api\Administration\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTypeRequest;
use App\Http\Requests\UpdateTypeRequest;
use App\Models\Administration\Asset\Type;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = Type::with('category');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereHas('category', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate($entries),
        ]);
    }

    public function store(StoreTypeRequest $request)
    {
        $type = $this->masterDataService->createModel(new Type(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $type,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Type::with('category')->findOrFail($id),
        ]);
    }

    public function update(UpdateTypeRequest $request, string $id)
    {
        $type = Type::findOrFail($id);
        $type = $this->masterDataService->updateModel($type, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $type,
        ]);
    }

    public function destroy(string $id)
    {
        Type::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
