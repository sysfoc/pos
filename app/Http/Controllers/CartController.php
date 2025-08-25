<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response(
                $request->user()->cart()->get()
            );
        }
        return view('cart.index');
    }

   public function store(Request $request)
{
    $request->validate([
        'barcode' => 'nullable|string',
        'product_id' => 'nullable|exists:products,id',
    ]);

    $product = null;

    if ($request->filled('barcode')) {
        $product = Product::where('barcode', $request->barcode)->first();
    } elseif ($request->filled('product_id')) {
        $product = Product::find($request->product_id);
    }

    if (!$product) {
        return response([
            'message' => 'Product not found',
        ], 404);
    }

    // find cart item by product id (not barcode anymore)
    $cart = $request->user()->cart()->where('id', $product->id)->first();

    if ($cart) {
        // check stock
        if ($product->quantity <= $cart->pivot->quantity) {
            return response([
                'message' => 'Product available only: ' . $product->quantity,
            ], 400);
        }
        // increment
        $cart->pivot->quantity = $cart->pivot->quantity + 1;
        $cart->pivot->save();
    } else {
        if ($product->quantity < 1) {
            return response([
                'message' => 'Product out of stock',
            ], 400);
        }
        $request->user()->cart()->attach($product->id, ['quantity' => 1]);
    }

    return response('', 204);
}


    public function changeQty(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $request->user()->cart()->where('id', $request->product_id)->first();

        if ($cart) {
            $cart->pivot->quantity = $request->quantity;
            $cart->pivot->save();
        }

        return response([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id'
        ]);
        $request->user()->cart()->detach($request->product_id);

        return response('', 204);
    }

    public function empty(Request $request)
    {
        $request->user()->cart()->detach();

        return response('', 204);
    }

    public function categories(Request $request)
    {
        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('status', 1);
            }])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'subcategories' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'product_count' => $child->products()->where('status', 1)->count(),
                        ];
                    })->toArray(),
                    'product_count' => $category->products()->where('status', 1)->count(),
                ];
            });

        return response()->json($categories);
    }
}
