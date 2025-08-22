<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
{
    $query = Product::with(['category', 'category.parent', 'unit'])->where('status', 1);

    if ($request->has('search')) {
        $query->where('name', 'LIKE', "%{$request->search}%");
    }

    if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->wantsJson()) {
        if ($request->query('for') === 'cart') {
            $products = $query->paginate(8); // 12 products for cart
            return response()->json([
                'data' => ProductResource::collection($products->items()),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]);
        } elseif ($request->query('for') === 'datatables') {
            $products = $query->get();
            return response()->json([
                'data' => ProductResource::collection($products),
                'draw' => (int) $request->query('draw', 1),
                'recordsTotal' => Product::where('status', 1)->count(),
                'recordsFiltered' => $query->count(),
            ]);
        }
        // Default JSON response
        $products = $query->get();
        return ProductResource::collection($products);
    }

    $products = $query->latest()->paginate(10);
    return view('products.index')->with('products', $products);
}

    public function create()
    {
        $categories = Category::where('status', 1)->get();
        $categoryData = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'has_children' => $category->children()->exists()
            ];
        });
        $units = Unit::where('status', 1)->get();
        $variants = Variant::with('values')->where('status', 1)->get();
        $variantData = $variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'values' => $variant->values->map(function ($value) {
                    return ['id' => $value->id, 'value' => $value->value];
                })->toArray()
            ];
        })->toArray();
        return view('products.create', compact('categories', 'categoryData', 'units', 'variants', 'variantData'));
    }

    public function store(ProductStoreRequest $request)
    {
        $image_path = '';

        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image_path,
            'barcode' => $request->barcode,
            'price' => $request->price,
            'cost' => $request->cost,
            'tax_percentage' => $request->tax_percentage,
            'tax_type' => $request->tax_type,
            'threshold' => $request->threshold,
            'unit_id' => $request->unit_id,
            'discount_percentage' => $request->discount_percentage,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'quantity' => $request->quantity,
            'status' => $request->status,
            'selling_type' => $request->selling_type,
            'brand_name' => $request->brand_name,
            'manufactured_date' => $request->manufactured_date,
            'expire_date' => $request->expire_date,
        ]);

        if (!$product) {
            return redirect()->back()->with('error', 'Sorry, something went wrong while creating product.');
        }

        // Save variant values if selected
        if ($request->filled('variant_value_id') && $request->filled('variant_id')) {
            $product->variantValues()->attach($request->variant_value_id, ['variant_id' => $request->variant_id]);
        }

        return redirect()->route('products.index')->with('success', 'Success, new product has been added successfully!');
    }

    public function show(Product $product)
    {
        //
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', 1)->get();
        $categoryData = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'has_children' => $category->children()->exists()
            ];
        });
        $units = Unit::where('status', 1)->get();
        $variants = Variant::with('values')->where('status', 1)->get();
        $variantData = $variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'values' => $variant->values->map(function ($value) {
                    return ['id' => $value->id, 'value' => $value->value];
                })->toArray()
            ];
        })->toArray();
        return view('products.edit', compact('product', 'categories', 'categoryData', 'units', 'variants', 'variantData'));
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $product->name = $request->name;
        $product->description = $request->description;
        $product->barcode = $request->barcode;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->tax_percentage = $request->tax_percentage;
        $product->tax_type = $request->tax_type;
        $product->threshold = $request->threshold;
        $product->unit_id = $request->unit_id;
        $product->discount_percentage = $request->discount_percentage;
        $product->sku = $request->sku;
        $product->category_id = $request->category_id;
        $product->quantity = $request->quantity;
        $product->status = $request->status;
        $product->selling_type = $request->selling_type;
        $product->brand_name = $request->brand_name;
        $product->manufactured_date = $request->manufactured_date;
        $product->expire_date = $request->expire_date;

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $image_path = $request->file('image')->store('products', 'public');
            $product->image = $image_path;
        }

        if (!$product->save()) {
            return redirect()->back()->with('error', 'Sorry, something went wrong while updating product.');
        }

        // Update variant values
        $product->variantValues()->detach();
        if ($request->filled('variant_value_id') && $request->filled('variant_id')) {
            $product->variantValues()->attach($request->variant_value_id, ['variant_id' => $request->variant_id]);
        }

        return redirect()->route('products.index')->with('success', 'Success, product has been updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return redirect()->route('products.index')->with('error', 'Cannot delete product because it is associated with one or more orders.');
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->variantValues()->detach();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
