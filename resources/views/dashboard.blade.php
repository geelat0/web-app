@extends('components.app')

@section('content')
<div class="container">
    @can('filter_dashboard')
    <div class="d-flex justify-content-start mb-4">
        <i class='bx bxs-filter-alt text-primary' id="filterIcon" style="cursor: pointer; font-size: 30px;"></i>
    </div>
    
    <!-- Filters Section (Hidden by Default) -->
    <div id="filtersSection" class="hidden">
        <div class="row mb-3 justify-content-center">
            <div class="col">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
                </div>
            </div>
            <div class="col">
                <div class="input-group">
                    <select id="month" class="form-select">
                        <option value="">Select Month</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col">
                <div class="input-group">
                    <select id="year" class="form-select">
                        <option value="">Select Year</option>
                        @for ($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <button class="btn btn-primary" id="filterBtn"> <i class="mdi mdi-filter-outline"></i> Filter</button>
                </div>
            </div>
        </div>
    </div>
    
    @endcan

    @can('access_pending_entries')
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card" style="width: 100%; margin-bottom: 1rem;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title">Pending Entries For {{\Carbon\Carbon::create()->month($targetMonth)->format('F')}}</h4>
                                <h3 id="entriesCount" class="{{ $entriesCount == 0 ? 'text-info' : 'text-danger' }} mt-1">
                                    {{ $entriesCount }}
                                </h3>
                            </div>
                            <div>
                                <i class="bx bxs-bell-ring {{ $entriesCount == 0 ? 'text-info' : 'text-danger' }} dash-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" style="width: 100%; margin-bottom: 1rem;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title">Completed Entries For {{\Carbon\Carbon::create()->month($targetMonth)->format('F')}}</h4>
                                <h3 id="CompleteEntriesCount" class="text-primary mt-1">
                                    {{ $CompleteEntriesCount }}
                                </h3>
                            </div>
                            <div>
                                <i class="bx bx-task text-primary dash-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- </div> --}}
    @if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP')
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card" style="width: 100%; margin-bottom: 1rem;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">Roles</h3>
                            <h3 id="roleCount">{{ $roleCount }}</h3>
                        </div>
                        <div>
                            <i class="bx bxs-purchase-tag text-success dash-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="width: 100%; margin-bottom: 1rem;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">Users</h3>
                            <h3 id="userCount">{{ $userCount }}</h3>
                        </div>
                        <div>
                            <i class="bx bxs-user text-primary dash-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="width: 100%; margin-bottom: 1rem;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">Login</h3>
                            <h3 id="loggedInUsersCount">{{ $loggedInUsersCount ?  $loggedInUsersCount : 0}}</h3>
                        </div>
                        <div>
                            <i class='bx bx-history text-warning dash-icon'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @endif
    
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Who's login now?</h3>
                  {{-- <p class="card-description"> Add class <code>.table-bordered</code> --}}
                  </p>
                  <div class="table-responsive pt-3">
                    <table id="login-table"  class="table table-striped" style="width: 100%">
                      <tbody>
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>
        </div>
    </div>


</div>
@endsection

@section('components.specific_page_scripts')
<script>
    $(function() {

        function fetchDashboardData() {
            $.ajax({
                url: '{{ route('fetch.dashboard.data') }}',
                method: 'GET',
                success: function(data) {
                    $('#userCount').text(data.userCount);
                    $('#roleCount').text(data.roleCount);
                    $('#entriesCount').text(data.entriesCount);
                    $('#loggedInUsersCount').text(data.loggedInUsersCount);
                    $('#CompleteEntriesCount').text(data.CompleteEntriesCount);
                }
            });
        }

        // Fetch dashboard data on page load
        fetchDashboardData();
        setInterval(fetchDashboardData, 30000);

        $('#filterIcon').click(function() {
            $('#filtersSection').toggleClass('hidden');
        });
        // Initialize Flatpickr date range picker
        flatpickr("#flatpickr-range", {
            mode: "range",
            dateFormat: "Y-m-d",
        });

        // Set current month and year as the selected values
        var now = new Date();
        var currentMonth = now.getMonth() + 1; // getMonth() returns month from 0-11
        var currentYear = now.getFullYear();

        $('#month').val(currentMonth);
        $('#year').val(currentYear);

        // Handle the filter button click
        $('#filterBtn').on('click', function() {
            var dateRange = $('#flatpickr-range').val();
            var [startDate, endDate] = dateRange ? dateRange.split(' to ') : [null, null];
            var month = $('#month').val();
            var year = $('#year').val();
            showLoader();

            $.ajax({
                url: '{{ route("dashboard.filter") }}',
                method: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    month: month,
                    year: year
                },
                success: function(data) {
                    hideLoader();
                    $('#userCount').text(data.userCount);
                    $('#roleCount').text(data.roleCount);
                    $('#entriesCount').text(data.entriesCount);
                }
            });
        });

        var table;

        table = $('#login-table').DataTable({
            responsive: true,
            processing: false,
            serverSide: true,
            pageLength: 30,
            lengthChange: true,
            paging: false,
            ordering: false,
            scrollY: 400,
            // select: {
            //     style: 'single',
            // },
            ajax: {
                url: '{{ route('Loginlist') }}',
                data: function(d) {
                    // Include the date range in the AJAX request
                    d.date_range = $('#flatpickr-range').val();
                    d.search = $('#search-input').val();
                },
            },

            columns: [
                // { data: 'id', name: 'id', title: 'ID', visible: false },
                { data: 'user', name: 'user', title: 'Name' },
                { data: 'user_name', name: 'user_name', title: 'User Name' },
                { data: 'position', name: 'position', title: 'Position' },
                { data: 'role', name: 'role', title: 'Role' },
                // { data: 'last_activity', name: 'last_activity', title: 'last_activity' },
                // { data: 'time_in', name: 'time_in', title: 'Time in' },
            ],

            language: {
                emptyTable: "No data found",
                search: "", // Remove "Search:" label
                searchPlaceholder: "Search..." // Set placeholder text
            },

            dom: '<"d-flex justify-content-between flex-wrap">rtip',  // Adjust DOM layout
        });

        function reloadTable() {
            table.ajax.reload(null, false); // Reloads the table data without resetting pagination
        }

        // Reload table every 1 minute (60000 milliseconds)
        setInterval(reloadTable, 30000);
        console.log('here');

    });
</script>
@endsection