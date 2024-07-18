@extends('app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0">Roles</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="roles-table" class="table table-striped table-bordered" style="width:100%">
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('role_page.create')
@include('role_page.edit')
@include('role_page.view')
@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        var table;
        
        table = $('#roles-table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 30,
            lengthChange: false,
            paging: false,
            ordering: false,
            scrollY: 400,
            select: {
                style: 'single'
            },
            ajax: '{{ route('role.list') }}',

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
                        $('#createRoleModal').modal('show');

                        $('#createRoleForm').on('submit', function(e) {
                            e.preventDefault();
                            // Handle form submission, e.g., via AJAX
                            var formData = $(this).serialize();
                            $.ajax({
                                url: '{{ route('role.store') }}', 
                                method: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        $('#createRoleModal').modal('hide');
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success!',
                                                text: response.message,
                                                showConfirmButton: true,
                                            })
                                            table.ajax.reload();
                                        }
                                        else{
                                            
                                            var errors = response.errors;
                                            Object.keys(errors).forEach(function(key) {
                                                var inputField = $('#createRoleForm [name=' + key + ']');
                                                inputField.addClass('is-invalid');
                                                $('#createRoleForm #' + key + 'Error').text(errors[key][0]);
                                            });
                                            
                                        }
                                    
                                },
                                error: function(xhr) {
                                    // Handle error
                                    console.log(xhr.responseText);
                                }
                            });
                        });
                    }
                },
                {
                    text: 'Edit',
                    className: 'btn btn-primary user_btn',
                    enabled: false,
                    action: function (e, dt, node, config) {
                        $('#editRoleModal').modal('show');

                        var selectedData = dt.row({ selected: true }).data();
                        $('#edit_role_id').val(selectedData.id);
                        $('#edit_name').val(selectedData.name);
                        $('#edit_status').val(selectedData.status);

                        $('#editRoleForm').off('submit').on('submit', function(e) {
                                e.preventDefault();
                                var formData = $(this).serialize();
                                $.ajax({
                                    url: '{{ route('role.update') }}', 
                                    method: 'POST',
                                    data: formData,
                                    success: function(response) {
                                        if (response.success) {
                                            $('#editRoleModal').modal('hide');
                                            
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success!',
                                                text: response.message,
                                                showConfirmButton: true,
                                            })

                                            table.ajax.reload();
                                           
                                        } else {
                                            var errors = response.errors;
                                            Object.keys(errors).forEach(function(key) {
                                                var inputField = $('#editRoleForm [name=' + key + ']');
                                                inputField.addClass('is-invalid');
                                                $('#editRoleForm #' + key + 'Error').text(errors[key][0]);
                                            });
                                        }
                                    },
                                    error: function(xhr) {
                                        console.log(xhr.responseText);
                                    }
                            });
                        });
                    }
                },
                {
                    text: 'View',
                    className: 'btn btn-info user_btn',
                    enabled: false,
                    action: function (e, dt, node, config) {
                        //alert('View Activated!');

                        var selectedData = dt.row({ selected: true }).data();
                        $('#view_role_id').val(selectedData.id);
                        $('#view_name').val(selectedData.name);
                        $('#view_status').val(selectedData.status);
                        $('#viewRoleModal').modal('show');
                        
                    }
                },
                {
                    text: 'Delete',
                    className: 'btn btn-danger user_btn',
                    enabled: false,
                    action: function (e, dt, node, config) {
                        //alert('Delete Activated!');

                        var selectedUserId = table.row({ selected: true }).data().id; // Assuming you have selected a user row

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '{{ route('role.destroy') }}',
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        id: selectedUserId
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            Swal.fire(
                                                'Deleted!',
                                                'User has been deleted.',
                                                'success'
                                            );
                                            table.ajax.reload();
                                        } else {
                                            var errorMessage = '';
                                            Object.keys(response.errors).forEach(function(key) {
                                                errorMessage += response.errors[key][0] + '<br>';
                                            });
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Deletion Failed',
                                                html: errorMessage
                                            });
                                        }
                                    },
                                    error: function(xhr) {
                                        console.log(xhr.responseText);
                                    }
                                });
                            }
                        });
                    }
                },
               
                
            ],

            columns: [
                { data: 'id', name: 'id', title: 'ID', visible: false },
                { data: 'name', name: 'name', title: 'Name' },
                { data: 'status', name: 'status', title: 'Status' },
                { data: 'created_by', name: 'created_by', title: 'Created By' },
                { data: 'created_at', name: 'created_at', title: 'Created At' },
            ],

            language: {
                emptyTable: "No users found"
            },

            dom: '<"d-flex justify-content-between flex-wrap"fB>rtip', // Adjust DOM layout
        });

        table.buttons().container().appendTo('#roles-table_wrapper .col-md-6:eq(0)');

        table.on('select deselect', function() {
            var selectedRows = table.rows({ selected: true }).count();
            table.buttons(['.btn-primary', '.btn-info', '.btn-danger', '.btn-secondary']).enable(selectedRows > 0);
        });

        $('#createRoleModal, #editRoleModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset(); // Reset form fields
            $(this).find('.is-invalid').removeClass('is-invalid'); // Remove validation error classes
            $(this).find('.invalid-feedback').text(''); // Clear error messages
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
