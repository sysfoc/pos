@extends('layouts.admin')

@section('title', 'Product Management')
@section('content-header', 'Product Management')
@section('content-actions')
    <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
        <i class="fas fa-plus mr-2"></i> Add New Product
    </a>
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .form-label, .form-control, .btn {
            font-size: 0.9rem;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
    </style>
@endsection
@section('content')
    <div class="card p-4">
        <div class="overflow-x-auto">
            <table id="productsTable" class="w-full">
                <thead>
                    <tr>
                        <th class="px-2 py-2 text-xs">Image</th>
                        <th class="px-2 py-2 text-xs">Name</th>
                        <th class="px-2 py-2 text-xs">Barcode</th>
                        <th class="px-2 py-2 text-xs">Category</th>
                        <th class="px-2 py-2 text-xs">Description</th>
                        <th class="px-2 py-2 text-xs">Quantity</th>
                        <th class="px-2 py-2 text-xs">Discount (%)</th>
                        <th class="px-2 py-2 text-xs">Price</th>
                        <th class="px-2 py-2 text-xs">Status</th>
                        <th class="px-2 py-2 text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td class="px-2 py-2">
                                <img class="product-img rounded" src="{{ $product->image ? Storage::url($product->image) : asset('images/placeholder.png') }}" alt="{{ $product->name }}">
                            </td>
                            <td class="px-2 py-2 text-xs">{{ $product->name }}</td>
                            <td class="px-2 py-2 text-xs">{{ $product->barcode }}</td>
                            <td class="px-2 py-2 text-xs">{{ $product->category ? ($product->category->parent ? $product->category->parent->name . ' > ' . $product->category->name : $product->category->name) : 'None' }}</td>
                            <td class="px-2 py-2 text-xs">
                                <span title="{{ $product->description ?? 'N/A' }}">
                                    {{ \Illuminate\Support\Str::limit($product->description ?? 'N/A', 10, '...') }}
                                </span>
                            </td>
                            <td class="px-2 py-2 text-xs">{{ $product->quantity }}</td>
                            <td class="px-2 py-2 text-xs">{{ $product->discount_percentage ? number_format($product->discount_percentage, 2) . '%' : 'N/A' }}</td>
                            <td class="px-2 py-2 text-xs">{{ config('settings.currency_symbol') }}{{ number_format($product->price, 2) }}</td>
                            <td class="px-2 py-2 text-xs">
                                <span class="text-xs font-medium {{ $product->status ? 'text-green-600' : 'text-red-600' }}">{{ $product->status ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-2 py-1">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-xs bg-blue-600 text-white text-xs px-2 py-1 rounded hover:bg-blue-700">
                                    Edit
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs bg-red-600 text-white text-xs px-2 py-1 rounded hover:bg-red-700 btn-delete">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#productsTable').DataTable({
                pageLength: 10,
                ordering: false,
                columnDefs: [
                    { orderable: false, targets: [0, 9] } // Disable sort on Image and Actions
                ]
            });

            // Delete confirmation
            $(document).on('click', '.btn-delete', function (e) {
                e.preventDefault();
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This product will be deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
