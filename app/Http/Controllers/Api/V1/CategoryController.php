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

/**
 * @OA\Tag(
 *     name="Categories V1",
 *     description="CRUD operations for categories (version 1)"
 * )
 */
class CategoryController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Categories V1"},
     *     summary="List all categories",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"products"})
     *     ),
     *     @OA\Parameter(
     *         name="category_name[like]",
     *         in="query",
     *         description="Filter by partial category name (contains)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_at[eq]",
     *         in="query",
     *         description="Filter by exact creation date",
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Parameter(
     *         name="created_at[gt]",
     *         in="query",
     *         description="Filter for records created after specified date",
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories list",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=7),
     *                         @OA\Property(property="category_name", type="string", example="Electronics"),
     *                         @OA\Property(property="description_excerpt", type="string", example="Latest electronic devices"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="url_image", type="string", nullable=true),
     *                         @OA\Property(
     *                             property="products",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/ProductResource"),
     *                             nullable=true
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="meta",
     *                     type="object",
     *                     @OA\Property(property="organization", type="string"),
     *                     @OA\Property(property="final_tester", type="string"),
     *                     @OA\Property(property="authors", type="array", @OA\Items(type="string"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     * )
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
                ->paginate(5)
                ->appends($request->query())
        );

        return $this->successResponse(
            'Categories retrieved successfully',
            200,
            $categoriesCollection
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     tags={"Categories V1"},
     *     summary="Create new category (only Admin)",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreCategoryRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category created successfully!!"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="category_id", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (admin role required)",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Forbidden"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid Data",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="The name field is required."),
     *            @OA\Property(property="errors", type="object",
     *                @OA\Property(
     *                    property="name", type="array",
     *                    @OA\Items(type="string", example="The name field is required.")
     *                )
     *            )
     *        )
     *     ),
     * )
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
     * @OA\Post(
     *     path="/api/v1/categories/bulk",
     *     tags={"Categories V1"},
     *     summary="Mass creation of categories (only Admin)",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody( required=true,
     *       @OA\JsonContent(type="array", minItems=3, maxItems=3, 
     *          @OA\Items( ref="#/components/schemas/StoreCategoryRequest" )
     *       )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Several categories created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categories created successfully!!"),
     *             @OA\Property(property="response", type="object", @OA\Property(property="count", type="integer", example=3))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (admin role required)",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Forbidden"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid Data",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="The 0.name field is required."),
     *            @OA\Property(property="errors", type="object",
     *                @OA\Property(
     *                    property="name", type="array",
     *                    @OA\Items(type="string", example="The 0.name field is required.")
     *                )
     *            )
     *        )
     *     ),
     * )
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
     * @OA\Get(
     *     path="/api/v1/categories/{category}",
     *     tags={"Categories V1"},
     *     summary="Get an specific category",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"products"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category retrieved successfully"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="category_name", type="string", example="Electronics"),
     *                 @OA\Property(property="description_excerpt", type="string", example="Latest electronic devices"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="url_image", type="string", nullable=true),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ProductResource"),
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category is not in database",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Category Not Found"),
     *         )
     *     ),
     *     
     * )
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
     * @OA\Patch(
     *     path="/api/v1/categories/{category}",
     *     tags={"Categories V1"},
     *     summary="Update category, This action is the same as PUT which is also included (only Admin)",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="category", in="path", description="Category ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateCategoryRequest")),
     *     @OA\Response(response=200, description="Category updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category updated successfully!!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (admin role required)",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Forbidden"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid Data",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="The name field is required."),
     *            @OA\Property(property="errors", type="object",
     *                @OA\Property(
     *                    property="name", type="array",
     *                    @OA\Items(type="string", example="The name field is required.")
     *                )
     *            )
     *        )
     *     ),
     * )
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
            return $this->successResponse('Category updated successfully!!', 200);
        } catch (\Exception $e) {
            Log::error('Error updating category ' . $e->getMessage() . ' In Line: ' . $e->getLine());
            return $this->errorResponse('Failed to update category', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{category}",
     *     tags={"Categories V1"},
     *     summary="Delete category (only Admin)",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter( name="category", in="path", description="Category ID", required=true, 
     *         @OA\Schema(type="integer") 
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(nullable=true)
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (admin role required)",
     *         @OA\JsonContent(@OA\Property(property="message", type="string", example="Forbidden"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found category",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Category not found"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Category deletion failed because the category has products associated",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Failed to delete category"))
     *     ),
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            $category->delete();

            return $this->successResponse('Category deleted successfully', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Category not found: ' . $e->getMessage());
            return $this->errorResponse('Category not found', 404);
        } catch (\Exception $e) {
            Log::error('Error deleting category ' . $e->getMessage() . ' In Line: ' . $e->getLine());
            return $this->errorResponse('Failed to delete category', 500);
        }
    }
}
