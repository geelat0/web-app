@extends('app')  {{-- Main blade File --}}

{{-- Content of Pages --}}
@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">

            </div>
            <div class="card-body">

                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                      <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Personal Information</button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Change Password</button>
                    </li>
                </ul>
                  <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
                        <form id="updateProfileForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row justify-content-center">
                                <div class="col-md-3 text-center">
                                    <div class="profile-picture">
                                        <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : 'https://static.vecteezy.com/system/resources/previews/005/544/718/non_2x/profile-icon-design-free-vector.jpg' }}" alt="Profile Picture" class="img-thumbnail mb-3 rounded-circle" id="profileImagePreview">
                                        <input type="file" class="form-control d-none" id="profile_image" name="profile_image">

                                        <div id="profile_imageError" class="invalid-feedback"></div>
                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-primary" id="uploadProfilePictureBtn">
                                                <i class="mdi mdi-briefcase-upload"></i> Upload Photo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <div class="form-group">
                                        <label for="user_name">Username</label>
                                        <input type="text" class="form-control" id="user_name" name="user_name" value="{{ $user->user_name }}" disabled>
                                        <div id="user_nameError" class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}">
                                        <div id="emailError" class="invalid-feedback"></div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $user->first_name }}">
                                        <div id="first_nameError" class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="province">Province</label>
                                        <select class="form-select capitalize" id="province" name="province" >
                                            <option value="">Select a Province...</option>
                                            <option value="Albay" {{ $user->province == 'Albay' ? 'selected' : '' }}>Albay</option>
                                            <option value="Camarines Norte" {{ $user->province == 'Camarines Norte' ? 'selected' : '' }}>Camarines Norte</option>
                                            <option value="Camarines Sur" {{ $user->province == 'Camarines Sur' ? 'selected' : '' }}>Camarines Sur</option>
                                            <option value="Catanduanes" {{ $user->province == 'Catanduanes' ? 'selected' : '' }}>Catanduanes</option>
                                            <option value="Masbate" {{ $user->province == 'Masbate' ? 'selected' : '' }}>Masbate</option>
                                            <option value="Sorsogon" {{ $user->province == 'Sorsogon' ? 'selected' : '' }}>Sorsogon</option>
                                        </select>
                                        <div id="provinceError" class="invalid-feedback"></div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $user->last_name }}">
                                        <div id="last_nameError" class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="position">Position</label>
                                        <input type="text" class="form-control" id="position" name="position" value="{{ $user->position }}">
                                        <div id="positionError" class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="middle_name">Middle Name</label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ $user->middle_name }}">
                                        <div id="middle_nameError" class="invalid-feedback"></div>
                                    </div>

                                    <div class="form-group">
                                        <label for="mobile_number">Mobile Number</label>
                                        <input type="text" class="form-control" id="mobile_number" name="mobile_number" value="{{ $user->mobile_number }}">
                                        <div id="mobile_numberError" class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                            
                        </form>
                    </div>
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
                        <form id="changePasswordForm">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <div id="current_passwordError" class="invalid-feedback"></div>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div id="new_passwordError" class="invalid-feedback"></div>
                            </div>
                            <div class="form-group">
                                <label for="new_password_confirmation">Confirm New Password</label>
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                                <div id="new_password_confirmationError" class="invalid-feedback"></div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </div>
                        </form>
                    </div>
                    
                  </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#updateProfileForm').on('submit', function(e) {
            e.preventDefault();
            showLoader();
            var formData = new FormData(this);

            $.ajax({
                url: '{{ route('profile.update') }}', 
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        hideLoader();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: true,
                        });

                        if (response.profile_image) {
                            $('#profileImagePreview').attr('src', response.profile_image);
                        }
                    } else {
                        var errors = response.errors;
                        Object.keys(errors).forEach(function(key) {
                            var inputField = $('#updateProfileForm [name=' + key + ']');
                            inputField.addClass('is-invalid');
                            $('#updateProfileForm #' + key + 'Error').text(errors[key][0]);
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

        $('#updateProfileForm').find('input, select').on('keyup change', function() {
            $(this).removeClass('is-invalid');
            var errorId = $(this).attr('name') + 'Error';
            $('#' + errorId).text('');
        });

        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            showLoader();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route('password.change') }}', 
                method: 'POST',
                data: formData,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: true,
                        });
                        $('#changePasswordForm')[0].reset();
                    } else {
                        var errors = response.errors;
                        Object.keys(errors).forEach(function(key) {
                            var inputField = $('#changePasswordForm [name=' + key + ']');
                            inputField.addClass('is-invalid');
                            $('#changePasswordForm #' + key + 'Error').text(errors[key][0]);
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

        $('#uploadProfilePictureBtn').on('click', function() {
            $('#profile_image').click();
        });

        $('#profile_image').on('change', function() {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#profileImagePreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
        });

        $('form').on('reset', function() {
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').text('');
        });
    });
</script>
@endsection
