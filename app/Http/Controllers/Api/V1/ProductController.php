<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Requests\ProductFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductResource::collection(Product::latest()->paginate(5));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductFormRequest $request)
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
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
