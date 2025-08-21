@extends('layouts.admin')

@section('title', 'Variant Management')
@section('content-header', 'Variant Management')
@section('content-actions')
    <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-xs"
            onclick="document.getElementById('addVariantModal').classList.remove('hidden')">
        <i class="fas fa-plus mr-2"></i> Add Variant
    </button>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .form-label, .form-control, .btn {
            font-size: 0.75rem;
        }
        .modal {
            display: none;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal:not(.hidden) {
            display: flex;
        }
        table.dataTable th, table.dataTable td {
            font-size: 0.75rem;
        }
    </style>
@endsection

@section('content')

    <div class="card p-4">
        <div class="overflow-x-auto">
            <table id="variantsTable" class="w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Variant Name</th>
                        <th class="px-4 py-2">Values</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($variants as $variant)
                        <tr>
                            <td class="px-4 py-2">{{ $variant->name }}</td>
                            <td class="px-4 py-2">{{ $variant->values->pluck('value')->implode(', ') }}</td>
                            <td class="px-4 py-2">
                                <span class="text-xs font-medium {{ $variant->status ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $variant->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <button type="button" class="btn btn-xs bg-blue-600 text-white px-2 py-0.5 rounded hover:bg-blue-700 btn-edit"
                                        data-id="{{ $variant->id }}"
                                        data-name="{{ $variant->name }}"
                                        data-values="{{ $variant->values->pluck('value')->implode(',') }}"
                                        data-status="{{ $variant->status }}"
                                        onclick="openEditModal(this)">
                                    Edit
                                </button>
                                <form action="{{ route('variants.destroy', $variant) }}" method="POST" class="inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs bg-red-600 text-white px-2 py-0.5 rounded hover:bg-red-700 btn-delete">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Add Variant Modal -->
        <div id="addVariantModal" class="modal hidden fixed inset-0 items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center p-4 border-b">
                    <h5 class="text-base font-semibold">Add Variant</h5>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-sm"
                            onclick="document.getElementById('addVariantModal').classList.add('hidden')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('variants.store') }}" method="POST">
                    @csrf
                    <div class="p-4">
                        <div class="mb-3">
                            <label for="add_name" class="form-label block text-gray-700">Variant Name</label>
                            <input type="text" name="name" id="add_name" class="form-control w-full border rounded p-2 text-xs"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="add_values" class="form-label block text-gray-700">Values (comma-separated)</label>
                            <input type="text" name="values" id="add_values" class="form-control w-full border rounded p-2 text-xs"
                                   value="{{ old('values') }}" required>
                            @error('values')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="add_status" class="form-label block text-gray-700">Status</label>
                            <select name="status" id="add_status" class="form-control w-full border rounded p-2 text-xs">
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end p-4 border-t">
                        <button type="button" class="btn bg-gray-500 text-white px-2 py-0.5 rounded hover:bg-gray-600 mr-2 text-xs"
                                onclick="document.getElementById('addVariantModal').classList.add('hidden')">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn bg-blue-600 text-white px-2 py-0.5 rounded hover:bg-blue-700 text-xs">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Variant Modal -->
        <div id="editVariantModal" class="modal hidden fixed inset-0 items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center p-4 border-b">
                    <h5 class="text-base font-semibold">Edit Variant</h5>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-sm"
                            onclick="document.getElementById('editVariantModal').classList.add('hidden')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="editVariantForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-4">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label block text-gray-700">Variant Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control w-full border rounded p-2 text-xs" required>
                            @error('name')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_values" class="form-label block text-gray-700">Values (comma-separated)</label>
                            <input type="text" name="values" id="edit_values" class="form-control w-full border rounded p-2 text-xs" required>
                            @error('values')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label block text-gray-700">Status</label>
                            <select name="status" id="edit_status" class="form-control w-full border rounded p-2 text-xs">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('status')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end p-4 border-t">
                        <button type="button" class="btn bg-gray-500 text-white px-2 py-0.5 rounded hover:bg-gray-600 mr-2 text-xs"
                                onclick="document.getElementById('editVariantModal').classList.add('hidden')">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn bg-blue-600 text-white px-2 py-0.5 rounded hover:bg-blue-700 text-xs">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
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
            const table = $('#variantsTable').DataTable({
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
                    text: 'This variant and its values will be deleted.',
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

            // Populate Edit Modal
            window.openEditModal = function(button) {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const values = button.getAttribute('data-values');
                const status = button.getAttribute('data-status');

                // Populate form fields
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_values').value = values;
                document.getElementById('edit_status').value = status;

                // Set form action
                document.getElementById('editVariantForm').action = '/admin/variants/' + id;

                // Show modal
                document.getElementById('editVariantModal').classList.remove('hidden');
            };
        });
    </script>
@endsection
