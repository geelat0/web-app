@extends('app')

@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Login History</h4>
        {{-- <p class="card-description"> Add class <code>.table-bordered</code> --}}
        </p>
        <div class="table-responsive pt-3">
          <table id="users-table"  class="table table-striped" style="width: 100%">
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        var file_name = 'Login History ' + new Date().toISOString().split('T')[0];

        var table;
        
        table = $('#users-table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 30,
            lengthChange: true,
            paging: false,
            ordering: false,
            scrollY: 400,
            // select: {
            //     style: 'single',
            // },
            ajax: '{{ route('list') }}',

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

                    extend: 'collection',
                    text: 'Actions',
                    className: 'btn btn-primary user_btn',
                    buttons: [
                            {
                                extend: 'excelHtml5',
                                text: 'Export to Excel',
                                filename: function () {
                                    return file_name;
                                }
                            },
                            {
                                extend: 'pdf',
                                text: 'Export to PDF',
                                filename: function () {
                                    return file_name;
                                },
                                title: 'Login History',
                                orientation: 'portrait',
                                pageSize: 'A4',
                                exportOptions: {
                                    modifier: {
                                        page: 'current'
                                    }
                                }
                            },
                            {
                                extend: 'csvHtml5',
                                text: 'Export to CSV',
                                filename: function () {
                                    return file_name;
                                }
                            },
                    ]

                }
                
            ],

            columns: [
                // { data: 'id', name: 'id', title: 'ID', visible: false },
                { data: 'user', name: 'user', title: 'Login User' },
                { data: 'date_time_in', name: 'date_time_in', title: 'Date Time in' },
                { data: 'status', name: 'status', title: 'Status' },
            ],

            language: {
                emptyTable: "No users found"
            },

            dom: '<"d-flex justify-content-between flex-wrap"fB>rtip', // Adjust DOM layout
        });

        // table.buttons().container().appendTo('#users-table_wrapper .col-md-6:eq(0)');

        // table.on('select deselect', function() {
        //     var selectedRows = table.rows({ selected: true }).count();
        //     table.buttons(['.btn-primary', '.btn-info', '.btn-danger']).enable(selectedRows > 0);
        // });       

    });


</script>

@endsection
