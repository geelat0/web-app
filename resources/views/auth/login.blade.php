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
    <link rel="stylesheet" href="{{asset('css/registration.css')}}">



    <title>login</title>
  </head>
  <body>
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="card border-radius custom-login-card p-4">
                <!-- <div class="card-header align-items-center">
                    <div class="d-flex justify-content-center"><i class="bi bi-pencil-fill" style="font-size: 28px;"></i></div>
                    <div class="d-flex justify-content-center">BUGS Login</div>
                </div> -->
                <div class="card-body">
                    <form id="loginForm">
                        <div class="row">
                            <div class="content-center col-md-6">
                                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRF_ecLxSxg4DSqlbWaIUdHDo-Kb2aiR4YGg7bxcGV7mAKIY-zTuPw2FuuA_9CEA5jaO6M&usqp=CAU" class="logo-container" alt="Login">
                                <h1 class="custom-header hide">Sign In</h1>
                                <p class="font-weight-normal custom-paragraph hide">to continue to System</p>
                            </div>
                            <div class="col-md-6">
                                <div class="row d-flex flex-column align-items-center">
                                    <h5 class="custom-header mb-3" style="font-weight: 600; color: #0C0342;">System Title</h5>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">First Name</label> -->
                                            <input type="text" name="email" class="form-control" id="email" aria-describedby="" placeholder="Email">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <!-- <label for="exampleInputEmail1">First Name</label> -->
                                            <input type="password" name="password" class="form-control" id="password" aria-describedby="" placeholder="Password">
                                            <a href="/forgot-password" class="custom-link">Forgot Password?</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-flex flex-row-reverse align-items-end row-button-submit">
                            <button type="submit" class="btn btn-primary custom-btn">Login</button>
                            <a href="/user/create" class="custom-link">Dont have an Account?</a>
                        </div>
                    </form>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" integrity="sha512-+NqPlbbtM1QqiK8ZAo4Yrj2c4lNQoGv8P79DPtKzj++l5jnN39rHA/xsqn8zE9l0uSoxaCdrOgFs6yjyfbBxSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/registration.js') }}"></script>


    <script>
        $('#loginForm').on('submit', function(event) {
            event.preventDefault();
            $.ajax({
                url: '{{ route('login') }}',
                method: 'POST',
                data: {
                    email: $('#email').val(),
                    password: $('#password').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '/dash-home';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: response.message, // Assuming your API returns a message
                            showConfirmButton: true,
                        })
                        // alert('Invalid credentials');
                    }
                },
                error: function() {
                    Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: 'Please fill out the required fields.', // Assuming your API returns a message
                            showConfirmButton: true,
                        })
                    // alert('Error logging in');
                }
            });
        });
    </script>
  </body>
</html>
