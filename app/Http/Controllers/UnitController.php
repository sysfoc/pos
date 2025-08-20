<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('created_at', 'desc')->get();
        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_name' => 'required|string|max:255|unique:units,unit_name',
            'short_name' => 'required|string|max:50|unique:units,short_name',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Unit::create($request->only(['unit_name', 'short_name', 'status']));

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'unit_name' => 'required|string|max:255|unique:units,unit_name,' . $id,
            'short_name' => 'required|string|max:50|unique:units,short_name,' . $id,
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $unit = Unit::findOrFail($id);
        $unit->update($request->only(['unit_name', 'short_name', 'status']));

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        if ($unit->products()->exists()) {
            return redirect()->route('units.index')->with('error', 'Cannot delete unit with associated products.');
        }
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
    }
}
