@extends('components.app')

@section('content')
<div class="container mt-5">

    <form id="NewIndicatorForm">
        @csrf
        <div class="row mt-4">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"> <a href="/indicator" class="text-primary"><i class='bx bx-left-arrow-circle'></i></a>
                            Edit</h4>
                        <div class="row">
                            <input type="hidden" name="id" value="{{ $indicator->id }}">
                            <div class="form-group">
                                <label for="org_id" class="required">Organizational Outcome</label>
                                <select id="org_id" class="form-select capitalize" name="org_id">
                                    @if($indicator)
                                        <option value="{{ $indicator->org_id }}" selected>{{ $indicator->org->organizational_outcome }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div id="cards-containers">
                <div class="col-lg-12 grid-margin stretch-card card-template cards-container">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group">
                                    <label for="measures" class="required">Measure</label>
                                    <textarea type="text" class="form-control capitalize" name="measures" id="measures_0" aria-describedby="">{{ $indicator->measures }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="division_id" class="required">Division</label>
                                    <select id="division_id_0" class="division-select form-select" name="division_id[]" multiple="multiple">
                                        @if($indicator)
                                            @foreach( $division_ids as $division)
                                                <option value="{{ $division }}" selected>{{ App\Models\Division::find($division)->division_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="invalid-feedback" id="division_idError"></div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="target" class="required">Target</label>
                                        <input type="text" class="form-control capitalize" name="target" id="target_0" aria-describedby="" value="{{ $indicator->target }}" {{ $indicator->targetType == 'actual' ? 'disabled' : '' }}>
                                        <div class="invalid-feedback" id="targetError[]"></div>
                                    </div>
                                    <div class="form-group">
                                        <input class="form-check-input" type="radio" name="targetType_0" id="Percentage_0" value="percentage" {{ $indicator->targetType == 'percentage' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="Percentage_0">Percentage</label>
                                    </div>
                                    <div class="form-group">
                                        <input class="form-check-input" type="radio" name="targetType_0" id="Number_0" value="number" {{ $indicator->targetType == 'number' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="Number_0">Number</label>
                                    </div>
                                    <div class="form-group">
                                        <input class="form-check-input" type="radio" name="targetType_0" id="Actual_0" value="actual" {{ $indicator->targetType == 'actual' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="Actual_0">Actual</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="alloted_budget" class="required">Alloted Budget</label>
                                        <input type="number" class="form-control capitalize" name="alloted_budget" id="alloted_budget_0" aria-describedby="" value="{{ $indicator->alloted_budget }}">
                                        <div class="invalid-feedback" id="alloted_budgetError"></div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="months_0">Month</label>
                                        <select id="months_0" class="months form-select" name="months">
                                            <option value="">Select Month</option>
                                            @for ($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ $indicator->month == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                                            @endfor
                                        </select>
                                        <div class="invalid-feedback" id="monthsError_[]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('components.specific_page_scripts')

<script>
$(document).ready(function() {
    // Initialize Select2 for Organizational Outcome
    $('#org_id').select2({
        placeholder: 'Select an Option',
        allowClear: true,
        ajax: {
            url: '{{ route('org.getOrg') }}',
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
                            text: item.organizational_outcome
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Initialize Select2 for Division
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

    function setCurrentMonth(index) {
        const now = new Date();
        const currentMonth = now.getMonth() + 1; // getMonth() returns 0-based index, so add 1
        $(`#months_${index}`).val(currentMonth).prop('disabled', true);
    }

    setCurrentMonth(0);

    // Handle target type change
    $(document).on('change', 'input[name^="targetType"]', function() {
        const index = $(this).closest('.card').find('input[name^="targetType"]').attr('id').split('_').pop();
        const selectedType = $(this).val();
        const targetInput = $(`#target_${index}`);
        const currentValue = targetInput.val();

        if (selectedType == 'percentage') {
            targetInput
            .attr('type', 'text') // Set type to text to allow appending "%"
            .attr('min', '0')
            .attr('max', '100')
            .attr('placeholder', '%')
            .removeAttr('disabled')
            .val(currentValue.replace('Actual', ''))
            .off('input.percentage')
            .on('input.percentage', function() {
                // Remove non-numeric characters except '%'
                let value = $(this).val().replace(/[^\d%]/g, '');

                if (value.indexOf('%') !== -1) {
                    value = value.substring(0, value.indexOf('%') + 1); // Keep only one "%"
                }
                // Ensure the value is within the range
                if ($.isNumeric(value) && value >= 0 && value <= 100) {
                    $(this).val(`${value}%`);
                } else {
                    $(this).val(value);
                }
            });
        } else if (selectedType == 'number') {
            targetInput
            .attr('type', 'number')
            .removeAttr('min')
            .removeAttr('max')
            .removeAttr('placeholder')
            .removeAttr('disabled')
            .val(currentValue.replace('%', ''))
            .off('input.percentage');
        } else if (selectedType == 'actual') {
            targetInput
            .attr('type', 'text')
            .attr('disabled', 'disabled')
            .removeAttr('placeholder')
            .off('input.percentage')
            .val('Actual');
        }
    });

    // Form submission
    $('#NewIndicatorForm').on('submit', function(e) {
        e.preventDefault();
        showLoader();

        $.ajax({
            url: '{{ route('indicator.update', $indicator->id) }}', // Assuming you have a route named 'indicator.update'
            type: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                hideLoader();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                    });
                    window.location.href = '/indicator';
                }
            },
            error: function(xhr) {
                hideLoader();
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('.invalid-feedback').html(''); // Clear any previous error messages
                    for (let key in errors) {
                        const keyParts = key.split('.');
                        if (keyParts.length > 1) {
                            const index = keyParts[1];
                            const errorKey = keyParts[0];
                            $(`#${errorKey}Error_${index}`).html(errors[key][0]).show();
                        } else {
                            $(`#${key}Error`).html(errors[key][0]).show();
                        }
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Errors!',
                        html: 'Please fill out the required fields with asterisk',
                        showConfirmButton: true,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oh no!',
                        text: 'Something went wrong.',
                        showConfirmButton: true,
                    });
                }
            }
        });
    });
});
</script>
@endsection
