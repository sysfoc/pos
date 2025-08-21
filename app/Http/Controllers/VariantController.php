<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use App\Models\VariantValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VariantController extends Controller
{
    public function index()
    {
        $variants = Variant::with('values')->get();
        return view('variants.index', compact('variants'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:variants,name',
            'values' => 'required|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $variant = Variant::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        $values = array_filter(array_map('trim', explode(',', $request->values)));
        foreach ($values as $value) {
            $variant->values()->create([
                'value' => $value,
                'status' => $request->status,
            ]);
        }

        return redirect()->route('variants.index')->with('success', 'Variant created successfully');
    }

    public function update(Request $request, Variant $variant)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:variants,name,' . $variant->id,
            'values' => 'required|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $variant->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        $variant->values()->delete();
        $values = array_filter(array_map('trim', explode(',', $request->values)));
        foreach ($values as $value) {
            $variant->values()->create([
                'value' => $value,
                'status' => $request->status,
            ]);
        }

        return redirect()->route('variants.index')->with('success', 'Variant updated successfully');
    }

    public function destroy(Variant $variant)
    {
        $variant->delete();
        return redirect()->route('variants.index')->with('success', 'Variant deleted successfully');
    }
}
