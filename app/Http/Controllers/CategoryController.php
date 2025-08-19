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
        $validator = Validator::make($request->all(), [
            'code' => 'nullable|string|max:50|unique:categories,code',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Category::create($request->only(['code', 'name', 'parent_id', 'description', 'status']));

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'nullable|string|max:50|unique:categories,code,' . $id,
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category = Category::findOrFail($id);
        $category->update($request->only(['code', 'name', 'parent_id', 'description', 'status']));

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        // Check if category has children or products before deleting
        if ($category->children()->exists()) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category with subcategories.');
        }
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category with associated products.');
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
