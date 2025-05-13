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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
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
            $productsCollection,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
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
                ['product_id' => $product->id], 
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product ' . $e->getMessage() . ' In line: ' . $e->getLine());
            return $this->errorResponse('Failed to create product', 500);
        }
    }

    /**
     * Store several resources in storage.
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

            Log::info('Datos finales antes de insert:', $bulkData);
            Product::insert($bulkData);
            
            DB::commit();
            return $this->successResponse(
                'Products created successfully!!', 
                ['count' => count($bulkData)], 
                201
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating products ' . $e->getMessage() . ' In line: ' . $e->getLine());
            return $this->errorResponse('Failed to create products', 500);
        }
    }

    /**
     * Display the specified resource.
     * 
     */
    public function show($id): JsonResponse
    {
        try {

            $product = Product::findOrFail($id);

            return $this->successResponse(
                'Product retrieved successfully',
                new ProductResource($product),
                200
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Product Not Found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product->fill($request->validated());

            if (!$product->isDirty()) {
                return $this->successResponse('No changes detected!!', null, 200);
            } 
            
            $product->update();

            DB::commit();
            return $this->successResponse('Product updated succesfully!!', null, 200);
        } catch (\Exception $e) {
            Log::error('Error updating product ' . $e->getMessage() . ' In Line: ' . $e->getLine());
            return $this->errorResponse('Failed to update product', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $product->delete();

            return $this->successResponse('Product deleted successfully', null, 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product ' . $e->getMessage() . ' In Line: ' . $e->getLine());
            return $this->errorResponse('Failed to delete product', 500);
        }
    }
}
