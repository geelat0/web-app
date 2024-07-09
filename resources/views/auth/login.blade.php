<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/css/login.css')}}">
    {{-- <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .card {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style> --}}
</head>
<body>

    <div class="login_form">
        <!-- Login form container -->
        <form id="loginForm">
          <h3>Login</h3>
    
          <div class="login_option">
            <!-- Google button -->

            <div class="logo">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRF_ecLxSxg4DSqlbWaIUdHDo-Kb2aiR4YGg7bxcGV7mAKIY-zTuPw2FuuA_9CEA5jaO6M&usqp=CAU" alt="Logo">
            </div>
            
          </div>
    
          <!-- Login option separator -->
          <p class="separator">
            <span></span>
          </p>
    
          <!-- Email input box -->
          <div class="input_box">
            <label for="email">Email</label>
            <input type="email" id="email" placeholder="Enter email address" required />
          </div>
    
          <!-- Paswwrod input box -->
          <div class="input_box">
            <div class="password_title">
              <label for="password">Password</label>
              <a href="/forgot-password">Forgot Password?</a>
            </div>
    
            <input type="password" id="password" placeholder="Enter your password" required />
          </div>
    
           <!-- Login button -->
          <button type="submit">Log In</button>
    
          <p class="sign_up" style="font-size: 11px">Don't have an account? <a href="/user/create">Sign up</a></p>
        </form>
      </div>

{{-- <div class="card">
    <div class="card-body">
        <h3 class="card-title text-center mb-4">Login</h3>
        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" id="email" placeholder="Enter email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="/forgot-password">Forgot Password?</a> | 
            <a href="/user/create">Sign Up</a>
        </div>
    </div>
</div> --}}

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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
                    alert('Invalid credentials');
                }
            },
            error: function() {
                alert('Error logging in');
            }
        });
    });
</script>
</body>
</html>
