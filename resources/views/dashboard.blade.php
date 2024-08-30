@extends('components.app')

@section('content')
<div class="container">

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

    @if(Auth::user()->role->name !== 'IT' )
        <div class="row">
            <div class="col mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title">Pending Entries</h3>
                                <h1 id="entriesCount" class="{{ $entriesCount == 0 ? 'text-warning' : 'text-danger' }} mt-1">
                                    {{ $entriesCount }}
                                </h1>
                            </div>
                            <div>
                                <i class="bx bxs-time {{ $entriesCount == 0 ? 'text-warning' : 'text-danger' }} dash-icon"></i>
                            </div>
                        </div>
                    </div>
                    {{-- @if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP') --}}
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a href="/entries" class="{{ $entriesCount == 0 ? 'text-warning' : 'text-danger' }}">View Details</a>
                        <a href="/entries" class="{{ $entriesCount == 0 ? 'text-warning' : 'text-danger' }}"><i class="fas fa-arrow-circle-right text-primary"></i></a>
                    </div>
                    {{-- @endif --}}
            </div>
        </div>
    @endif
{{-- 
        <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="card-title">Roles</h2>
                            <h3 id="roleCount">{{ $roleCount }}</h3>
                        </div>
                        <div>
                            <i class="fas fa-user-tag fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
                @if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP')
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="/roles" class="text-success">View Details</a>
                    <a href="/roles" class="text-success"><i class="fas fa-arrow-circle-right text-success"></i></a>
                </div>
                @endif
            </div>
        </div> --}}
    </div>
    @if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP')
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="card-title">Users</h2>
                                <h3 id="userCount">{{ $userCount }}</h3>
                            </div>
                            <div>
                                <i class="bx bxs-user text-primary dash-icon"></i>
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP')
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a href="/user" class="text-primary">View Details</a>
                        <a href="/user" class="text-primary"><i class="fas fa-arrow-circle-right text-primary"></i></a>
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="card-title">Roles</h2>
                                <h3 id="roleCount">{{ $roleCount }}</h3>
                            </div>
                            <div>
                                <i class="bx bxs-purchase-tag text-success dash-icon"></i>
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP')
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a href="/roles" class="text-success">View Details</a>
                        <a href="/roles" class="text-success"><i class="fas fa-arrow-circle-right text-success"></i></a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('components.specific_page_scripts')
<script>
    $(function() {
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
    });
</script>
@endsection