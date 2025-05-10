<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Requests\ProductFormRequest;
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
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(Product::latest()->paginate(5));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductFormRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product = new Product();
            $product->fill($request->validated());
            $product->sku = Str::upper(Str::random(10));

            if($request->hasFile('featured_image')){
                $featuredImage = $request->file('featured_image');
                $featuredImageName = Str::slug($request->name) . '-' . uniqid() . '.' . $featuredImage->getClientOriginalExtension();
                $featuredImagePath = $featuredImage->storeAs('public/products', $featuredImageName);

                $product->featured_image = 'storage/' . str_replace('public/', '', $featuredImagePath);
            }
            $product->save();

            DB::commit();
            return $this->successResponse('Product created succesfully!!', ['product_id' => $product->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product ' . $e->getMessage() . ' In line: ' . $e->getLine());
            return $this->errorResponse('Failed to create product', 500);
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
    public function update(ProductFormRequest $request, Product $product): JsonResponse
    {
        DB::beginTransaction();
        try {
            $product->fill($request->validated());

            if($request->hasFile('featured_image')){
                $featuredImage = $request->file('featured_image');
                $featuredImageName = Str::slug($request->name) . '-' . uniqid() . '.' . $featuredImage->getClientOriginalExtension();
                $featuredImagePath = $featuredImage->storeAs('public/products', $featuredImageName);

                $product->featured_image = 'storage/' . str_replace('public/', '', $featuredImagePath);
            }
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
            return $this->errorResponse('Failed to delete post', 500);
        }
    }
}
