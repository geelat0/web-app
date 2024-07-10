@extends('app')

@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0">User Management</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="users-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('user.data') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'user_name', name: 'user_name' },
                { data: 'email', name: 'email' },
                { data: 'created_at', name: 'created_at' },
            ],
            "language": {
                "emptyTable": "No users found"
            },
            pageLength: 30,
            lengthChange: false,  // Remove the "Show" entries dropdown
            paging: false,        // Remove pagination controls
            ordering: false       // Remove sorting symbols
        });

        // Add buttons to DataTable
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    text: 'Edit',
                    className: 'btn btn-primary',
                    action: function (e, dt, node, config) {
                        // Add edit action logic here
                    }
                },
                {
                    text: 'Delete',
                    className: 'btn btn-danger',
                    action: function (e, dt, node, config) {
                        // Add delete action logic here
                    }
                }
            ]
        });

        // Place buttons on the right side
        table.buttons().container().appendTo($('#users-table_wrapper').find('.dataTables_length'));
    });
</script>
@endsection
