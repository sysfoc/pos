@extends('layouts.admin')

@section('title', 'Product Management')
@section('content-header', 'Product Management')
@section('content-actions')
    <a href="{{ route('products.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Add New Product</a>
@endsection
@section('css')
    <style>
        .table th,
        .table td {
            font-size: 0.7rem;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
        .form-label, .form-control, .btn {
            font-size: 0.9rem;
        }
    </style>
@endsection
@section('content')
    <div class="card product-list">
        <div class="card-body">

            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Barcode</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Discount (%)</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td><img class="product-img img-thumbnail" src="{{ $product->image ? Storage::url($product->image) : asset('images/placeholder.png') }}" alt="{{ $product->name }}"></td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->barcode }}</td>
                            <td>{{ $product->category ? ($product->category->parent ? $product->category->parent->name . ' > ' . $product->category->name : $product->category->name) : 'None' }}</td>
                            <td>
                                <span title="{{ $product->description ?? 'N/A' }}">
                                    {{ \Illuminate\Support\Str::limit($product->description ?? 'N/A', 10, '...') }}
                                </span>
                            </td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->discount_percentage ? number_format($product->discount_percentage, 2) . '%' : 'N/A' }}</td>
                            <td>{{ config('settings.currency_symbol') }}{{ number_format($product->price, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $product->status ? 'success' : 'danger' }}">{{ $product->status ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $products->links() }}
        </div>
    </div>
@endsection
