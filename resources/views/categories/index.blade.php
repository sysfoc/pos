@extends('layouts.admin')

@section('title', 'Category Management')
@section('content-header', 'Category Management')
@section('content-actions')
    <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
        <i class="fas fa-plus mr-2"></i> Add New Category
    </a>
@endsection

@section('css')
    <!-- Minimal required DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .child-category {
            padding-left: 2rem;
            color: #6b7280; /* Lighter color for child categories */
        }
        .parent-category {
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">


            <div class="overflow-x-auto">
                <table id="categoriesTable" class="display w-full">
                    <thead>
                        <tr>
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
                            <!-- Parent Category -->
                            <tr class="parent-category">
                                <td>{{ $category->code }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->parent ? $category->parent->name : 'None' }}</td>
                                <td title="{{ $category->description ?? 'N/A' }}">
                                    {{ \Illuminate\Support\Str::limit($category->description ?? 'N/A', 20, '...') }}
                                </td>
                                <td>{{ $category->status ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @if (!$category->children()->exists())
                                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            <!-- Child Categories -->
                            @foreach ($category->children()->orderBy('created_at', 'desc')->get() as $child)
                                <tr class="child-category">
                                    <td>{{ $child->code }}</td>
                                    <td>{{ $child->name }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td title="{{ $child->description ?? 'N/A' }}">
                                        {{ \Illuminate\Support\Str::limit($child->description ?? 'N/A', 20, '...') }}
                                    </td>
                                    <td>{{ $child->status ? 'Active' : 'Inactive' }}</td>
                                    <td>
                                        <a href="{{ route('categories.edit', $child) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('categories.destroy', $child->id) }}" method="POST" class="inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- Minimal required DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#categoriesTable').DataTable({
                pageLength: 10,
                ordering: false, // Disable sorting to respect created_at order
                columnDefs: [
                    { orderable: false, targets: 5 } // Disable sort on Actions
                ]
            });

            // Delete confirmation
            $(document).on('click', '.btn-delete', function (e) {
                e.preventDefault();
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This category will be deleted.",
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
