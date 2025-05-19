<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ProductCollection;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\BulkStoreProductRequest;
use App\Filters\ProductFilter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Products V1",
 *     description="CRUD operations for products (version 1)"
 * )
 */
class ProductController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Products V1"},
     *     summary="List all products",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="name[like]",
     *         in="query",
     *         description="Filter by partial product name (contains)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price[eq]",
     *         in="query",
     *         description="Filter by exact price",
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="stock[gte]",
     *         in="query",
     *         description="Filter by an amount that is greater than or equal to the given value.",
     *         @OA\Schema(type="number", format="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products list",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 ref="#/components/schemas/ProductCollection"
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
        $filter = new ProductFilter();
        $queryItems = $filter->transform($request);

        $products = Product::where($queryItems);

        $productsCollection = new ProductCollection(
            $products
                ->paginate()
                ->appends($request->query())
        );
        
        return $this->successResponse(
            'Products retrieved successfully',
            200,
            $productsCollection
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     tags={"Products V1"},
     *     summary="Create new product (only Admin)",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreProductRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product created successfully!!"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="product_id", type="integer", example=18)
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
    public function store(StoreProductRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product = new Product();
            $product->fill($request->validated());
            $product->sku = Str::upper(Str::random(10));
            $product->save();

            DB::commit();
            return $this->successResponse(
                'Product created succesfully!!', 
                201,
                ['product_id' => $product->id],
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product ' . $e->getMessage() . ' In line: ' . $e->getLine());
            return $this->errorResponse('Failed to create product', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products/bulk",
     *     tags={"Products V1"},
     *     summary="Mass creation of products (only Admin)",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody( required=true,
     *       @OA\JsonContent(type="array", minItems=3, maxItems=3, 
     *          @OA\Items( ref="#/components/schemas/StoreProductRequest" )
     *       )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Several products created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Products created successfully!!"),
     *             @OA\Property(property="response", type="object", @OA\Property(property="count", type="integer", example=4))
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
    public function bulkStore(BulkStoreProductRequest $request): JsonResponse
    {


        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            
            $bulkData = collect($validatedData)->map(function ($productData) {
                return [
                    'category_id' => $productData['category_id'],
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'purchase_price' => $productData['purchase_price'],
                    'sale_price' => $productData['sale_price'],
                    'stock' => $productData['stock'],
                    'featured_image' => $productData['featured_image'],
                    'status' => $productData['status'],
                    'sku' => Str::upper(Str::random(10)),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            })->toArray();

            Product::insert($bulkData);
            
            DB::commit();
            return $this->successResponse(
                'Products created successfully!!', 
                201,
                ['count' => count($bulkData)]
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating products ' . $e->getMessage() . ' In line: ' . $e->getLine());
            return $this->errorResponse('Failed to create products', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{product}",
     *     tags={"Products V1"},
     *     summary="Get an specific product",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="product retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product retrieved successfully"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 ref="#/components/schemas/ProductResource"
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
     *         description="Product is not in database",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Product Not Found"),
     *         )
     *     ),
     *     
     * )
     */
    public function show($id): JsonResponse
    {
        try {

            $product = Product::findOrFail($id);

            return $this->successResponse(
                'Product retrieved successfully',
                200,
                new ProductResource($product)
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Product Not Found', 404);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/products/{product}",
     *     tags={"Products V1"},
     *     summary="Update product, This action is the same as PUT which is also included (only Admin)",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="product", in="path", description="Product ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateProductRequest")),
     *     @OA\Response(response=200, description="Product updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product updated successfully!!")
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
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product->fill($request->validated());

            if (!$product->isDirty()) {
                return $this->successResponse('No changes detected!!', 200);
            } 
            
            $product->update();

            DB::commit();
            return $this->successResponse('Product updated successfully!!', 200);
        } catch (\Exception $e) {
            Log::error('Error updating product ' . $e->getMessage() . ' In Line: ' . $e->getLine());
            return $this->errorResponse('Failed to update product', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{product}",
     *     tags={"Products V1"},
     *     summary="Delete product (only Admin)",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter( name="product", in="path", description="Product ID", required=true, 
     *         @OA\Schema(type="integer") 
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully",
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
     *         description="Not found product",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Product not found"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Product deletion failed",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", example="Failed to delete product"))
     *     ),
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            $product->delete();

            return $this->successResponse('Product deleted successfully', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Product not found: ' . $e->getMessage());
            return $this->errorResponse('Product not found', 404);
        } catch (\Exception $e) {
            Log::error('Error deleting product ' . $e->getMessage() . ' In Line: ' . $e->getLine());
            return $this->errorResponse('Failed to delete product', 500);
        }
    }
}
