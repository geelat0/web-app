<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Style -->
    <link rel="stylesheet" href="{{asset('css/registration.css')}}">

    <!-- Bootstrap Icon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <title>Registration</title>
  </head>
  <body>
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center vh-100">   
            <div class="card border-radius custom-registration-card p-3">
                <!-- <div class="card-header align-items-center">
                    <div class="d-flex justify-content-center"><i class="bi bi-pencil-fill" style="font-size: 28px;"></i></div>
                    <div class="d-flex justify-content-center">BUGS Honorarium Monitoring System Registration Form</div>
                </div> -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h1>Registration</h1>
                            <p class="font-weight-normal">Fill out the form carefully.</p> 
                        </div>
                        <div class="col-md-8">
                            <form id="registrationForm" method="POST" action="{{ route('user.store') }}">
                                @csrf
                                <div class="row mb-3">

                                    <input type="hidden" name="status" class="form-control capitalize" name="status" value="active" aria-describedby="">
                                    <input type="hidden" name="role" class="form-control capitalize" name="role" value="user" aria-describedby="">

                                    

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">First Name</label> -->
                                            <input type="text" class="form-control capitalize" name="first_name" id="firstName" aria-describedby="" placeholder="First Name">
                                            <div class="invalid-feedback" id="firstNameError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">Middle Name</label> -->
                                            <input type="text" class="form-control capitalize" id="middleName" name="middle_name" aria-describedby="" placeholder="Middle Name">
                                            <div class="invalid-feedback" id="middleNameError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">Surname</label> -->
                                            <input type="text" class="form-control capitalize" id="lastName" name="last_name" aria-describedby="" placeholder="Surname">
                                            <div class="invalid-feedback" id="lastNameError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">Suffix</label> -->
                                            <input type="text" class="form-control capitalize" id="suffix" name="suffix" aria-describedby="" placeholder="Suffix">
                                            <!-- <div class="invalid-feedback" id="suffixNameError"></div> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">Suffix</label> -->
                                            <input type="text" class="form-control capitalize" id="position" name="position" aria-describedby="" placeholder="Position">
                                            <div class="invalid-feedback" id="positionError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <!-- <label for="exampleFormControlSelect1">Example select</label> -->
                                            <select class="custom-select" id="province" name="province">
                                                <option value="">Select a Province...</option>
                                                <option value="Albay">Albay</option>
                                                <option value="Camarines Norte">Camarines Norte</option>
                                                <option value="Camarines Sur">Camarines Sur</option>
                                                <option value="Catanduanes">Catanduanes</option>
                                                <option value="Masbate">Masbate</option>
                                                <option value="Sorsogon">Sorsogon</option>
                                            </select>
                                            <div class="invalid-feedback" id="collegeError"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">Middle Name</label> -->
                                            <input type="text" class="form-control" id="contactNumber" name="mobile_number" placeholder="Contact Number">
                                            <div class="invalid-feedback" id="contactNumberError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">Surname</label> -->
                                            <input type="text" class="form-control" id="email" aria-describedby="" name="email" placeholder="Email">
                                            <div class="invalid-feedback" id="emailError"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">First Name</label> -->
                                            <input type="password" class="form-control" id="password" aria-describedby="" name="password" placeholder="Password">
                                            <div class="invalid-feedback" id="passwordError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">Middle Name</label> -->
                                            <input type="password" class="form-control" id="confirmPassword" aria-describedby="" name="password" placeholder="Confirm Password">
                                            <div class="invalid-feedback" id="confirmPasswordError"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-flex flex-row-reverse align-items-end row-button-submit">
                                    <button type="submit" class="btn btn-primary custom-btn"><i class="bi bi-pencil-square">&nbsp;</i>Register Account</button>
                                    <a href="/" class="custom-link">Already have an Account?</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
                </div>
                <!-- <div class="card-footer text-muted">
                    copyright reserve 2024
                </div> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    {{-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" integrity="sha512-+NqPlbbtM1QqiK8ZAo4Yrj2c4lNQoGv8P79DPtKzj++l5jnN39rHA/xsqn8zE9l0uSoxaCdrOgFs6yjyfbBxSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/registration.js') }}"></script>

    
  </body>
</html>