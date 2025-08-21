
@extends('layouts.admin')

@section('title', 'Create Product')
@section('content-header', 'Create Product')
@section('content-actions')
    <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg shadow-md hover:bg-gray-700 transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Products
    </a>
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .form-label {
            @apply text-sm font-normal text-gray-700;
        }
        .form-control {
            @apply w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition;
        }
        .error-border {
            @apply border-red-500;
        }
        .error-text {
            @apply text-red-600 text-sm mt-1 flex items-center;
        }
        .section-title {
            font-weight: 700 !important;
            font-size: 1.125rem !important;
        }
    </style>
@endsection
@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-xl shadow-lg">
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li class="flex items-center"><i class="fas fa-exclamation-circle mr-2"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Basic Information -->
            <div class="bg-blue-50 p-6 mb-6 rounded-lg border border-blue-200">
                <h2 class="section-title text-lg pb-4"><i class="fas fa-info-circle mr-2 text-blue-600"></i> Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control @error('name') error-border @enderror" id="name"
                               placeholder="Enter product name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control @error('image') error-border @enderror" name="image" id="image">
                        @error('image')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') error-border @enderror"
                                  id="description" placeholder="Enter short description" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pricing Details -->
            <div class="bg-green-50 p-6 mb-6 rounded-lg border border-green-200">
                <h2 class="section-title text-lg pb-4"><i class="fas fa-dollar-sign mr-2 text-green-600"></i> Pricing Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" class="form-control @error('price') error-border @enderror" id="price"
                               placeholder="Enter price" value="{{ old('price') }}" required step="0.01">
                        @error('price')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="cost" class="form-label">Cost</label>
                        <input type="number" name="cost" class="form-control @error('cost') error-border @enderror" id="cost"
                               placeholder="Enter cost" value="{{ old('cost') }}" required step="0.01">
                        @error('cost')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="tax_percentage" class="form-label">Tax Percentage</label>
                        <input type="number" name="tax_percentage" class="form-control @error('tax_percentage') error-border @enderror" id="tax_percentage"
                               placeholder="Enter tax percentage" value="{{ old('tax_percentage') }}" step="0.01">
                        @error('tax_percentage')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="tax_type" class="form-label">Tax Type</label>
                        <select name="tax_type" class="form-control @error('tax_type') error-border @enderror" id="tax_type">
                            <option value="" {{ old('tax_type') == '' ? 'selected' : '' }}>Select Tax Type</option>
                            <option value="inclusive" {{ old('tax_type', 'exclusive') == 'inclusive' ? 'selected' : '' }}>Inclusive</option>
                            <option value="exclusive" {{ old('tax_type', 'exclusive') == 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                        </select>
                        @error('tax_type')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="discount_percentage" class="form-label">Discount Percentage</label>
                        <input type="number" name="discount_percentage" class="form-control @error('discount_percentage') error-border @enderror" id="discount_percentage"
                               placeholder="Enter discount percentage" value="{{ old('discount_percentage') }}" step="0.01">
                        @error('discount_percentage')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Category Information -->
            <div class="bg-purple-50 p-6 mb-6 rounded-lg border border-purple-200">
                <h2 class="section-title text-lg pb-4"><i class="fas fa-tags mr-2 text-purple-600"></i> Category Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="parent_category_id" class="form-label">Parent Category</label>
                        <select name="parent_category_id" class="form-control @error('category_id') error-border @enderror" id="parent_category_id">
                            <option value="">Select Parent Category</option>
                            @foreach ($categories->where('parent_id', null) as $category)
                                <option value="{{ $category->id }}" {{ old('parent_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="category_id" class="form-label">Subcategory</label>
                        <select name="category_id" class="form-control @error('category_id') error-border @enderror" id="category_id">
                            <option value="">Select Subcategory</option>
                        </select>
                        @error('category_id')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Inventory Details -->
            <div class="bg-yellow-50 p-6 mb-6 rounded-lg border border-yellow-200">
                <h2 class="section-title text-lg pb-4"><i class="fas fa-box-open mr-2 text-yellow-600"></i> Inventory Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control @error('quantity') error-border @enderror" id="quantity"
                               placeholder="Enter quantity" value="{{ old('quantity', 1) }}" required>
                        @error('quantity')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="threshold" class="form-label">Threshold</label>
                        <input type="number" name="threshold" class="form-control @error('threshold') error-border @enderror" id="threshold"
                               placeholder="Enter stock threshold" value="{{ old('threshold') }}">
                        @error('threshold')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select name="unit_id" class="form-control @error('unit_id') error-border @enderror" id="unit_id">
                            <option value="">Select Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->unit_name }} ({{ $unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Add Variants -->
            <div class="bg-teal-50 p-6 mb-6 rounded-lg border border-teal-200">
                <h2 class="section-title text-lg pb-4"><i class="fas fa-tags mr-2 text-teal-600"></i> Add Variants</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="variant_id" class="form-label">Select Variant</label>
                        <select name="variant_id" class="form-control @error('variant_id') error-border @enderror" id="variant_id">
                            <option value="">Select Variant</option>
                            @foreach ($variants as $variant)
                                <option value="{{ $variant->id }}" {{ old('variant_id') == $variant->id ? 'selected' : '' }}>{{ $variant->name }}</option>
                            @endforeach
                        </select>
                        @error('variant_id')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="variant_value_id" class="form-label">Variant Values</label>
                        <select name="variant_value_id" class="form-control @error('variant_value_id') error-border @enderror" id="variant_value_id" disabled>
                            <option value="">Select Variant Value</option>
                        </select>
                        @error('variant_value_id')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="bg-indigo-50 p-6 mb-6 rounded-lg border border-indigo-200">
                <h2 class="section-title text-lg pb-4"><i class="fas fa-list-alt mr-2 text-indigo-600"></i> Additional Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="barcode" class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control @error('barcode') error-border @enderror"
                               id="barcode" placeholder="Enter barcode number" value="{{ old('barcode') }}">
                        @error('barcode')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control @error('sku') error-border @enderror" id="sku"
                               placeholder="Enter SKU" value="{{ old('sku') }}">
                        @error('sku')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="selling_type" class="form-label">Selling Type</label>
                        <select name="selling_type" class="form-control @error('selling_type') error-border @enderror" id="selling_type">
                            <option value="" {{ old('selling_type') == '' ? 'selected' : '' }}>Select Selling Type</option>
                            <option value="online" {{ old('selling_type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="pos" {{ old('selling_type') == 'pos' ? 'selected' : '' }}>POS</option>
                        </select>
                        @error('selling_type')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="brand_name" class="form-label">Brand Name</label>
                        <input type="text" name="brand_name" class="form-control @error('brand_name') error-border @enderror" id="brand_name"
                               placeholder="Enter brand name" value="{{ old('brand_name') }}">
                        @error('brand_name')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="manufactured_date" class="form-label">Manufactured Date</label>
                        <input type="date" name="manufactured_date" class="form-control @error('manufactured_date') error-border @enderror" id="manufactured_date"
                               value="{{ old('manufactured_date') }}">
                        @error('manufactured_date')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="expire_date" class="form-label">Expire Date</label>
                        <input type="date" name="expire_date" class="form-control @error('expire_date') error-border @enderror" id="expire_date"
                               value="{{ old('expire_date') }}">
                        @error('expire_date')
                            <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-gray-50 p-6 mb-6 rounded-lg border border-gray-200">
                <h2 class="section-title text-lg pb-4"><i class="fas fa-toggle-on mr-2 text-gray-600"></i> Status</h2>
                <div class="mb-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-control @error('status') error-border @enderror" id="status">
                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <span class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
                <button type="submit" class="btn bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i> Save
                </button>
                <a href="{{ route('products.index') }}" class="btn bg-gray-500 text-white px-6 py-3 rounded-lg shadow-md hover:bg-gray-600 transition">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Preload categories and variants from controller
            const categories = @json($categoryData ?? []);
            const variants = @json($variantData ?? []);

            // Populate subcategories when parent category changes
            $('#parent_category_id').on('change', function () {
                const parentId = $(this).val();
                const subcategorySelect = $('#category_id');
                subcategorySelect.empty().append('<option value="">Select Subcategory</option>');

                const subcategories = categories.filter(category =>
                    category.parent_id == parentId && !category.has_children
                );

                subcategories.forEach(category => {
                    subcategorySelect.append(
                        `<option value="${category.id}">${category.name}</option>`
                    );
                });
            });

            // Populate variant values when variant changes
            $('#variant_id').on('change', function () {
                const variantId = $(this).val();
                const variantValueSelect = $('#variant_value_id');
                variantValueSelect.empty().append('<option value="">Select Variant Value</option>').prop('disabled', !variantId);

                if (variantId) {
                    const selectedVariant = variants.find(variant => variant.id == variantId);
                    if (selectedVariant && selectedVariant.values) {
                        selectedVariant.values.forEach(value => {
                            variantValueSelect.append(
                                `<option value="${value.id}">${value.value}</option>`
                            );
                        });
                    }
                }
            });

            // Trigger change on page load to populate subcategories
            @if (old('parent_category_id'))
                $('#parent_category_id').val('{{ old('parent_category_id') }}').trigger('change');
            @endif

            // Trigger change on page load to populate variant values
            @if (old('variant_id'))
                $('#variant_id').val('{{ old('variant_id') }}').trigger('change');
            @endif
        });
    </script>
@endsection

