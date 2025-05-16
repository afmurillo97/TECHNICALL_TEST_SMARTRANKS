<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\CategoryCollection;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\BulkStoreCategoryRequest;
use App\Filters\CategoryFilter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filter = new CategoryFilter();
        $queryItems = $filter->transform($request);
        
        $includeProducts = $request->query('include');

        $categories = Category::where($queryItems)->latest();
        if ($includeProducts) {
            $categories = $categories->with('products');
        }

        $categoriesCollection = new CategoryCollection(
            $categories
                ->paginate()
                ->appends($request->query())
        );

        return $this->successResponse(
            'Categories retrieved successfully',
            200,
            $categoriesCollection
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $category = new category();
            $category->fill($request->validated());

            $category->save();

            DB::commit();
            return $this->successResponse(
                'Category created succesfully!!', 
                201,
                ['category_id' => $category->id] 
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating category ' . $e->getMessage() . ' In line: ' . $e->getLine());
            return $this->errorResponse('Failed to create category', 500);
        }
    }

    /**
     * Store several resources in storage.
     */
    public function bulkStore(BulkStoreCategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            
            $bulkData = collect($validatedData)->map(function ($categoryData) {
                return [
                    'name' => Str::headline($categoryData['name']),
                    'description' => isset($categoryData['description']) && trim($categoryData['description']) !== '' 
                        ? Str::of($categoryData['description'])->trim()->ucfirst()->toString()
                        : null,
                    'featured_image' => $categoryData['featured_image'] ?? null,
                    'status' => (bool) ($categoryData['status'] ?? false),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            })->toArray();

            Category::insert($bulkData);
            
            DB::commit();
            return $this->successResponse(
                'Categories created successfully!!', 
                201,
                ['count' => count($bulkData)]
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating categories ' . $e->getMessage() . ' In line: ' . $e->getLine());
            return $this->errorResponse('Failed to create categories', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {

            $category = Category::findOrFail($id);
            $includeProducts = request()->query('include');

            if ($includeProducts) {
                $category->loadMissing('products');
            }

            return $this->successResponse(
                'Category retrieved successfully',
                200,
                new categoryResource($category)
            );

        } catch (\Exception $e) {
            return $this->errorResponse('category Not Found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        DB::beginTransaction();
        try {
            $category->fill($request->validated());

            if (!$category->isDirty()) {
                return $this->successResponse('No changes detected!!', 200);
            } 
            
            $category->update();

            DB::commit();
            return $this->successResponse('Category updated succesfully!!', 200);
        } catch (\Exception $e) {
            Log::error('Error updating category ' . $e->getMessage() . ' In Line: ' . $e->getLine());
            return $this->errorResponse('Failed to update category', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $category->delete();

            return $this->successResponse('Category deleted successfully', 204);
        } catch (\Exception $e) {
            Log::error('Error deleting category ' . $e->getMessage() . ' In Line: ' . $e->getLine());
            return $this->errorResponse('Failed to delete category', 500);
        }
    }
}
