@extends('layouts.admin')

@section('title', 'Add New Unit')
@section('content-header', 'Add New Unit')
@section('content-actions')
    <a href="{{ route('units.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
        <i class="fas fa-arrow-left mr-2"></i> Back to Units
    </a>
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .form-control {
            font-size: 0.9rem;
        }
    </style>
@endsection
@section('content')
    <div class="card p-4">
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('units.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="unit_name" class="form-label block text-gray-700">Unit Name</label>
                <input type="text" name="unit_name" id="unit_name" class="form-control w-full border rounded p-2" value="{{ old('unit_name') }}" required>
            </div>
            <div class="mb-4">
                <label for="short_name" class="form-label block text-gray-700">Short Name</label>
                <input type="text" name="short_name" id="short_name" class="form-control w-full border rounded p-2" value="{{ old('short_name') }}" required>
            </div>
            <div class="mb-4">
                <label for="status" class="form-label block text-gray-700">Status</label>
                <select name="status" id="status" class="form-control w-full border rounded p-2">
                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-save mr-1"></i> Save
            </button>
            <a href="{{ route('units.index') }}" class="btn bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-times mr-1"></i> Cancel
            </a>
        </form>
    </div>
@endsection
