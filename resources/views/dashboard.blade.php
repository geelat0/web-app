@extends('components.app')

@section('content')
<div class="container">

    <h2 class="mt-4">Dashboard</h2>
    <p class="mb-3">Welcome to your dashboard, {{ $user->user_name }}</p>

    <div class="row mb-4 justify-content-center">
        <div class="col">
            <div class="input-group">
                <input type="text" id="date-range-picker" class="form-control" placeholder="Select Date Range">
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
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="/user" class="text-primary">View Details</a>
                    <a href="/user" class="text-primary"><i class="fas fa-arrow-circle-right text-primary"></i></a>
                </div>
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
                            <i class="fas fa-user-tag fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="/roles" class="text-success">View Details</a>
                    <a href="/roles" class="text-success"><i class="fas fa-arrow-circle-right text-success"></i></a>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="row">
        <div class="col stretch-card">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title">Bar chart</h4>
                <canvas id="barChart"></canvas>
              </div>
            </div>
          </div>
        <div class="col stretch-card">
            <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Pie chart</h4>
                  <div class="doughnutjs-wrapper d-flex justify-content-center">
                    <canvas id="pieChart"></canvas>
                  </div>
                </div>
              </div>
          </div>
    </div> --}}
</div>
@endsection

@section('components.specific_page_scripts')
{{-- <script>
$(function() {
    // Initialize date range picker
    $('#date-range-picker').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('#date-range-picker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('#date-range-picker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Handle the filter button click
    $('#filterBtn').on('click', function() {
        var dateRange = $('#date-range-picker').val();
        var [startDate, endDate] = dateRange ? dateRange.split(' - ') : [null, null];
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
            }
        });
    });
});
</script> --}}
@endsection
