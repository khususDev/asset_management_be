<?php

namespace App\Http\Controllers\Api\Administration\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Administration\Asset\Category;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;

        return response()->json(['success' => true, 'data' => $this->masterDataService->listItems(Category::query(), ['name', 'code'], $entries)]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->masterDataService->createModel(new Category(), $request->validated());
        return response()->json(['success' => true, 'message' => 'Category asset successfully added!', 'data' => $category]);
    }

    public function show($id)
    {
        return response()->json(['success' => true, 'data' => Category::findOrFail($id)]);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $category = $this->masterDataService->updateModel($category, $request->validated());
        return response()->json(['success' => true, 'message' => 'Category asset successfully updated!', 'data' => $category]);
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Category asset successfully deleted!']);
    }
}
