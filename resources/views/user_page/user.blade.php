@extends('app')

@section('content')
<div class="col-lg-12 grid-margin stretch-card">

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">User Management</h4>
            {{-- <p class="card-description"> Add class <code>.table-bordered</code> --}}
            </p>
            <div class="row">
                <div class="col">
                    <p class="d-inline-flex gap-1">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                            <i class="mdi mdi-filter-outline"></i> Filter
                        </button>
                    </p>
                    
                </div>
                <div class="col d-flex justify-content-end mb-3" >

                    <div id="table-buttons" class="d-flex">
                        <!-- Buttons will be appended here -->
                    </div>

                </div>
                
            </div>
            <div class="collapse" id="collapseExample">
                <div class="card card-body">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="input-group input-group-sm me-3">
                            <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Search...">
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" id="date-range-picker" class="form-control form-control-sm" placeholder="Select date range">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-12 grid-margin stretch-card">

    <div class="card">
        <div class="card-body">
            <div class="table-responsive pt-3">
                <table id="users-table" class="table table-striped" style="width: 100%">
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

<script>
    $(document).ready(function() {

        var table;
        
        table = $('#users-table').DataTable({
            responsive: true,
            processing: false,
            serverSide: true,
            pageLength: 30,
            lengthChange: false,
            paging: false,
            ordering: false,
            scrollY: 400,
            select: {
                style: 'single',
            },
            ajax: {
                url: '{{ route('user.list') }}',
                data: function(d) {
                    // Include the date range in the AJAX request
                    d.date_range = $('#date-range-picker').val();
                    d.search = $('#search-input').val();
                }
            },
            buttons: [
                {
                    text: 'Reload',
                    className: 'btn btn-warning user_btn',
                    enabled: true,
                    action: function ( e, dt, node, config ) {
                        dt.ajax.reload();
                    }
                },
                {
                    text: 'Add',
                    className: 'btn btn-success user_btn',
                    enabled: true,
                    action: function (e, dt, node, config) {
                        $('#createUserModal').modal('show');
                        $('#createUserForm').on('submit', function(e) {
                            e.preventDefault();
                            showLoader();
                            // Handle form submission, e.g., via AJAX
                            var formData = $(this).serialize();
                            $.ajax({
                                url: '{{ route('users.store') }}', 
                                method: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        $('#createUserModal').modal('hide');
                                            hideLoader();
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
                                                var inputField = $('#createUserForm [name=' + key + ']');
                                                inputField.addClass('is-invalid');
                                                $('#createUserForm #' + key + 'Error').text(errors[key][0]);
                                            });
                                            hideLoader();
                                            
                                        }
                                    
                                },
                                error: function(xhr) {
                                    hideLoader();
                                    // Handle error
                                    console.log(xhr.responseText);
                                }
                            });
                        });
                    }
                },
                {
                    text: 'Edit',
                    className: 'btn btn-info user_btn',
                    enabled: false,
                    action: function (e, dt, node, config) {
                        $('#editUserModal').modal('show');

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

                        $('#editUserForm').off('submit').on('submit', function(e) {
                                e.preventDefault();
                                showLoader();
                                var formData = $(this).serialize();
                                $.ajax({
                                    url: '{{ route('users.update') }}', 
                                    method: 'POST',
                                    data: formData,
                                    success: function(response) {
                                        
                                        if (response.success) {
                                            $('#editUserModal').modal('hide');
                                            hideLoader();
                                            
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
                                                var inputField = $('#editUserForm [name=' + key + ']');
                                                inputField.addClass('is-invalid');
                                                $('#editUserForm #' + key + 'Error').text(errors[key][0]);
                                            });
                                            hideLoader();
                                        }
                                    },
                                    error: function(xhr) {
                                        hideLoader();
                                        console.log(xhr.responseText);
                                    }
                            });
                        });
                    }
                },
                {
                    text: 'View',
                    className: 'btn btn-primary user_btn',
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
                                showLoader(); 
                                $.ajax({
                                    url: '{{ route('users.destroy') }}',
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        id: selectedUserId
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            hideLoader();
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
                                            hideLoader();
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Deletion Failed',
                                                html: errorMessage
                                            });
                                        }
                                    },
                                    error: function(xhr) {
                                        hideLoader();
                                        console.log(xhr.responseText);
                                    }
                                });
                            }
                        });
                    }
                },
                {

                    extend: 'collection',
                    text: 'Actions',
                    enabled: false,
                    className: 'btn btn-primary user_btn',
                    buttons: [
                        {
                            text: 'Temporary Password',
                            action: function (e, dt, node, config) {

                                var selectedData = dt.row({ selected: true }).data();
                                showLoader();

                                $.ajax({
                                    url: '{{ route('users.temp-password') }}', 
                                    method: 'POST',
                                    data: {
                                        id: selectedData.id,
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            hideLoader();
                                            
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success!',
                                                text: response.message,
                                                showConfirmButton: true,
                                            })
                                           
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error!',
                                                text: response.message,
                                                showConfirmButton: true,
                                            })
                                            hideLoader();
                                        }
                                    },
                                    error: function(xhr) {
                                        hideLoader();
                                        console.log(xhr.responseText);
                                    }
                            });
                                console.log(selectedData.id);
                                
                            }
                        },

                        {
                            text: 'Change Status',
                            action: function(e, dt, node, config) {
                                var selectedData = dt.row({ selected: true }).data();
                                Swal.fire({
                                    title: 'Change Status',
                                    input: 'select',
                                    inputOptions: {
                                        'Active': 'Active',
                                        'Inactive': 'Inactive',
                                        'Blocked': 'Blocked',
                                    },
                                    inputPlaceholder: selectedData.status,
                                    showCancelButton: true,
                                    inputValidator: (value) => {
                                        return new Promise((resolve) => {
                                            if (value) {
                                                resolve();
                                            } else {
                                                resolve('You need to select a status');
                                            }
                                        });
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        showLoader();
                                        $.ajax({
                                            url: '{{ route('users.change-status') }}',
                                            method: 'POST',
                                            data: {
                                                id: selectedData.id,
                                                status: result.value,
                                                _token: '{{ csrf_token() }}'
                                            },
                                            success: function(response) {
                                                hideLoader();
                                                if (response.success) {
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: 'Success!',
                                                        text: response.message,
                                                        showConfirmButton: true
                                                    });
                                                    table.ajax.reload();
                                                } else {
                                                    hideLoader();
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error!',
                                                        text: response.message,
                                                        showConfirmButton: true
                                                    });
                                                }
                                            },
                                            error: function(xhr) {
                                                hideLoader();
                                                console.log(xhr.responseText);
                                            }
                                        });
                                    }
                                });
                            }
                        },

                        {
                            text: 'Proxy Login',
                            action: function (e, dt, node, config) {

                                var selectedData = dt.row({ selected: true }).data();
                                showLoader();

                                $.ajax({
                                    url: '{{ route('users.gen-proxy') }}', 
                                    method: 'POST',
                                    data: {
                                        id: selectedData.id,
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            hideLoader();
                                            window.location.href = response.redirect;
                                           
                                        } else {
                                            var errors = response.errors;
                                            Object.keys(errors).forEach(function(key) {
                                                var inputField = $('#editUserForm [name=' + key + ']');
                                                inputField.addClass('is-invalid');
                                                $('#editUserForm #' + key + 'Error').text(errors[key][0]);
                                            });
                                            hideLoader();
                                        }
                                    },
                                    error: function(xhr) {
                                        hideLoader();
                                        console.log(xhr.responseText);
                                    }
                            });
                                console.log(selectedData.id);
                                
                            }
                        },

                        {
                            text: 'Disabled 2FA Auth',
                            action: function(e, dt, node, config) {
                                var selectedData = dt.row({ selected: true }).data();
                                showLoader();

                                $.ajax({
                                    url: '{{ route('twofaDisabled') }}', 
                                    method: 'POST',
                                    data: {
                                        id: selectedData.id,
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            hideLoader();
                                            
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success!',
                                                text: response.message,
                                                showConfirmButton: true,
                                            })
                                           
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error!',
                                                text: response.message,
                                                showConfirmButton: true,
                                            })
                                            hideLoader();
                                        }
                                    },
                                    error: function(xhr) {
                                        hideLoader();
                                        console.log(xhr.responseText);
                                    }
                                });
                                console.log(selectedData.id);
                            }
                            

                        },
                       
                    ]

                }
                
            ],

            columns: [
                { data: 'id', name: 'id', title: 'ID', visible: false },
                { data: 'user_name', name: 'user_name', title: 'User Name' },
                { data: 'name', name: 'name', title: 'Name' },
                { data: 'email', name: 'email', title: 'Email' },
                { data: 'position', name: 'position', title: 'Position' },
                { data: 'province', name: 'province', title: 'Province' },
                { data: 'role', name: 'role', title: 'Role' },
                { data: 'status', name: 'status', title: 'Status' },
                { data: 'created_by', name: 'created_by', title: 'Created By' },
                { data: 'created_at', name: 'created_at', title: 'Created At' },
            ],

            language: {
                emptyTable: "No data found",
                search: "", // Remove "Search:" label
                searchPlaceholder: "Search..." // Set placeholder text
            },

            dom: '<"d-flex justify-content-between flex-wrap"B>rtip',  // Adjust DOM layout
        });

        $('.navbar-toggler').on('click', function() {
        // Reload the DataTable
            table.ajax.reload(null, false); // false to keep the current paging
        });


        // table.buttons().container().appendTo('#users-table_wrapper .col-md-6:eq(0)');
        table.buttons().container().appendTo('#table-buttons');

        $('#search-input').on('keyup', function() {
            table.ajax.reload();  // Reload the table when the search input changes
        });

        table.on('select deselect', function() {
            var selectedRows = table.rows({ selected: true }).count();
            table.buttons(['.btn-primary', '.btn-info', '.btn-danger']).enable(selectedRows > 0);
        });

        $('#createUserModal, #editUserModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset(); // Reset form fields
            $(this).find('.is-invalid').removeClass('is-invalid'); // Remove validation error classes
            $(this).find('.invalid-feedback').text(''); // Clear error messages
        });


        // START DATE RANGE JS
        $('#date-range-picker').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#date-range-picker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            table.ajax.reload(null, false);  // Reload the table with the new date range
        });

        $('#date-range-picker').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            table.ajax.reload(null, false);  // Reload the table when the date range is cleared
        });

        $('#filter-date').click(function() {
            table.ajax.reload(null, false); 
        });

         // END DATE RANGE JS

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


    $(document).ready(function() {
       
    });


</script>

@endsection
