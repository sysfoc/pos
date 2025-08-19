<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->paginate(10); // Paginate with 10 items per page
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::where('status', 1)->get(); // Only active categories as parents
        return view('categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:categories,code',
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

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $categories = Category::where('status', 1)->where('id', '!=', $id)->get(); // Exclude self as parent
        return view('categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:categories,code,' . $id,
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
        // Check if category has children before deleting
        if ($category->children()->exists()) {
            return response()->json(['error' => 'Cannot delete category with subcategories.'], 400);
        }
        $category->delete();
        return response()->json(['success' => 'Category deleted successfully.']);
    }
}
