@extends('layouts.admin')

@section('title', 'Product Management')
@section('content-header', 'Product Management')
@section('content-actions')
    <a href="{{ route('products.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Add New Product</a>
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
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
    </style>
@endsection
@section('content')
    <div class="card product-list">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
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
                            <td>{{ $product->category ? $product->category->name : 'None' }}</td>
                            <td>
                                <span title="{{ $product->description ?? 'N/A' }}">
                                    {{ \Illuminate\Support\Str::limit($product->description ?? 'N/A', 10, '...') }}
                                </span>
                            </td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->discount_percentage ? number_format($product->discount_percentage, 2) . '%' : 'N/A' }}</td>
                            <td>{{ config('settings.currency_symbol') }}{{ number_format($product->price, 2) }}</td>
                            <td>
                                <span class="right badge badge-{{ $product->status ? 'success' : 'danger' }}">{{ $product->status ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-danger btn-sm btn-delete" data-url="{{ route('products.destroy', $product) }}"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $products->links() }}
        </div>
    </div>
@endsection
@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $(document).on('click', '.btn-delete', function () {
                $this = $(this);
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                });

                swalWithBootstrapButtons.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete this product?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post($this.data('url'), { _method: 'DELETE', _token: '{{ csrf_token() }}' }, function (res) {
                            if (res.success) {
                                $this.closest('tr').fadeOut(500, function () {
                                    $(this).remove();
                                });
                                Swal.fire('Deleted!', 'Product has been deleted.', 'success');
                            } else {
                                Swal.fire('Error!', 'Failed to delete product.', 'error');
                            }
                        }).fail(function () {
                            Swal.fire('Error!', 'Failed to delete product.', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endsection
