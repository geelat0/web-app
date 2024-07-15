@extends('app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0">User Management</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="users-table" class="table table-striped table-bordered" style="width:100%">
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('user_page.create')
@include('user_page.edit')
@include('user_page.view')
@endsection

@section('scripts')
<script src="{{ asset('js/registration.js') }}"></script>
<script>
    $(document).ready(function() {

        var table;
        
        table = $('#users-table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 30,
            lengthChange: false,
            paging: false,
            ordering: false,
            scrollY: 400,
            select: {
                style: 'single' // Allow only single row selection
            },
            ajax: '{{ route('user.data') }}',

            buttons: [
                {
                    text: 'Reload',
                    className: 'btn btn-warning user_btn',
                    action: function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }
                },
                {
                    text: 'Add',
                    className: 'btn btn-success user_btn',
                    action: function (e, dt, node, config) {
                        $('#createUserModal').modal('show');
                    }
                },
                {
                    text: 'Edit',
                    className: 'btn btn-primary user_btn',
                    enabled: false,
                    action: function (e, dt, node, config) {

                        var selectedData = dt.row({ selected: true }).data();
                        $('#edit_user_id').val(selectedData.id);
                        $('#edit_firtsname').val(selectedData.first_name);
                        $('#edit_lastname').val(selectedData.last_name);
                        $('#edit_middlesname').val(selectedData.middle_name);
                        $('#edit_contactNumber').val(selectedData.mobile_number);
                        $('#edit_suffix').val(selectedData.suffix);
                        $('#edit_email').val(selectedData.email);
                        $('#edit_position').val(selectedData.position);
                        $('#edit_province').val(selectedData.province);
                        $('#edit_role').val(selectedData.role_id).change();

                        $('#editUserModal').modal('show');
                    }
                },
                {
                    text: 'View',
                    className: 'btn btn-info user_btn',
                    enabled: false,
                    action: function (e, dt, node, config) {
                        //alert('View Activated!');

                        var selectedData = dt.row({ selected: true }).data();
                        $('#viewt_user_id').val(selectedData.id);
                        $('#view_firtsname').val(selectedData.first_name);
                        $('#view_lastname').val(selectedData.last_name);
                        $('#view_middlesname').val(selectedData.middle_name);
                        $('#view_contactNumber').val(selectedData.mobile_number);
                        $('#view_suffix').val(selectedData.suffix);
                        $('#view_email').val(selectedData.email);
                        $('#view_position').val(selectedData.position);
                        $('#view_province').val(selectedData.province);
                        $('#view_role').val(selectedData.role_id).change();

                        $('#viewUserModal').modal('show');
                    }
                },
                {
                    text: 'Delete',
                    className: 'btn btn-danger user_btn',
                    enabled: false,
                    action: function (e, dt, node, config) {
                        alert('Delete Activated!');
                    }
                }
            ],

            columns: [
                // { data: 'id', name: 'id', title: 'ID' },
                { data: 'user_name', name: 'user_name', title: 'User Name' },
                { data: 'name', name: 'name', title: 'Name' },
                { data: 'email', name: 'email', title: 'Email' },
                { data: 'position', name: 'position', title: 'Position' },
                { data: 'province', name: 'province', title: 'Province' },
                { data: 'role', name: 'role', title: 'Role' },
                { data: 'created_by', name: 'created_by', title: 'Created By' },
                { data: 'created_at', name: 'created_at', title: 'Created At' },
                // { data: 'password', name: 'password', title: 'Password' },
            ],

            language: {
                emptyTable: "No users found"
            },

            dom: '<"d-flex justify-content-between flex-wrap"fB>rtip', // Adjust DOM layout
        });

        table.buttons().container().appendTo('#users-table_wrapper .col-md-6:eq(0)');

        table.on('select deselect', function() {
            var selectedRows = table.rows({ selected: true }).count();
            table.buttons(['.btn-primary', '.btn-info', '.btn-danger']).enable(selectedRows > 0);
        });

        $('#createUserModal').on('hidden.bs.modal', function () {
            $('#createUserForm')[0].reset();
        });
    });

    $('#createUserForm').on('submit', function(e) {
        e.preventDefault();
        // Handle form submission, e.g., via AJAX
        var formData = $(this).serialize();
        $.ajax({
            url: '{{ route('users.store') }}', 
            method: 'POST',
            data: formData,
            success: function(response) {

                 if (response.success) {
                    $('#createUserModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message, // Assuming your API returns a message
                            showConfirmButton: true,
                        })
                        table.ajax.reload();
                    }else{
                        var errors = response.errors;
                        var errorMessage = '';
                        Object.keys(errors).forEach(function(key) {
                            errorMessage += key + ': ' + errors[key][0] + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            html: errorMessage
                        });
                    }
                
            },
            error: function(xhr) {
                // Handle error
                console.log(xhr.responseText);
            }
        });
    });

    $('#editUserForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: '{{ route('users.update') }}', 
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editUserModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: true,
                        })
                        table.ajax.reload();
                    } else {
                        var errors = response.errors;
                        var errorMessage = '';
                        Object.keys(errors).forEach(function(key) {
                            errorMessage += key + ': ' + errors[key][0] + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            html: errorMessage
                        });
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
         });
    });


    $(document).ready(function() {
        $.ajax({
            url: '{{ route('get.role') }}',
            type: 'GET',
            success: function(data) {
                var rolesDropdown = $('#rolesDropdown');
                var editRolesDropdown = $('#edit_role');
                var viewRolesDropdown = $('#view_role');
                data.forEach(function(role) {
                    rolesDropdown.append('<option value="' + role.id + '">' + role.name + '</option>');
                    editRolesDropdown.append('<option value="' + role.id + '">' + role.name + '</option>');
                    viewRolesDropdown.append('<option value="' + role.id + '">' + role.name + '</option>');

                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching roles:', error);
            }
        });
    });


</script>

@endsection
