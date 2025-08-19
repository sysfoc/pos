@extends('layouts.admin')

@section('title', 'Customer Management')
@section('content-header', 'Customer Management')
@section('content-actions')
    <a href="{{ route('customers.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Add New Customer</a>
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        /* Decrease table text size */
        .table th,
        .table td {
            font-size: 0.7rem;
            /* smaller than default */
        }
    </style>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>CNIC</th>
                        <th>NTN Number</th>
                        <th>STN</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>{{ $customer->first_name }} {{ $customer->last_name }}</td>
                            <td>
                                <span title="{{ $customer->email }}">
                                    {{ \Illuminate\Support\Str::limit($customer->email, 10, '...') }}
                                </span>
                            </td>

                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->cnic }}</td>
                            <td>{{ $customer->ntn_number }}</td>
                            <td>{{ $customer->fbr_number }}</td>
                            <td>
                                <span title="{{ $customer->address }}">
                                    {{ \Illuminate\Support\Str::limit($customer->address, 10, '...') }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-danger btn-sm btn-delete"
                                    data-url="{{ route('customers.destroy', $customer) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $customers->render() }}
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
                    text: "Do you really want to delete this customer?",
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
                        }, function() {
                            $this.closest('tr').fadeOut(500, function() {
                                $(this).remove();
                            })
                        })
                    }
                })
            })
        })
    </script>
@endsection
