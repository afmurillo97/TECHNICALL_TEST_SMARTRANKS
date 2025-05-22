<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

class WelcomeController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('status', true)
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::withCount('products')
            ->where('status', true)
            ->latest()
            ->take(8)
            ->get();

        return view('welcome', compact('products', 'categories'));
    }
} 