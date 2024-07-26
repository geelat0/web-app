@extends('app')  {{-- Main blade File --}}


{{-- Content of Pages --}}
@section('content')

<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Organizational Outcome/PAP</h4>
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
        
        {{-- <p class="card-description"> Add class <code>.table-bordered</code> --}}
        </p>
        
        <div class="table-responsive pt-3">
          <table id="org-table"  class="table table-striped" style="width: 100%">
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
    @include('org_outcome.create')
    @include('org_outcome.edit')
    @include('org_outcome.view')
@endsection


{{-- JS of Pages --}}
@section('scripts')

<script>
    $(document).ready(function() {

        var table;

        table = $('#org-table').DataTable({
            responsive: true,
            processing: false,
            serverSide: true,
            pageLength: 30,
            lengthChange: false,
            paging: false,
            ordering: false,
            scrollY: 400,
            select: {
                style: 'single'
            },
            ajax: {
                url: '{{ route('org.list') }}',
                data: function(d) {
                    // Include the date range in the AJAX request
                    d.date_range = $('#date-range-picker').val();
                    d.search = $('#search-input').val();
                },
                // beforeSend: function() {
                //     showLoader(); // Show loader before starting the AJAX request
                // },
                // complete: function() {
                //     hideLoader(); // Hide loader after AJAX request completes
                // }
            },
            buttons: [
                // {
                //     text: 'Reload',
                //     className: 'btn btn-warning user_btn',
                //     action: function ( e, dt, node, config ) {
                //         dt.ajax.reload();
                //     }
                // },
                {
                    text: 'Add',
                    className: 'btn btn-success user_btn',
                    action: function (e, dt, node, config) {
                        $('#createOrgModal').modal('show');

                        $('#createOrgForm').on('submit', function(e) {
                            e.preventDefault();
                            showLoader();
                            // Handle form submission, e.g., via AJAX
                            var formData = $(this).serialize();
                            $.ajax({
                                url: '{{ route('org.store') }}', 
                                method: 'POST',
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        $('#createOrgModal').modal('hide');
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
                                            hideLoader();
                                           
                                            var errors = response.errors;
                                                Object.keys(errors).forEach(function (key) {
                                                    var inputField = $('#createOrgForm [name="organizational_outcome[]"]');
                                                    inputField.each(function() {
                                                        if ($(this).val() === '') {
                                                            $(this).addClass('is-invalid');
                                                            $(this).siblings('.invalid-feedback').text(errors[key][0]);
                                                        }
                                                    });
                                                });
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
                        $('#editOrgModal').modal('show');

                        var selectedData = dt.row({ selected: true }).data();
                        $('#edit_role_id').val(selectedData.id);
                        $('#edit_name').val(selectedData.organizational_outcome);
                        $('#edit_status').val(selectedData.status);

                        $('#editOrgForm').off('submit').on('submit', function(e) {
                                e.preventDefault();
                                showLoader();
                                var formData = $(this).serialize();
                                $.ajax({
                                    url: '{{ route('org.update') }}', 
                                    method: 'POST',
                                    data: formData,
                                    success: function(response) {
                                        if (response.success) {
                                            $('#editOrgModal').modal('hide');
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
                                                var inputField = $('#editOrgForm [name=' + key + ']');
                                                inputField.addClass('is-invalid');
                                                $('#editOrgForm #' + key + 'Error').text(errors[key][0]);
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
                    className: 'btn btn-warning user_btn',
                    enabled: false,
                    action: function (e, dt, node, config) {
                        //alert('View Activated!');

                        var selectedData = dt.row({ selected: true }).data();
                        $('#view_role_id').val(selectedData.id);
                        $('#view_name').val(selectedData.organizational_outcome);
                        $('#view_status').val(selectedData.status);
                        $('#viewOrgModal').modal('show');
                        
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
                                    url: '{{ route('org.destroy') }}',
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        id: selectedUserId
                                    },
                                    success: function(response) {
                                        hideLoader();
                                        if (response.success) {
                                            Swal.fire(
                                                'Deleted!',
                                                'Organization Outcome has been deleted.',
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
            
                
            ],

            columns: [
                { data: 'id', name: 'id', title: 'ID', visible: false },
                { data: 'organizational_outcome', name: 'organizational_outcome', title: 'Organizational Outcome' },
                { data: 'status', name: 'status', title: 'Status' },
                { data: 'created_by', name: 'created_by', title: 'Created By' },
                { data: 'created_at', name: 'created_at', title: 'Created At' },
            ],

            language: {
                emptyTable: "No data found",
                search: "", // Remove "Search:" label
                searchPlaceholder: "Search..." // Set placeholder text
            },

            dom: '<"d-flex justify-content-between flex-wrap"B>rtip', 
        });

        $('.navbar-toggler').on('click', function() {
        // Reload the DataTable
            table.ajax.reload(null, false); // false to keep the current paging
        });

        $('#search-input').on('keyup', function() {
            table.ajax.reload();  // Reload the table when the search input changes
        });

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

        // table.buttons().container().appendTo('#roles-table_wrapper .col-md-6:eq(0)');
        table.buttons().container().appendTo('#table-buttons');

        table.on('select deselect', function() {
            var selectedRows = table.rows({ selected: true }).count();
            table.buttons(['.btn-warning', '.btn-info', '.btn-danger']).enable(selectedRows > 0);
        });

        $('#createOrgModal, #editOrgModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset(); // Reset form fields
            $(this).find('.is-invalid').removeClass('is-invalid'); // Remove validation error classes
            $(this).find('.invalid-feedback').text(''); // Clear error messages
        });

        let outcomeIndex = 1;

        $('#addOutcomeBtn').click(function () {
            const newOutcomeHtml = `
                <div class="form-group mt-3" id="organizational_outcome_group_${outcomeIndex}">
                    <label for="organizational_outcome_${outcomeIndex}" class="required">Organization Outcome</label>
                    <input type="text" class="form-control capitalize" name="organizational_outcome[]" id="organizational_outcome_${outcomeIndex}" aria-describedby="">
                    <div class="invalid-feedback" id="organizational_outcome_${outcomeIndex}Error"></div>
                    <button type="button" class="btn btn-danger btn-sm mt-2 removeOutcomeBtn" data-index="${outcomeIndex}"><i class="mdi mdi-delete"></i></button>
                </div>
            `;
            $('#organizational_outcomes').append(newOutcomeHtml);
            outcomeIndex++;
        });

        $(document).on('click', '.removeOutcomeBtn', function () {
            const index = $(this).data('index');
            $(`#organizational_outcome_group_${index}`).remove();
        });

    });
</script>

@endsection
