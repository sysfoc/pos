<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        // Fetch only parent categories (where parent_id is null) with their children, ordered by created_at desc
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('created_at', 'desc')->get();
        // Fetch active main categories with subcategories for the Add Sub and Edit modals
        $mainCategories = Category::where('status', 1)->whereNull('parent_id')->get();
        return view('categories.index', compact('categories', 'mainCategories'));
    }


    public function create()
    {
        // Fetch only active main categories (parent_id is null) that have subcategories
        $categories = Category::where('status', 1)->whereNull('parent_id')->get();
        return view('categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Validation rules based on whether it's a main category or subcategory
        $rules = [
            'code' => 'nullable|string|max:50|unique:categories,code',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'boolean',
        ];

        // Require description only for subcategories
        if ($request->filled('parent_id')) {
            $rules['description'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Prepare data, excluding description for main categories
        $data = $request->only(['code', 'name', 'parent_id', 'status']);
        if ($request->filled('parent_id')) {
            $data['description'] = $request->input('description');
        } else {
            $data['description'] = null; // Explicitly set to null for main categories
        }

        Category::create($data);

        return response()->json(['message' => 'Category created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Validation rules based on whether it's a main category or subcategory
        $rules = [
            'code' => 'nullable|string|max:50|unique:categories,code,' . $id,
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'boolean',
        ];

        // Require description only for subcategories
        if ($request->filled('parent_id') || $category->parent_id) {
            $rules['description'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Prepare data, excluding description for main categories
        $data = $request->only(['code', 'name', 'parent_id', 'status']);
        if ($request->filled('parent_id') || $category->parent_id) {
            $data['description'] = $request->input('description');
        } else {
            $data['description'] = null; // Explicitly set to null for main categories
        }

        $category->update($data);

        return response()->json(['message' => 'Category updated successfully.']);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        // Check if category has children or products before deleting
        if ($category->children()->exists()) {
            return response()->json(['message' => 'Cannot delete category with subcategories.'], 400);
        }
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Cannot delete category with associated products.'], 400);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
