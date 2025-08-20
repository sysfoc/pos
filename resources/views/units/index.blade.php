@extends('layouts.admin')

@section('title', 'Unit Management')
@section('content-header', 'Unit Management')
@section('content-actions')
    <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700" onclick="document.getElementById('addUnitModal').classList.remove('hidden')">
        <i class="fas fa-plus mr-2"></i> Add Unit
    </button>
@endsection

@section('css')
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .form-control {
            font-size: 0.9rem;
        }
        .modal {
            display: none;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal:not(.hidden) {
            display: flex;
        }
    </style>
@endsection

@section('content')
    <div class="card p-4">


        <div class="overflow-x-auto">
            <table id="unitsTable" class="w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Unit Name</th>
                        <th class="px-4 py-2">Short Name</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td class="px-4 py-2">{{ $unit->unit_name }}</td>
                            <td class="px-4 py-2">{{ $unit->short_name }}</td>
                            <td class="px-4 py-2">{{ $unit->status ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-2">
                                <button type="button" class="btn btn-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 btn-edit"
                                        data-id="{{ $unit->id }}"
                                        data-unit-name="{{ $unit->unit_name }}"
                                        data-short-name="{{ $unit->short_name }}"
                                        data-status="{{ $unit->status }}">
                                    Edit
                                </button>
                                @if (!$unit->products()->exists())
                                    <form action="{{ route('units.destroy', $unit->id) }}" method="POST" class="inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 btn-delete">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Add Unit Modal -->
        <div id="addUnitModal" class="modal hidden fixed inset-0 items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center p-4 border-b">
                    <h5 class="text-lg font-semibold">Add Unit</h5>
                    <button type="button" class="text-gray-500 hover:text-gray-700" onclick="document.getElementById('addUnitModal').classList.add('hidden')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="addUnitForm" action="{{ route('units.store') }}" method="POST">
                    @csrf
                    <div class="p-4">
                        <div class="mb-4">
                            <label for="add_unit_name" class="form-label block text-gray-700">Unit Name</label>
                            <input type="text" name="unit_name" id="add_unit_name" class="form-control w-full border rounded p-2" required>
                            <div id="add_unit_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div class="mb-4">
                            <label for="add_short_name" class="form-label block text-gray-700">Short Name</label>
                            <input type="text" name="short_name" id="add_short_name" class="form-control w-full border rounded p-2" required>
                            <div id="add_short_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div class="mb-4">
                            <label for="add_status" class="form-label block text-gray-700">Status</label>
                            <select name="status" id="add_status" class="form-control w-full border rounded p-2">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end p-4 border-t">
                        <button type="button" class="btn bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2" onclick="document.getElementById('addUnitModal').classList.add('hidden')">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Unit Modal -->
        <div id="editUnitModal" class="modal hidden fixed inset-0 items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center p-4 border-b">
                    <h5 class="text-lg font-semibold">Edit Unit</h5>
                    <button type="button" class="text-gray-500 hover:text-gray-700" onclick="document.getElementById('editUnitModal').classList.add('hidden')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editUnitForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-4">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-4">
                            <label for="edit_unit_name" class="form-label block text-gray-700">Unit Name</label>
                            <input type="text" name="unit_name" id="edit_unit_name" class="form-control w-full border rounded p-2" required>
                            <div id="edit_unit_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div class="mb-4">
                            <label for="edit_short_name" class="form-label block text-gray-700">Short Name</label>
                            <input type="text" name="short_name" id="edit_short_name" class="form-control w-full border rounded p-2" required>
                            <div id="edit_short_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div class="mb-4">
                            <label for="edit_status" class="form-label block text-gray-700">Status</label>
                            <select name="status" id="edit_status" class="form-control w-full border rounded p-2">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end p-4 border-t">
                        <button type="button" class="btn bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2" onclick="document.getElementById('editUnitModal').classList.add('hidden')">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#unitsTable').DataTable({
                pageLength: 10,
                ordering: false,
                columnDefs: [
                    { orderable: false, targets: 3 } // Disable sort on Actions
                ]
            });

            // Delete confirmation
            $(document).on('click', '.btn-delete', function (e) {
                e.preventDefault();
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This unit will be deleted.",
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

            // Handle form submission for Add Unit
            $('#addUnitForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const errorFields = ['add_unit_name', 'add_short_name'];

                // Clear previous error messages
                errorFields.forEach(field => {
                    $(`#${field}_error`).addClass('hidden').text('');
                });

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Unit created successfully.',
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function (key, messages) {
                                const field = key.split('.').pop();
                                $(`#add_${field}_error`).text(messages[0]).removeClass('hidden');
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while creating the unit.',
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
                const unitName = button.data('unit-name');
                const shortName = button.data('short-name');
                const status = button.data('status');

                // Populate form fields
                $('#edit_id').val(id);
                $('#edit_unit_name').val(unitName);
                $('#edit_short_name').val(shortName);
                $('#edit_status').val(status);

                // Set form action
                $('#editUnitForm').attr('action', '{{ url("/admin/units") }}/' + id);

                // Show modal
                document.getElementById('editUnitModal').classList.remove('hidden');
            });

            // Handle Edit Form submission
            $('#editUnitForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const errorFields = ['edit_unit_name', 'edit_short_name'];

                // Clear previous error messages
                errorFields.forEach(field => {
                    $(`#${field}_error`).addClass('hidden').text('');
                });

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST', // Laravel handles PUT via _method
                    data: form.serialize(),
                    success: function (response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Unit updated successfully.',
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function (key, messages) {
                                const field = key.split('.').pop();
                                $(`#edit_${field}_error`).text(messages[0]).removeClass('hidden');
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while updating the unit.',
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
