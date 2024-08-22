
@extends('components.app')

@section('content')

<div class="row">
    <div class="col">
        
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Generate OPCR</h4>
                {{-- <p class="card-description"> Add class <code>.table-bordered</code> --}}
                </p>
                <div class="row">
                    <div class="col">
                        <form id="GenerateForm">
                            @csrf
                            {{-- <div class="form-group mb-4">
                                <label for="flatpickr-date" class="form-label">Select a date range</label>
                                <input type="text" id="date-range-picker" name="created_at" class="form-control" placeholder="YYYY-MM-DD" id="flatpickr-date" />
                            </div> --}}

                            <div class="form-group mb-3 me-3">
                                <label for="year" class="required">Year</label>
                                <select id="year" class="form-select" name="year">
                                    <option value="">Select Year</option>
                                    @for ($i = date('Y'); $i >= 2020; $i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group mb-3 me-3">
                                <label for="period">Period</label>
                                <select id="period" class="form-select" name="period">
                                    <option value="">Select Period</option>
                                    <optgroup label="Quarter">
                                        <option value="Q1">Q1 (Jan-Mar)</option>
                                        <option value="Q2">Q2 (Apr-Jun)</option>
                                        <option value="Q3">Q3 (Jul-Sep)</option>
                                        <option value="Q4">Q4 (Oct-Dec)</option>
                                    </optgroup>
                                    <optgroup label="Semestral">
                                        <option value="H1">S1 (Jan-Jun)</option>
                                        <option value="H2">S2 (Jul-Dec)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="form-group mb-3 me-3">
                                <label for="division_id">Division</label>
                                <select id="division_id" class="division-select form-select" name="division_id[]">
                                </select>
                                <div class="invalid-feedback" id="division_idError"></div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('components.specific_page_scripts')

<script>
        flatpickr("#date-range-picker", {
            mode: "range",
            dateFormat: "m/d/Y",
            onChange: function(selectedDates, dateStr, instance) {
                // Check if both start and end dates are selected
                if (selectedDates.length === 2) {
                      
                }
            }
        });
        
        $('#GenerateForm').on('submit', function(e) {
            e.preventDefault();
            showLoader();

            var year = $('#year').val();
            var divisions = $('#division_id').val();

            if (year === "") {
                hideLoader();
                Swal.fire({
                    icon: 'warning',
                    title: 'Year Required',
                    text: 'Please select a year before generating the report.',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
                return;
            }

            $.ajax({
                url: '{{ route('generate.pdf') }}',
                type: 'POST',
                data: $(this).serialize(),
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(blob) {
                    hideLoader();

                    var filename = year + '-OPCR-RO5.pdf';
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'The report has been generated and downloaded.',
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    });
                },
                error: function(xhr) {
                    hideLoader();
                    console.log(xhr);
                }
            });
        });
</script>


<script>
     function initializeDivisionSelect() {
        $('.division-select').select2({
            placeholder: 'Select an Option',
            allowClear: true,
            ajax: {
                url: '{{ route('indicator.getDivision') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.division_name
                            };
                        })
                    };
                },
                cache: true
            }
        });
    }
    initializeDivisionSelect();

</script>
@endsection