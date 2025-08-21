@extends('layouts.admin')

@section('title', 'Category Management')
@section('content-header', 'Category Management')
@section('content-actions')
    <button type="button" class="inline-flex items-center px-2 py-1 bg-green-600 text-white rounded-md hover:bg-green-700" data-bs-toggle="modal" data-bs-target="#addMainModal">
        <i class="fas fa-plus mr-2"></i> Add Main
    </button>
    <button type="button" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 ml-2" data-bs-toggle="modal" data-bs-target="#addSubModal">
        <i class="fas fa-plus mr-2"></i> Add Sub
    </button>
@endsection

@section('css')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .child-category {
            padding-left: 2rem;
            color: #6b7280; /* Lighter color for child categories */
        }
        .parent-category {
            font-weight: 600;
        }
        .modal-content {
            border-radius: 0.5rem;
        }
        .form-label, .form-control, .btn {
            font-size: 0.9rem;
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
                                <td>N/A</td>
                                <td>{{ $category->status ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCategoryModal"
                                            data-id="{{ $category->id }}"
                                            data-code="{{ $category->code ?? '' }}"
                                            data-name="{{ $category->name }}"
                                            data-status="{{ $category->status }}"
                                            data-is-main="true">Edit</button>
                                    @if (!$category->children()->exists() && !$category->products()->exists())
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
                                        <button type="button" class="btn btn-sm btn-primary btn-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCategoryModal"
                                                data-id="{{ $child->id }}"
                                                data-code="{{ $child->code ?? '' }}"
                                                data-name="{{ $child->name }}"
                                                data-parent-id="{{ $child->parent_id }}"
                                                data-description="{{ $child->description ?? '' }}"
                                                data-status="{{ $child->status }}"
                                                data-is-main="false">Edit</button>
                                        @if (!$child->children()->exists() && !$child->products()->exists())
                                            <form action="{{ route('categories.destroy', $child->id) }}" method="POST" class="inline-block delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger btn-delete">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Add Main Category Modal -->
            <div class="modal fade" id="addMainModal" tabindex="-1" aria-labelledby="addMainModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addMainModalLabel">Add Main Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="addMainForm" action="{{ route('categories.store') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="main_code" class="form-label">Code</label>
                                    <input type="text" name="code" id="main_code" class="form-control" value="{{ old('code') }}">
                                    <div id="main_code_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="main_name" class="form-label">Name</label>
                                    <input type="text" name="name" id="main_name" class="form-control" value="{{ old('name') }}" required>
                                    <div id="main_name_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="main_status" class="form-label">Status</label>
                                    <select name="status" id="main_status" class="form-control">
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Sub Category Modal -->
            <div class="modal fade" id="addSubModal" tabindex="-1" aria-labelledby="addSubModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addSubModalLabel">Add Sub Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="addSubForm" action="{{ route('categories.store') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="sub_code" class="form-label">Code</label>
                                    <input type="text" name="code" id="sub_code" class="form-control" value="{{ old('code') }}">
                                    <div id="sub_code_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="sub_name" class="form-label">Name</label>
                                    <input type="text" name="name" id="sub_name" class="form-control" value="{{ old('name') }}" required>
                                    <div id="sub_name_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="sub_parent_id" class="form-label">Parent Category</label>
                                    <select name="parent_id" id="sub_parent_id" class="form-control" required>
                                        <option value="">Select Parent</option>
                                        @foreach ($mainCategories as $category)
                                            <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="sub_parent_id_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="sub_description" class="form-label">Description</label>
                                    <textarea name="description" id="sub_description" class="form-control">{{ old('description') }}</textarea>
                                    <div id="sub_description_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="sub_status" class="form-label">Status</label>
                                    <select name="status" id="sub_status" class="form-control">
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Category Modal -->
            <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editCategoryForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label for="edit_code" class="form-label">Code</label>
                                    <input type="text" name="code" id="edit_code" class="form-control">
                                    <div id="edit_code_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Name</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                    <div id="edit_name_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3" id="edit_parent_id_container" style="display: none;">
                                    <label for="edit_parent_id" class="form-label">Parent Category</label>
                                    <select name="parent_id" id="edit_parent_id" class="form-control">
                                        <option value="">Select Parent</option>
                                        @foreach ($mainCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="edit_parent_id_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3" id="edit_description_container" style="display: none;">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea name="description" id="edit_description" class="form-control" required></textarea>
                                    <div id="edit_description_error" class="text-danger mt-1" style="display: none;"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select name="status" id="edit_status" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#categoriesTable').DataTable({
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
                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            success: function (response) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: xhr.responseJSON.message || 'An error occurred while deleting the category.',
                                    icon: 'error',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            });

            // Handle form submission for Add Main and Add Sub modals
            $('#addMainForm, #addSubForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const formId = form.attr('id');
                const errorFields = formId === 'addMainForm' ?
                    ['main_code', 'main_name'] :
                    ['sub_code', 'sub_name', 'sub_parent_id', 'sub_description'];

                // Clear previous error messages
                errorFields.forEach(field => {
                    $(`#${field}_error`).hide().text('');
                });

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.reload(); // Refresh to update table
                        });
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function (key, messages) {
                                const field = key.split('.').pop();
                                const fieldId = formId === 'addMainForm' ? `main_${field}` : `sub_${field}`;
                                $(`#${fieldId}_error`).text(messages[0]).show();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while creating the category.',
                                icon: 'error',
                                confirmButtonColor: '#d33'
                            });
                        }
                    }
                });
            });

            // Populate Edit Modal
            $(document).on('click', '.btn-edit', function () {
                const button = $(this);
                const id = button.data('id');
                const code = button.data('code');
                const name = button.data('name');
                const parentId = button.data('parent-id');
                const description = button.data('description');
                const status = button.data('status');
                const isMain = button.data('is-main') === true;

                // Populate form fields
                $('#edit_id').val(id);
                $('#edit_code').val(code);
                $('#edit_name').val(name);
                $('#edit_status').val(status);

                // Show/hide parent_id and description fields based on whether it's a main category
                if (isMain) {
                    $('#edit_parent_id_container').hide();
                    $('#edit_parent_id').val('');
                    $('#edit_description_container').hide();
                    $('#edit_description').val('').prop('required', false);
                } else {
                    $('#edit_parent_id_container').show();
                    $('#edit_parent_id').val(parentId || '');
                    $('#edit_description_container').show();
                    $('#edit_description').val(description || '').prop('required', true);
                }

                // Dynamically set form action
                $('#editCategoryForm').attr('action', '{{ url("/admin/categories") }}/' + id);
            });

            // Handle Edit Form submission
            $('#editCategoryForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const errorFields = ['edit_code', 'edit_name', 'edit_parent_id', 'edit_description'];

                // Clear previous error messages
                errorFields.forEach(field => {
                    $(`#${field}_error`).hide().text('');
                });

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST', // Laravel handles PUT via _method
                    data: form.serialize(),
                    success: function (response) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.reload(); // Refresh to update table
                        });
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function (key, messages) {
                                const field = key.split('.').pop();
                                $(`#edit_${field}_error`).text(messages[0]).show();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while updating the category.',
                                icon: 'error',
                                confirmButtonColor: '#d33'
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection
