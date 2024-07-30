@extends('app')  {{-- Main blade File --}}

{{-- Content of Pages --}}
@section('content')

<div class="container mt-5">

    <form id="NewIndicatorForm">
        @csrf
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title"> <a href="/indicator" class="text-primary"><i class="fas fa-arrow-circle-left text-primary"></i></a>
                        Create</h4>
                    <div class="row">
                        <div class="form-group">
                            <label for="org_id" class="required">Organizational Outcome</label>
                            <select id="org_id" class="form-select capitalize" name="org_id">                                    
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div id="cards-containers">
            <div class="col-lg-12 grid-margin stretch-card card-template cards-container">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group">
                                <label for="measures">Measure</label>
                                <textarea type="text" class="form-control capitalize" name="measures[]" id="measures_0" aria-describedby=""></textarea>
                            </div> 
                            <div class="form-group">
                                <label for="division_id">Division</label>
                                <select id="division_id_0" class="division-select form-select" name="division_id[0][]" multiple="multiple">                                    
                                </select>
                                <div class="invalid-feedback" id="division_idError_[]"></div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="target" class="required">Target</label>
                                    <input type="text" class="form-control capitalize" name="target[]" id="target_0" aria-describedby="" disabled>
                                    <div class="invalid-feedback" id="targetError[]"></div>
                                </div>
                                <div class="form-group">
                                    <input class="form-check-input" type="radio" name="targetType_0" id="Percentage_0" value="percentage">
                                    <label class="form-check-label" for="Percentage_0">Percentage</label>
                                </div>
                                <div class="form-group">
                                    <input class="form-check-input" type="radio" name="targetType_0" id="Number_0" value="number">
                                    <label class="form-check-label" for="Number_0">Number</label>
                                </div>
                                <div class="form-group">
                                    <input class="form-check-input" type="radio" name="targetType_0" id="Actual_0" value="actual">
                                    <label class="form-check-label" for="Actual_0">Actual</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="alloted_budget">Alloted Budget</label>
                                    <input type="number" class="form-control capitalize" name="alloted_budget[]" id="alloted_budget_0" aria-describedby="">
                                    <div class="invalid-feedback" id="alloted_budgetError"></div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="months_0">Month</label>
                                    <select id="months_0" class="months form-select" name="months[]">      
                                        <option value="">Select Month</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
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

        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-start">
                    <button type="button" class="btn btn-primary btn-add-card" id="addOutcomeBtn"><i class="mdi mdi-plus-circle-outline"></i>Add Entries</button>
                </div>

            </div>
            <div class="col">
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
                    
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
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


    let outcomeIndex = 1;

    function addOutcomeCard() {
        const newOutcomeHtml = `
            <div class="col-lg-12 grid-margin stretch-card card-template cards-container" id="cards-container_${outcomeIndex}">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group">
                                <label for="measures_${outcomeIndex}" class="required">Measure</label>
                                <input type="text" class="form-control capitalize" name="measures[]" id="measures_${outcomeIndex}" aria-describedby="">
                                <div class="invalid-feedback" id="measureError_${outcomeIndex}"></div>
                            </div> 
                            <div class="form-group">
                                <label for="division_id_${outcomeIndex}">Division</label>
                                <select id="division_id_${outcomeIndex}" class="division-select form-select" name="division_id[${outcomeIndex}][]" multiple="multiple">                                    
                                </select>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                     <label for="target_${outcomeIndex}" class="required">Target</label>
                                    <input type="text" class="form-control capitalize" name="target[]" id="target_${outcomeIndex}" aria-describedby="" disabled>
                                    <div class="invalid-feedback" id="targetError_${outcomeIndex}"></div>
                                </div>
                                <div class="form-group">
                                    <input class="form-check-input target-type" type="radio" name="targetType_${outcomeIndex}" id="Percentage_${outcomeIndex}" value="percentage">
                                    <label class="form-check-label" for="Percentage_${outcomeIndex}">Percentage</label>
                                </div>
                                <div class="form-group">
                                    <input class="form-check-input target-type" type="radio" name="targetType_${outcomeIndex}" id="Number_${outcomeIndex}" value="number">
                                    <label class="form-check-label" for="Number_${outcomeIndex}">Number</label>
                                </div>
                                <div class="form-group">
                                    <input class="form-check-input target-type" type="radio" name="targetType_${outcomeIndex}" id="Actual_${outcomeIndex}" value="actual">
                                    <label class="form-check-label" for="Actual_${outcomeIndex}">Actual</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="alloted_budget_${outcomeIndex}">Alloted Budget</label>
                                    <input type="number" class="form-control capitalize" name="alloted_budget[]" id="alloted_budget_${outcomeIndex}" aria-describedby="">
                                    <div class="invalid-feedback" id="alloted_budgetError_${outcomeIndex}"></div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="months_${outcomeIndex}">Month</label>
                                    <select id="months_${outcomeIndex}" class="months form-select" name="months[]">      
                                        <option value="">Select Month</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                                        @endfor                              
                                    </select>
                                    <div class="invalid-feedback" id="monthsError_${outcomeIndex}"></div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm mt-2 removeOutcomeBtn" data-index="${outcomeIndex}"><i class="mdi mdi-delete"></i></button>
                    </div>
                </div>
            </div>
        `;
        $('#cards-containers').append(newOutcomeHtml);
        
        initializeDivisionSelect();
        setCurrentMonth(outcomeIndex); 
        outcomeIndex++;
    }

    $('#addOutcomeBtn').click(function () {
        addOutcomeCard();
    });

    $(document).on('click', '.removeOutcomeBtn', function () {
        const index = $(this).data('index');
        $(`#cards-container_${index}`).remove();
    });
    

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

    

    $('#NewIndicatorForm').on('submit', function(e) {
        e.preventDefault();
        showLoader();

        $.ajax({
            url: '{{ route('indicator.store') }}',
            type: 'POST',
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
                    $('#NewIndicatorForm')[0].reset(); // Reset the form
                    $('#cards-containers').html($('.cards-container:first').clone());
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
