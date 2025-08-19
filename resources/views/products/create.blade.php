@extends('layouts.admin')

@section('title', 'Create Product')
@section('content-header', 'Create Product')
@section('content-actions')
    <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Products</a>
@endsection
@section('css')
    <style>
        .form-label, .form-control, .btn, .custom-file-label {
            font-size: 0.9rem;
        }
    </style>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name"
                           placeholder="Enter product name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                              id="description" placeholder="Enter short description">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="image" class="form-label">Product Image</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('image') is-invalid @enderror" name="image" id="image">
                        <label class="custom-file-label" for="image">Choose File</label>
                    </div>
                    @error('image')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="barcode" class="form-label">Barcode</label>
                    <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror"
                           id="barcode" placeholder="Enter barcode number" value="{{ old('barcode') }}" required>
                    @error('barcode')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" id="price"
                           placeholder="Enter price" value="{{ old('price') }}" required step="0.01">
                    @error('price')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="cost" class="form-label">Cost</label>
                    <input type="number" name="cost" class="form-control @error('cost') is-invalid @enderror" id="cost"
                           placeholder="Enter cost" value="{{ old('cost') }}" required step="0.01">
                    @error('cost')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="tax_percentage" class="form-label">Tax Percentage</label>
                    <input type="number" name="tax_percentage" class="form-control @error('tax_percentage') is-invalid @enderror" id="tax_percentage"
                           placeholder="Enter tax percentage" value="{{ old('tax_percentage') }}" step="0.01">
                    @error('tax_percentage')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="tax_type" class="form-label">Tax Type</label>
                    <select name="tax_type" class="form-control @error('tax_type') is-invalid @enderror" id="tax_type">
                        <option value="" {{ old('tax_type') == '' ? 'selected' : '' }}>Select Tax Type</option>
                        <option value="inclusive" {{ old('tax_type', 'exclusive') == 'inclusive' ? 'selected' : '' }}>Inclusive</option>
                        <option value="exclusive" {{ old('tax_type', 'exclusive') == 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                    </select>
                    @error('tax_type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="threshold" class="form-label">Threshold</label>
                    <input type="number" name="threshold" class="form-control @error('threshold') is-invalid @enderror" id="threshold"
                           placeholder="Enter stock threshold" value="{{ old('threshold') }}">
                    @error('threshold')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="unit" class="form-label">Unit</label>
                    <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" id="unit"
                           placeholder="Enter unit (e.g., kg, piece)" value="{{ old('unit') }}">
                    @error('unit')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="discount_percentage" class="form-label">Discount Percentage</label>
                    <input type="number" name="discount_percentage" class="form-control @error('discount_percentage') is-invalid @enderror" id="discount_percentage"
                           placeholder="Enter discount percentage" value="{{ old('discount_percentage') }}" step="0.01">
                    @error('discount_percentage')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="sku" class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" id="sku"
                           placeholder="Enter SKU" value="{{ old('sku') }}">
                    @error('sku')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" id="category_id">
                        <option value="">None</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" id="quantity"
                           placeholder="Enter quantity" value="{{ old('quantity', 1) }}" required>
                    @error('quantity')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" id="status">
                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            bsCustomFileInput.init();
        });
    </script>
@endsection
