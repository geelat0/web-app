<!DOCTYPE html>
<html>
<head>
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background-color: #f2f2f2;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            margin: 20px auto;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="card-title text-center">Registration Form</h2>
            <form id="registrationForm">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name:</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" required>
                </div>
                <div class="form-group">
                    <label for="province">Province:</label>
                    {{-- <input type="text" class="form-control" id="province" name="province" required> --}}

                    <select class="form-control" id="province" name="province" aria-label="Default select example">
                        <option selected>Select an Option</option>
                        <option value="Albay">Albay</option>
                        <option value="Camarines Norte">Camarines Norte</option>
                        <option value="Camarines Sur">Camarines Sur</option>
                        <option value="Catanduanes">Catanduanes</option>
                        <option value="Masbate">Masbate</option>
                        <option value="Sorsogon">Sorsogon</option>
                      </select>
                </div>
                <div class="form-group d-none">
                    <label for="position">Position:</label>
                    <input type="text" class="form-control" id="position" name="position" value="active">
                </div>
                <div class="form-group">
                    <label for="mobile_number">Mobile Number:</label>
                    <input type="text" class="form-control" id="mobile_number" name="mobile_number" required>
                </div>
                <div class="form-group d-none">
                    <label for="role">Role:</label>
                    <input type="text" class="form-control" id="role" name="role" value="user">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group d-none">
                    <label for="status">Status:</label>
                    <input type="text" class="form-control" id="status" name="status" value="active" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#registrationForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route("user.store") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        alert('User registered successfully');
                        $('#registrationForm')[0].reset();
                    },
                    error: function(xhr) {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>
