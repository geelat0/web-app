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
                            @if(in_array(Auth::user()->role->name, ['IT', 'Admin']))
                            <div class="form-group">
                                <label for="org_id" class="required">Organizational Outcome</label>
                                <select id="org_id" class="form-select capitalize" name="org_id">
                                    @if($indicator)
                                        <option value="{{ $indicator->org_id }}" selected>{{ $indicator->org->organizational_outcome }}</option>
                                    @endif
                                </select>
                                <div class="invalid-feedback" id="org_idError"></div>
                            </div>
                            @endif
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
                                <div class="form-group mb-3">
                                    <label for="measures" class="required">Measure</label>
                                    <textarea type="text" class="form-control capitalize" name="measures" id="measures" aria-describedby=""  @if(!in_array(Auth::user()->role->name, ['IT', 'Admin'])) disabled @endif>{{ $indicator->measures }}</textarea>
                                    <div class="invalid-feedback" id="measuresError"></div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="division_id" class="required">Division</label>
                                    <select id="division_id_0" class="division-select form-select" name="division_id[]" multiple="multiple" @if(!in_array(Auth::user()->role->name, ['IT', 'Admin'])) disabled @endif>
                                        @if($indicator)
                                            @foreach( $division_ids as $division)
                                                <option value="{{ $division }}" selected>{{ App\Models\Division::find($division)->division_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="invalid-feedback" id="division_idError"></div>
                                </div>
                                @if(in_array(Auth::user()->role->name, ['IT', 'Admin']))
                                <div class="form-group mb-3">
                                    <label for="">Target Type:</label>
                                        <div class="form-check form-check-inline percent">
                                        <input class="form-check-input" type="radio" name="targetType_0" id="Percentage_0" value="percentage">
                                    <label class="form-check-label" for="Percentage_0">Percentage</label>
                                    </div>
                                        <div class="form-check form-check-inline number">
                                        <input class="form-check-input" type="radio" name="targetType_0" id="Number_0" value="number">
                                    <label class="form-check-label" for="Number_0">Number</label>
                                    </div>
                                    <div class="form-check form-check-inline actual">
                                        <input class="form-check-input" type="radio" name="targetType_0" id="Actual_0" value="actual">
                                        <label class="form-check-label" for="Actual_0">Actual</label>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="row row-cols-4 mb-3" id="targetFields_0">
                                <div class="col mb-3" >
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group mb-3">
                                        <label for="target" class="required">Target</label>
                                        <input type="text" class="form-control capitalize" name="target" id="target_0" aria-describedby="" value="{{ $indicator->target }}" {{ $indicator->target == 'Actual' ? 'disabled' : '' }} @if(!in_array(Auth::user()->role->name, ['IT', 'Admin'])) disabled @endif>
                                        <div class="invalid-feedback" id="targetError[]"></div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="alloted_budget" class="required">Alloted Budget</label>
                                        <input type="number" step="any"  class="form-control capitalize" name="alloted_budget" id="alloted_budget" aria-describedby="" value="{{ $indicator->alloted_budget }}">
                                        <div class="invalid-feedback" id="alloted_budgetError"></div>
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
        }).on('change', function() {
            const index = $(this).attr('id').split('_').pop();
            const selectedDivisions = $(this).val();
            updateTargetFields(index, selectedDivisions);
        });

        // Check if any divisions are already selected and update accordingly
        $('.division-select').each(function() {
            const index = $(this).attr('id').split('_').pop();
            const selectedDivisions = $(this).val();
            if (selectedDivisions.length > 0) {
                updateTargetFields(index, selectedDivisions);
            }
        });
    }
    // initializeDivisionSelect();


    // Handle target type change
    $(document).on('change', 'input[name^="targetType"]', function() {
        const index = $(this).closest('.card').find('input[name^="targetType"]').attr('id').split('_').pop();
        const selectedType = $(this).val();
        const targetInput = $(`#target_${index}`);
        let currentValue = targetInput.val();
        // const currentValue = targetInput.val().replace('%', '')

        if (selectedType == 'percentage') {
            targetInput
            .attr('type', 'text') // Set type to text to allow appending "%"
            .attr('min', '0')
            .attr('max', '100')
            .attr('placeholder', '%')
            .removeAttr('disabled')
            .val(currentValue.includes('Actual') ? '' : `${currentValue.replace('%', '')}%`)
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
            .val(currentValue.replace('%', '').replace('Actual', ''))
            .off('input.percentage');
        } else if (selectedType == 'actual') {
            targetInput
            .attr('type', 'text')
            .attr('disabled', 'disabled')
            .removeAttr('placeholder')
            .off('input.percentage')
            .val('Actual');
            $(`#target_${index}`).val('Actual');
        }
    });


     //---------------------------------------------------START JS FOR DIVISION'S INPUTS---------------------------------------------------//

   // Function to update target fields based on selected divisions
    function updateTargetFields(index, selectedDivisions, isInitialLoad = false) {
        const targetContainer = $(`#targetFields_${index}`);
        targetContainer.empty();

        if (selectedDivisions.length > 0) {
            selectedDivisions.forEach((divisionId) => {
                const divisionName = $(`#division_id_${index} option[value="${divisionId}"]`).text();
                const cleanedDivisionName = divisionName.replace(/\s*PO$/, '');

                if (divisionName.includes("PO")) {
                    const targetValue = @json($division_targets)[divisionId] || 0;
                    const budgetValue = @json($division_budget)[divisionId] || 0;

                    const displayValue = targetValue == 0 ? 'Actual' : targetValue;
                    const targetDisabled = targetValue == 0 ? 'disabled' : '';
                    const targetHtml = `
                        <div class= "col mb-3">
                            <div class="form-group">
                                <label for="target_${divisionId}_${index}" class="required">${divisionName} Target</label>
                                <input type="text" class="form-control capitalize target-input" name="${cleanedDivisionName}_target[]" id="target_${divisionId}_${index}" aria-describedby="" value="${displayValue}" ${targetDisabled}>
                                <div class="invalid-feedback" id="targetError_${divisionId}_${index}"></div>
                            </div>
                        </div>
                        <div class= "col mb-3">
                            <div class="form-group">
                                <label for="budget_${divisionId}_${index}" class="required">${divisionName} Budget</label>
                                <input type="number" step="any"  class="form-control capitalize alloted-budget" name="${cleanedDivisionName}_budget[]" id="budget_${divisionId}_${index}" value="${budgetValue}">
                                <div class="invalid-feedback" id="budgetError_${divisionId}_${index}"></div>
                            </div>
                        </div>
                    `;
                    targetContainer.append(targetHtml);

                    // Enable the target input and attach the input event to calculate total
                    const targetInput = $(`#target_${divisionId}_${index}`);
                    if (!isInitialLoad) {

                    @if(in_array(Auth::user()->role->name, ['IT', 'Admin']))
                        // targetInput.removeAttr('disabled');
                    @endif


                    } else {
                        const targetValue = $(`#target_${index}`).val();
                        // targetInput.val(targetValue).removeAttr('disabled');
                    }

                    targetInput.on('input', function() {
                        let total = 0;
                        let selectedType = $(`input[name="targetType_${index}"]:checked`).val();
                        console.log(selectedType);

                        $(`#targetFields_${index} .target-input`).each(function() {
                            let value = parseFloat($(this).val());
                            if (!isNaN(value)) {
                                total += value;
                            }
                        });
                        if (selectedType === 'percentage') {
                            $(`#target_${index}`).val(`${total}%`);
                        } else {
                            $(`#target_${index}`).val(total);
                        }
                    });

                    const budgetInput = $(`#budget_${divisionId}_${index}`);
                    budgetInput.on('input', function() {
                        let totalBudget = parseFloat($('#alloted_budget').val()) || 0;
                        let currentBudgetValue = parseFloat(budgetInput.data('initial-value')) || 0;

                        if (isNaN(totalBudget)) {
                            totalBudget = 0;
                        }

                        if (isNaN(currentBudgetValue)) {
                            currentBudgetValue = 0;
                        }

                        const newValue = parseFloat(budgetInput.val()) || 0;
                        totalBudget = totalBudget - currentBudgetValue + newValue;

                        budgetInput.data('initial-value', newValue);
                        $('#alloted_budget').val(totalBudget);
                    });
                    // Store the initial budget value for accurate calculations
                    budgetInput.data('initial-value', budgetValue);
                } else {
                    $('.percent').removeClass('d-none');
                }
            });
        }
    }

    // Trigger the initialization function on page load
    initializeDivisionSelect();

    $(document).on('change', '.division-select', function() {
        const index = $(this).attr('id').split('_').pop();
        const selectedDivisions = $(this).val();

        const hasPO = selectedDivisions.some((divisionId) => {
            const divisionName = $(`#division_id_${index} option[value="${divisionId}"]`).text();
            return divisionName.includes("PO");
        });

        $(`.percent`).removeClass('d-none')

        updateTargetFields(index, selectedDivisions);

        if (hasPO) {
            updateTargetFields(index, selectedDivisions);
        }
    });

    $(document).on('change', 'input[name^="targetType"]', function() {
        const index = $(this).closest('.card').find('input[name^="targetType"]').attr('id').split('_').pop();
        const selectedType = $(this).val();

        $(`#targetFields_${index} .target-input`).each(function() {
            const targetInput = $(this);
            // const currentValue = targetInput.val().replace('%', ''); // Remove % if present
            let currentValue = targetInput.val();

            if (selectedType === 'percentage') {
                targetInput
                    .attr('type', 'text')
                    .attr('min', '0')
                    .attr('max', '100')
                    .attr('placeholder', '%')
                    .removeAttr('disabled')
                    .val(currentValue.includes('Actual') ? '' : `${currentValue.replace('%', '')}%`)
                    .off('input.percentage')
                    .on('input.percentage', function() {
                        let value = $(this).val().replace(/[^\d%]/g, '');
                        if (value.indexOf('%') !== -1) {
                            value = value.substring(0, value.indexOf('%') + 1); // Keep only one "%"
                        }
                        if ($.isNumeric(value) && value >= 0 && value <= 100) {
                            $(this).val(`${value}%`);
                        } else {
                            $(this).val(value);
                        }
                    });

            } else if (selectedType === 'number') {
                targetInput
                    .attr('type', 'number')
                    .removeAttr('min')
                    .removeAttr('max')
                    .removeAttr('placeholder')
                    .removeAttr('disabled')
                    .val(currentValue.replace('%', '').replace('Actual', ''))
                    .off('input.percentage');

                let total = 0;
                $(`#targetFields_${index} .target-input`).each(function() {
                    const value = parseFloat($(this).val().replace('%', ''));
                    if (!isNaN(value)) {
                        total += value;
                    }
                });

                $(`#target_${index}`).val(total);
            } else if (selectedType === 'actual') {
                targetInput
                    .attr('type', 'text')
                    .attr('disabled', 'disabled')
                    .removeAttr('placeholder')
                    .off('input.percentage')
                    .val('Actual');
                $(`#target_${index}`).val('Actual');
            }
        });

    });



    //---------------------------------------------------END JS FOR DIVISION'S INPUTS---------------------------------------------------//

    // Form submission
    @if(in_array(Auth::user()->role->name, ['IT', 'Admin']))
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
                            console.log(errorKey);
                            $(`#${errorKey}`).addClass('is-invalid');
                            $(`#${errorKey}Error`).html(errors[key][0]).show();
                        } else {
                            $(`#${key}`).addClass('is-invalid');
                            $(`#${key}Error`).html(errors[key][0]).show();
                        }
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Errors!',
                        html: 'Please fill out the required fields with asterisk',
                        showConfirmButton: true,
                    });

                    $('.division-select').each(function() {
                        const index = $(this).attr('id').split('_').pop();
                        if ($(this).val().length === 0) { // Check if no value is selected
                            $(`#division_id_${index}`).addClass('is-invalid');
                            $(`#division_idError_${index}`).html('Please select at least one division.').show();
                            isValid = false;
                        } else {
                            $(`#division_idError_${index}`).html('').hide();
                        }
                    });

                    if (!isValid) {
                        hideLoader();
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Errors!',
                            html: 'Please fill out the required fields with asterisk',
                            showConfirmButton: true,
                        });
                        return;
                    }
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
    @endif

    @if(!in_array(Auth::user()->role->name, ['IT', 'Admin']))
    $('#NewIndicatorForm').on('submit', function(e) {
            e.preventDefault();
            showLoader();

            $.ajax({
                url: '{{ route('indicator.update_nonSuperAdminV2') }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    hideLoader();
                    window.location.href = '/indicator';
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: false,
                        });
                        $('#NewIndicatorForm')[0].reset(); // Reset the form
                        $('#cards-containers').html($('.cards-container:first').clone());
                    }
                },
                error: function(xhr) {
                    hideLoader();
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let isValid = true;
                        $('.invalid-feedback').html(''); // Clear any previous error messages
                        for (let key in errors) {
                            const keyParts = key.split('.');
                            console.log(keyParts);
                            if (keyParts.length > 1) {
                                const index = keyParts[1];
                                const errorKey = keyParts[0];
                                console.log(errorKey);
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

                        $('.division-select').each(function() {
                            const index = $(this).attr('id').split('_').pop();
                            if ($(this).val().length === 0) { // Check if no value is selected
                                $(`#division_idError_${index}`).html('Please select at least one division.').show();
                                isValid = false;
                            } else {
                                $(`#division_idError_${index}`).html('').hide();
                            }
                        });

                        if (!isValid) {
                            hideLoader();
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Errors!',
                                html: 'Please fill out the required fields with asterisk',
                                showConfirmButton: true,
                            });
                            return;
                        }
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
        @endif
});
</script>
@endsection
