@extends('layouts.admin')

@section('title', 'Category Management')
@section('content-header', 'Category Management')
@section('content-actions')
    <a href="{{ route('categories.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Add New Category</a>
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        .table th,
        .table td {
            font-size: 0.7rem;
        }
    </style>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->code }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->parent ? $category->parent->name : 'None' }}</td>
                            <td>
                                <span title="{{ $category->description ?? 'N/A' }}">
                                    {{ \Illuminate\Support\Str::limit($category->description ?? 'N/A', 10, '...') }}
                                </span>
                            </td>
                            <td>{{ $category->status ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-danger btn-sm btn-delete"
                                    data-url="{{ route('categories.destroy', $category) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $categories->links() }}
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.btn-delete', function() {
                $this = $(this);
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete this category?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.post($this.data('url'), {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        }, function(response) {
                            if (response.success) {
                                $this.closest('tr').fadeOut(500, function() {
                                    $(this).remove();
                                });
                                Swal.fire('Deleted!', 'Category has been deleted.', 'success');
                            } else {
                                Swal.fire('Error!', response.error || 'Failed to delete category.', 'error');
                            }
                        }).fail(function() {
                            Swal.fire('Error!', 'Failed to delete category.', 'error');
                        });
                    }
                })
            })
        })
    </script>
@endsection
