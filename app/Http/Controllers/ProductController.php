<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('variants')->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%")
                  ->orWhere('tasting_notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('origin')) {
            $query->where('origin', $request->origin);
        }

        if ($request->filled('roast_level')) {
            $query->where('roast_level', $request->roast_level);
        }

        $products = $query->latest()->get();
        $origins = Product::where('is_active', true)->distinct()->pluck('origin')->filter();
        $roastLevels = ['Light', 'Medium', 'Dark'];

        return view('products.index', compact('products', 'origins', 'roastLevels'));
    }

    public function show(string $slug)
    {
        $product = Product::with('variants')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $weights = $product->variants->pluck('weight_grams')->unique()->sort()->values();
        $grinds = $product->variants->pluck('grind_size')->unique()->values();

        // Siapkan data varian untuk interaksi real-time Alpine.js di frontend
        $variantsData = $product->variants->map(function ($v) {
            return [
                'id' => $v->id,
                'weight_grams' => $v->weight_grams,
                'grind_size' => $v->grind_size,
                'price' => (float) $v->price,
                'stock' => $v->stock,
            ];
        })->values();

        return view('products.show', compact('product', 'weights', 'grinds', 'variantsData'));
    }
}